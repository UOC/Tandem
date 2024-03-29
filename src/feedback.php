<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/classes/utils.php';
require_once __DIR__ . '/classes/lang.php';
require_once __DIR__ . '/classes/constants.php';
require_once __DIR__ . '/classes/gestorBD.php';
require_once __DIR__ . '/IMSBasicLTI/uoc-blti/lti_utils.php';

$user_obj                     = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$course_id                    = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : false;
$use_waiting_room             = isset( $_SESSION[ USE_WAITING_ROOM ] ) ? $_SESSION[ USE_WAITING_ROOM ] : false;
$feedback_selfreflection_form = isset( $_SESSION[ FEEDBACK_SELFREFLECTION_FORM ] ) ? $_SESSION[ FEEDBACK_SELFREFLECTION_FORM ] : false;

require_once __DIR__ . '/classes/IntegrationTandemBLTI.php';

// Si no existeix objecte usuari o no existeix curs redireccionem cap a l'index.
// TODO preguntar Antoni cap a on redirigir.
if ( ! $user_obj || ! $course_id ) {
	// Tornem a l'index.
	header( 'Location: index.php' );
	die();
}


require_once( __DIR__ . '/classes/constants.php' );
$id_feedback = isset( $_GET['id_feedback'] ) && $_GET['id_feedback'] > 0 ?
	$_GET['id_feedback'] :
	( isset( $_POST['id_feedback'] ) && $_POST['id_feedback'] > 0 ? $_POST['id_feedback'] : $_SESSION[ ID_FEEDBACK ] );

if ( ! $id_feedback ) {
	die( $LanguageInstance->get( 'Missing feedback parameter' ) );
}

$gestorBD = new GestorBD();
$gestorBD->set_userInTandem($user_obj->id,$course_id,0);
$feedbackDetails = $gestorBD->getFeedbackDetails( $id_feedback );
if ( ! $feedbackDetails ) {
	die( $LanguageInstance->get( 'Can not find feedback' ) );
}
$_SESSION[ ID_FEEDBACK ] = $id_feedback;

//we need these extra info to show like the list of portfolio
$extra_feedback_details = $gestorBD->getUserFeedback( $id_feedback );

if ( ! empty( $_POST['rating_partner'] ) ) {
	$rating_partner_feedback_form                  = new stdClass();
	$rating_partner_feedback_form->partner_rate    = isset( $_POST['partner_rate'] ) ? $_POST['partner_rate'] : 0;
	$rating_partner_feedback_form->partner_comment = isset( $_POST['partner_comment'] ) ? $_POST['partner_comment'] : '';
	$gestorBD->updateRatingPartnerFeedbackTandemDetail( $id_feedback, $rating_partner_feedback_form );

	//lets update this user ranking points
	$gestorBD->updateUserRankingPoints( $user_obj->id, $course_id );
	//now lets update the partner ranking points
	$gestorBD->updateUserRankingPoints( $feedbackDetails->id_partner, $course_id );
	$feedbackDetails = $gestorBD->getFeedbackDetails( $id_feedback );
}


if ( $user_obj->id != $feedbackDetails->id_user && $user_obj->id != $feedbackDetails->id_partner &&
     ! $user_obj->instructor && ! $user_obj->admin ) { //check if is the user of feedback if not can't not set feedback
	die( $LanguageInstance->get( 'Not authorized' ) );
}

$message  = false;
$can_edit = true;

if ( isset($_SESSION[ USE_WAITING_ROOM_NO_TEAMS ]) && $_SESSION[ USE_WAITING_ROOM_NO_TEAMS ] ) {
	$feedback_form_new                           = new stdClass();
	$feedback_form_new->grammaticalresource      = 0;
	$feedback_form_new->lexicalresource          = 0;
	$feedback_form_new->discoursemangement       = 0;
	$feedback_form_new->pronunciation            = 0;
	$feedback_form_new->interactivecommunication = 0;
	$feedback_form_new->other_observations       = "";
} else {
	$feedback_form                     = new stdClass();
	$feedback_form->fluency            = 0;
	$feedback_form->accuracy           = 0;
	$feedback_form->grade              = "";
	$feedback_form->pronunciation      = "";
	$feedback_form->vocabulary         = "";
	$feedback_form->grammar            = "";
	$feedback_form->other_observations = "";
}
if ( $feedbackDetails->feedback_form ) { //if it is false can edit
	$can_edit      = false;
	$message       = '<div class="alert alert-info" role="alert">' . $LanguageInstance->get( 'The information is stored you can only review it' ) . '</div>';
	$feedback_form = $feedbackDetails->feedback_form;
} else {
	if ( $user_obj->id == $feedbackDetails->id_user ) { //check if is the user of feedback if not can't not set feedback
		if ( ! empty( $_POST['save_feedback_new'] ) ) {
			$feedback_form_new->grammaticalresource      = $_POST['grammaticalresource'];
			$feedback_form_new->lexicalresource          = $_POST['lexicalresource'];
			$feedback_form_new->discoursemangement       = $_POST['discoursemangement'];
			$feedback_form_new->pronunciation            = $_POST['pronunciation'];
			$feedback_form_new->interactivecommunication = $_POST['interactivecommunication'];
			$feedback_form_new->other_observations       = $_POST['other_observations'];
			if ( $gestorBD->createFeedbackTandemDetail( $id_feedback, serialize( $feedback_form_new ) ) ) {
				$message  = '<div class="alert alert-success" role="alert">' . $LanguageInstance->get( 'Data saved successfully' ) . '</div>';
				$can_edit = false;

				//lets update this user ranking points
				$gestorBD->updateUserRankingPoints( $user_obj->id, $course_id );
				//now lets update the partner ranking points
				$gestorBD->updateUserRankingPoints( $feedbackDetails->id_partner, $course_id);
			}
		} else {
			if ( ! empty( $_POST['save_feedback'] ) ) {
				//try to save it!
				$feedback_form->fluency            = isset( $_POST['fluency'] ) ? $_POST['fluency'] : 50;
				$feedback_form->accuracy           = isset( $_POST['accuracy'] ) ? $_POST['accuracy'] : 50;
				$feedback_form->grade              = isset( $_POST['grade'] ) ? $_POST['grade'] : '';
				$feedback_form->pronunciation      = isset( $_POST['pronunciation'] ) ? $_POST['pronunciation'] : '';
				$feedback_form->vocabulary         = isset( $_POST['vocabulary'] ) ? $_POST['vocabulary'] : '';
				$feedback_form->grammar            = isset( $_POST['grammar'] ) ? $_POST['grammar'] : '';
				$feedback_form->other_observations = isset( $_POST['other_observations'] ) ? $_POST['other_observations'] : '';
				if ( isset( $_POST['fluency'] ) && strlen( $_POST['fluency'] ) > 0 &&
				     isset( $_POST['accuracy'] ) && strlen( $_POST['accuracy'] ) > 0 &&
				     isset( $_POST['grade'] ) && strlen( $_POST['grade'] ) > 0 /*&&
						isset($_POST['pronunciation']) && strlen($_POST['pronunciation'])>0 &&
						isset($_POST['vocabulary']) && strlen($_POST['vocabulary'])>0 &&
						isset($_POST['grammar']) && strlen($_POST['grammar'])>0*/ ) {
					if ( $gestorBD->createFeedbackTandemDetail( $id_feedback, serialize( $feedback_form ) ) ) {
						$message  = '<div class="alert alert-success" role="alert">' . $LanguageInstance->get( 'Data saved successfully' ) . '</div>';
						$can_edit = false;

						//lets update this user ranking points
						$gestorBD->updateUserRankingPoints( $user_obj->id, $course_id );
						//now lets update the partner ranking points
						$gestorBD->updateUserRankingPoints( $feedbackDetails->id_partner, $course_id);
					}
				} else {
					$message = '<div class="alert alert-danger" role="alert">' . $LanguageInstance->get( 'fill_required_fields' ) . '</div>';
				}
			}
		}
	} else {
		$can_edit = false;
	}
}
$partnerFeedback = $gestorBD->checkPartnerFeedback( $feedbackDetails->id_tandem, $id_feedback );

$partnerName = $gestorBD->getPartnerName( $id_feedback );

//@ybilbao 3iPunt -> Get course rubricks
$rubrics = $gestorBD->get_course_rubrics( $course_id );
//END

?>
<!DOCTYPE html>
<html>
<head>
    <title>Tandem</title>
    <meta charset="UTF-8"/>

    <link rel="stylesheet" type="text/css" media="all" href="css/autoAssignTandem.css?v=2"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/tandem-waiting-room.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/defaultInit.css"/>
    <link rel="stylesheet" type="text/css" media="all"
          href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/slider2.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/star-rating.min.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/bootstrap-tour.min.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/bootstrap-tour-standalone.min.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/feedback.css"/>
	<?php if ( $feedback_selfreflection_form ) { ?>
        <link rel="stylesheet" type="text/css" media="all" href="css/feedback-selfreflection.css"/>
	<?php } ?>
</head>
<body>
<!-- Begin page content -->
<div id="wrapper" class="container">
    <div class="page-header">
        <div class='row'>
            <div class='col-md-5'>
                <button class="btn btn-success" type='button'
                        onclick="window.location ='portfolio.php';"><?php echo $LanguageInstance->get( 'Back to list' ) ?></button>
                <h1><?php echo $LanguageInstance->get( 'peer_review_form' ) ?></h1>
				<?php if ( $user_obj->instructor == 1 ) { ?>
                    <p><?php echo $LanguageInstance->get( 'Name' ) ?>
                        : <?php echo $gestorBD->getUserName( $feedbackDetails->id_user ); ?></p>
				<?php } ?>
                <p><?php echo $LanguageInstance->get( 'your_partners_name' ) ?>: <?php echo $partnerName; ?>
                    <button type="button" id="button-contact" data-toggle="modal" data-target="#contact-modal"
                            class="btn btn-info" title="<?php echo $LanguageInstance->get( 'Contact' ) ?>"><i
                                class="glyphicon glyphicon-envelope"></i> <?php echo $LanguageInstance->get( 'Contact' ) ?>
                    </button>
                </p>
				<?php if ( $user_obj->instructor && $user_obj->instructor == 1 ) { ?>
                    <p>
                        <button type="button" id="button-contact" data-toggle="modal" data-target="#info-modal"
                                class="btn btn-info"
                                title="<?php echo $LanguageInstance->get( 'Session Tandem Information' ) ?>"><i
                                    class="glyphicon glyphicon-info-sign"></i> <?php echo $LanguageInstance->get( 'Session Tandem Information' ) ?>
                        </button>
                    </p>
				<?php } ?>
            </div>
            <div class='col-md-6'>
                <p><br/><br/>
                <div><?php echo $LanguageInstance->get( 'Created' ); ?>:
                    <b><?php echo $extra_feedback_details['created']; ?></b></div>
                <div><?php echo $LanguageInstance->get( 'Exercise' ); ?>:
                    <b><?php echo $extra_feedback_details['exercise']; ?></b></div>
                <div><?php echo $LanguageInstance->get( 'Total Duration' ); ?>:
                    <b><?php echo $extra_feedback_details['total_time']; ?></b></div>
                <div><?php echo $LanguageInstance->get( 'Duration per task' ); ?>:
                    <span style='font-size:11px;font-weight:bold'><?php
						$tt = array();
						foreach ( $extra_feedback_details['total_time_tasks'] as $key => $val ) {
							$tt[] = "T" . ++ $key . " = " . $val;
						}
						echo implode( ", ", $tt );
						?></span>
                </div>
                </p>
            </div>
            <div class='col-md-1'>
                <button type="button" id="button-help" class="btn btn-info"
                        title="<?php echo $LanguageInstance->get( 'Help' ) ?>"><i
                            class="glyphicon glyphicon-question-sign"></i> <?php echo $LanguageInstance->get( 'Help' ) ?>
                </button>
            </div>
        </div>
    </div>
	<?php if ( $message ) {
		echo $message;
	}

	if ( defined( 'BBB_SECRET' ) ) {

		$bbb_path = __DIR__ . '/bbb/BBBIntegration.php';
		if ( file_exists( $bbb_path ) ) {
			require_once $bbb_path;
			$url_video  = $feedbackDetails->external_video_url;
			if (empty($url_video)) {
			    if (!BBBIntegration::endMeeting($feedbackDetails->id_tandem)) {
			        // If we can't end then get if there are 3 hours from last connection
				    $meeting_info = BBBIntegration::checkSessionInfo( $feedbackDetails->id_tandem, false );
				    if ( $meeting_info->getReturnCode() === BBBIntegration::SUCCESS_STATUS ) {
					    $creation_time = round( $meeting_info->getMeeting()->getCreationTime() / 1000 );
					    $dateTime      = new DateTime();
					    $dateTime->sub( new DateInterval( 'PT3H' ) );
					    $time_start = $dateTime->getTimestamp();
					    $previous   = $creation_time < $time_start;
					    if ( $previous ) {
						    BBBIntegration::endMeeting( $feedbackDetails->id_tandem, true );
					    }

				    }
			    }
            }
			$recordings = BBBIntegration::getRecordings( $feedbackDetails->id_tandem );
			if ( count( $recordings ) > 0 ) {
				foreach ( $recordings as $recording ) {

					$playback_url = $recording->getPlaybackUrl() != null ? $recording->getPlaybackUrl() : $recording->getPresentationUrl();
					$gestorBD->storeRecordInformation( $recording->getRecordId(), $feedbackDetails->id_tandem,
						$recording->getName(),
						$recording->isPublished(), $recording->getState(), $recording->getStartTime(),
						$recording->getEndTime(), $recording->getPlaybackType(),
						$recording->getPlaybackUrl(), $recording->getPresentationUrl(), $recording->getPodcastUrl(),
						$recording->getStatisticsUrl(), $recording->getPlaybackLength(), $recording->getMetas() );
					$playback_url = BBBIntegration::uploadRecordingToS3( $recording, $gestorBD, $url_video,
						$feedbackDetails->id_tandem );

					?>
                    <p>
                        <a href="<?php echo empty($url_video)?$playback_url:$url_video; ?>" target="_blank"
                           class="btn2 btn-success"><?php echo $LanguageInstance->get( 'View video session' ) ?></a>

                        <button type="button" id="button-info-modal-<?php echo $recording->getRecordId(); ?>"
                                data-toggle="modal"
                                data-target="#video-info-modal-<?php echo $recording->getRecordId(); ?>"
                                class="btn btn-info"
                                title="<?php echo $LanguageInstance->get( 'Recording Information' ) ?>"><i
                                    class="glyphicon glyphicon-info-sign"></i> <?php echo $LanguageInstance->get( 'Recording Information' ) ?>
                        </button>
                    <div class="modal fade" id="video-info-modal-<?php echo $recording->getRecordId(); ?>" tabindex="-1"
                         role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    <h4 class="modal-title"><?php echo $LanguageInstance->get( 'Video Tandem Information' ) ?></h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <h5><?php echo $LanguageInstance->get( "Video info" ) . ': ' ?></h5>
                                        <ul>
                                            <li><?php echo $LanguageInstance->get( 'Start Time' ); ?>
                                                : <?php echo date( 'd/m/Y H:i:s',
													$recording->getStartTime() / 1000 ); ?></li>
                                            <li><?php echo $LanguageInstance->get( 'End Time' ); ?>
                                                : <?php echo date( 'd/m/Y H:i:s',
													$recording->getEndTime() / 1000 ); ?></li>
                                            <li><?php echo $LanguageInstance->get( 'Playback minutes' ); ?>
                                                : <?php echo $recording->getPlaybackLength(); ?></li>
											<?php if ( $recording->getStatisticsUrl() != null ) { ?>
                                                <li>
                                                    <a href="<?php echo $recording->getStatisticsUrl(); ?>"
                                                       target="_blank"><?php echo $LanguageInstance->get( 'Statistics' ); ?></a>
                                                </li>
											<?php } ?>
											<?php if ( $recording->getPodcastUrl() != null ) { ?>
                                                <li>
                                                    <a href="<?php echo $recording->getPodcastUrl(); ?>"
                                                       target="_blank"><?php echo $LanguageInstance->get( 'Podcast' ); ?></a>
                                                </li>
											<?php } ?>
											<?php if ( $recording->getPresentationUrl() != null ) { ?>
                                                <li>
                                                    <a href="<?php echo $recording->getPresentationUrl(); ?>"
                                                       target="_blank"><?php echo $LanguageInstance->get( 'Presentation' ); ?></a>
                                                </li>
											<?php } ?>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    </p>
					<?php
				}
			} else {

				if ( ! empty( $url_video ) ) {
					// Only we can show it from S3
//
					$recordings = $gestorBD->getRecordingsByTandemId($feedbackDetails->id_tandem);
					?>
                    <p>
                        <a onclick="window.open('<?php echo $url_video; ?>')"
                           class="btn2 btn-success"><?php echo $LanguageInstance->get( 'View video session' ) ?></a>
                        <?php
                        foreach ($recordings as $recording) {
                            ?>
                            <button type="button" id="button-info-modal-<?php echo $recording['recordingId']; ?>"
                                    data-toggle="modal"
                                    data-target="#video-info-modal-<?php echo $recording['recordingId']; ?>"
                                    class="btn btn-info"
                                    title="<?php echo $LanguageInstance->get( 'Recording Information' ) ?>"><i
                                        class="glyphicon glyphicon-info-sign"></i> <?php echo $LanguageInstance->get( 'Recording Information' ) ?>
                            </button>
                            <div class="modal fade" id="video-info-modal-<?php echo $recording['recordingId']; ?>" tabindex="-1"
                                 role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                            <h4 class="modal-title"><?php echo $LanguageInstance->get( 'Video Tandem Information' ) ?></h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <h5><?php echo $LanguageInstance->get( "Video info" ) . ': ' ?></h5>
                                                <ul>
                                                    <li><?php echo $LanguageInstance->get( 'Start Time' ); ?>
                                                        : <?php echo date( 'd/m/Y H:i:s',
									                        $recording['startTime'] / 1000 ); ?></li>
                                                    <li><?php echo $LanguageInstance->get( 'End Time' ); ?>
                                                        : <?php echo date( 'd/m/Y H:i:s',
									                        $recording['endTime'] / 1000 ); ?></li>
                                                    <li><?php echo $LanguageInstance->get( 'Playback minutes' ); ?>
                                                        : <?php echo $recording['playbackLength']; ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </p>
					<?php
				} else {
					?>
                    <p>
						<?php echo $LanguageInstance->get( 'The video is processing' ) ?>
                        <a
                                href="feedback.php?id_feedback=<?php echo $id_feedback ?>"
                                class="btn2 btn-warning alert-warning">
							<?php echo $LanguageInstance->get( 'Check if it is already available' ) ?>
                        </a>
                    </p>
					<?php
				}
			}
		}

	} else {
		if ( $feedbackDetails->id_tandem == $feedbackDetails->id_external_tool ) {
			if ( ! empty( $feedbackDetails->external_video_url ) ) {
				$url_video = get_video_from_s3( $feedbackDetails->external_video_url );
				?>
                <p>
                    <button id="viewVideo" onclick="setJwPlayerVideoUrl('<?php echo $url_video; ?>')" type="button"
                            class="btn btn-success"><?php echo $LanguageInstance->get( 'View video session' ) ?></button>
                    <button onclick="window.open('<?php echo $url_video; ?>')"
                            class="btn btn-success"><?php echo $LanguageInstance->get( 'Download' ) ?></button>
                </p>
			<?php } else { ?>
                <p>
                    <button class="btn btn-warning"
                            disabled="disabled"><?php echo $LanguageInstance->get( 'Video is processing' ) ?></button>
                </p>
			<?php } ?>

		<?php } else {
			if ( $feedbackDetails->id_external_tool > 0 ) {
				?>
                <p>
                    <button id="viewVideo"
                            onclick="window.open('ltiConsumer.php?id=100&<?php echo $feedbackDetails->id_external_tool > 0 ? ( ID_EXTERNAL . '=' . $feedbackDetails->id_external_tool ) : '' ?>&<?php echo $feedbackDetails->id_tandem > 0 ? ( CURRENT_TANDEM . '=' . $feedbackDetails->id_tandem ) : '' ?>')"
                            type="button"
                            class="btn btn-success"><?php echo $LanguageInstance->get( 'View video session' ) ?></button>
                </p>
				<?php
			} else {
				echo '<p ><button class="btn btn-warning" disabled="disabled">' . $LanguageInstance->get( "The video could not be recorded" ) . '</button></p>';
			}
		}
	}

	?>


    <!-- Nav tabs -->
    <div class='row'>
        <div class='col-md-12'>
            <ul class="nav nav-tabs" role="tablist">
                <li class="active"><a href="#main-container_old" role="tab"
                                      data-toggle="tab"><?php echo $LanguageInstance->get( 'Review your Tandem session' ) ?></a>
                </li>
                <li><a href="#other" role="tab"
                       data-toggle="tab"><?php echo $LanguageInstance->get( 'View received feedback' ) ?></a></li>
            </ul>
        </div>
    </div>
    <div class="tab-content">
		<?php if ( $feedback_selfreflection_form ) { ?>
            <div id="main-container_old" class='tab-pane active send-feedback-pane'>
                <div class="row">
                    <div class="col-md-12">
                        <div class="post-feedback-alerts-container"></div>
                    </div>
                    <div class="col-md-6" id="self-reflection-form-wrapper">
                        <h3><?php echo $LanguageInstance->get( 'Self-reflection' ) ?></h3>
                        <h4 style="display:inline-block;"><?php echo $LanguageInstance->get( '1. One or more things I did well.' ) ?></h4>
                        <div class="info" data-toggle="modal" data-target="#selfreflection-feedback-info-1"
                             style="display:initial;cursor:pointer"><img src="images/info.png"></div>
                        <ul class="list-unstyled feedback-list" id="selfreflection-feedback-list-1">
							<?php
							if ( ! empty( $feedback_form->selfReflection['achievements'] ) ) {
								foreach ( $feedback_form->selfReflection['achievements'] as $achievement ) {
									echo "<li class='panel panel-default'><div class='panel-body'>";
									echo "<span class='added-feedback'>$achievement</span>";
									if ( $can_edit ) {
										echo "<a href='javascript:' class='close' aria-hidden='true'>&times;</a>";
									}
									echo '</div></li>';
								}
							}
							?>
                        </ul>
						<?php if ( $can_edit ) { ?>
                            <form role="form" class="feedback-form" id="selfreflection-feedback-form-1"
                                  action="javascript:">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                               placeholder="<?php echo $LanguageInstance->get( 'Write one item' ) ?>"
                                               name="selfreflection-feedback-1">
                                        <span class="input-group-btn">
                                            <button type="button" id="selfreflection-feedback-button-1"
                                                    class="btn btn-success">
                                                <?php echo $LanguageInstance->get( '+ Add' ) ?>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="help-block validation-errors hidden"></div>
                                </div>
                            </form>
						<?php } ?>
                        <h4 style="display:inline-block;"><?php echo $LanguageInstance->get( '2. Three language errors I made.' ) ?></h4>
                        <div class="info" data-toggle="modal" data-target="#selfreflection-feedback-info-2"
                             style="display:initial;cursor:pointer"><img src="images/info.png"></div>
                        <ul class="list-unstyled feedback-list" id="selfreflection-feedback-list-2">
							<?php
							if ( ! empty( $feedback_form->selfReflection['errors'] ) ) {
								foreach ( $feedback_form->selfReflection['errors'] as $error ) {
									echo "<li class='panel panel-default'><div class='panel-body'>";
									echo "<span class='added-feedback'>$error</span>";
									if ( $can_edit ) {
										echo "<a href='javascript:' class='close' aria-hidden='true'>&times;</a>";
									}
									echo '</div></li>';
								}
							}
							?>
                        </ul>
						<?php if ( $can_edit ) { ?>
                            <form role="form" class="feedback-form" id="selfreflection-feedback-form-2"
                                  action="javascript:">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                               placeholder="<?php echo $LanguageInstance->get( 'Write one item' ) ?>"
                                               name="selfreflection-feedback-2">
                                        <span class="input-group-btn">
                                            <button type="button" id="selfreflection-feedback-button-2"
                                                    class="btn btn-success">
                                                <?php echo $LanguageInstance->get( '+ Add' ) ?>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="help-block validation-errors hidden"></div>
                                </div>
                            </form>
						<?php } ?>
                        <h4 style="display:inline-block;"><?php echo $LanguageInstance->get( '3. Other comments.' ) ?></h4>
                        <div class="info" data-toggle="modal" data-target="#selfreflection-feedback-info-3"
                             style="display:initial;cursor:pointer"><img src="images/info.png"></div>
						<?php if ( $can_edit ) { ?>
                            <form role="form" class="feedback-form" id="selfreflection-feedback-form-3"
                                  action="javascript:">
                                <div class="form-group">
                                    <textarea class="form-control" rows="3"
                                              placeholder="<?php echo $LanguageInstance->get( 'Add other comments' ) ?>"
                                              name="selfreflection-feedback-3"></textarea>
                                    <div class="help-block validation-errors hidden"></div>
                                </div>
                            </form>
						<?php } ?>
                        <p class="<?php if ( $can_edit ) {
							echo 'hidden';
						} ?>" id="saved-self-other-comments">
							<?php
							if ( ! empty( $feedback_form->selfReflection['comments'] ) ) {
								echo $feedback_form->selfReflection['comments'];
							} else {
								echo '-';
							}
							?>
                        </p>
                    </div>
                    <div class="col-md-6" id="peer-feedback-form-wrapper">
                        <h3><?php echo $LanguageInstance->get( 'Peer-feedback' ) ?></h3>
                        <h4 style="display:inline-block;"><?php echo $LanguageInstance->get( '1. One or more things your partner did well.' ) ?></h4>
                        <div class="info" data-toggle="modal" data-target="#partner-feedback-info-1"
                             style="display:initial;cursor:pointer"><img src="images/info.png"></div>
                        <ul class="list-unstyled feedback-list" id="partner-feedback-list-1">
							<?php
							if ( ! empty( $feedback_form->peerFeedback['achievements'] ) ) {
								foreach ( $feedback_form->peerFeedback['achievements'] as $achievement ) {
									echo "<li class='panel panel-default'><div class='panel-body'>";
									echo "<span class='added-feedback'>$achievement</span>";
									if ( $can_edit ) {
										echo "<a href='javascript:' class='close' aria-hidden='true'>&times;</a>";
									}
									echo '</div></li>';
								}
							}
							?>
                        </ul>
						<?php if ( $can_edit ) { ?>
                            <form role="form" class="feedback-form" id="partner-feedback-form-1" action="javascript:">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                               placeholder="<?php echo $LanguageInstance->get( 'Write one item' ) ?>"
                                               name="partner-feedback-1">
                                        <span class="input-group-btn">
                                            <button type="button" id="partner-feedback-button-1"
                                                    class="btn btn-success">
                                                <?php echo $LanguageInstance->get( '+ Add' ) ?>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="help-block validation-errors hidden"></div>
                                </div>
                            </form>
						<?php } ?>
                        <h4 style="display:inline-block;"><?php echo $LanguageInstance->get( '2. Three language errors your partner made.' ) ?></h4>
                        <div class="info" data-toggle="modal" data-target="#partner-feedback-info-2"
                             style="display:initial;cursor:pointer"><img src="images/info.png"></div>
                        <ul class="list-unstyled feedback-list" id="partner-feedback-list-2">
							<?php
							if ( ! empty( $feedback_form->peerFeedback['errors'] ) ) {
								foreach ( $feedback_form->peerFeedback['errors'] as $error ) {
									echo "<li class='panel panel-default'><div class='panel-body'>";
									echo "<span class='added-feedback'>$error</span>";
									if ( $can_edit ) {
										echo "<a href='javascript:' class='close' aria-hidden='true'>&times;</a>";
									}
									echo '</div></li>';
								}
							}
							?>
                        </ul>
						<?php if ( $can_edit ) { ?>
                            <form role="form" class="feedback-form" id="partner-feedback-form-2" action="javascript:">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                               placeholder="<?php echo $LanguageInstance->get( 'Write one item' ) ?>"
                                               name="partner-feedback-2">
                                        <span class="input-group-btn">
                                            <button type="button" id="partner-feedback-button-2"
                                                    class="btn btn-success">
                                                <?php echo $LanguageInstance->get( '+ Add' ) ?>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="help-block validation-errors hidden"></div>
                                </div>
                            </form>
						<?php } ?>
                        <h4 style="display:inline-block;"><?php echo $LanguageInstance->get( '3. Other comments.' ) ?></h4>
                        <div class="info" data-toggle="modal" data-target="#partner-feedback-info-3"
                             style="display:initial;cursor:pointer"><img src="images/info.png"></div>
						<?php if ( $can_edit ) { ?>
                            <form role="form" class="feedback-form" id="partner-feedback-form-3" action="javascript:">
                                <div class="form-group">
                                    <textarea class="form-control" rows="3"
                                              placeholder="<?php echo $LanguageInstance->get( 'Add other comments' ) ?>"
                                              name="partner-feedback-3"></textarea>
                                    <div class="help-block validation-errors hidden"></div>
                                </div>
                            </form>
						<?php } ?>
                        <p class="<?php if ( $can_edit ) {
							echo 'hidden';
						} ?>" id="saved-partner-other-comments">
							<?php
							if ( ! empty( $feedback_form->peerFeedback['comments'] ) ) {
								echo $feedback_form->peerFeedback['comments'];
							} else {
								echo '-';
							}
							?>
                        </p>
                    </div>
                </div>
                <div class="row text-right" id="save-feedback-data-wrapper">
					<?php if ( $can_edit ) { ?>
                        <button type="button" id="save-feedback-data" class="btn btn-primary">
							<?php echo $LanguageInstance->get( 'Submit feedback' ) ?>
                        </button>
					<?php } ?>
                    <br>
                    <br>
                    <br>
                </div>
            </div>
            <div id='other' class="tab-pane received-feedback-pane">
				<?php
				if ( ! empty( $partnerFeedback ) ) {
					$feedBackFormPartner = unserialize( $partnerFeedback );
					?>
                    <div class="row">
                        <div class="col-md-6" id="received-feedback-wrapper">
                            <h3><?php echo $LanguageInstance->get( 'Received feedback' ) ?></h3>
                            <h4><?php echo $LanguageInstance->get( '1. One or more things I did well.' ) ?></h4>
                            <span><em><?php echo $LanguageInstance->get( 'According to my partner:' ) ?></em></span>
                            <ul class="list-unstyled feedback-list">
								<?php
								if ( ! empty( $feedBackFormPartner->peerFeedback['achievements'] ) ) {
									foreach ( $feedBackFormPartner->peerFeedback['achievements'] as $achievement ) {
										echo "<li class='panel panel-default'><div class='panel-body'>";
										echo "<span class='added-feedback'>$achievement</span>";
										echo '</div></li>';
									}
								}
								?>
                            </ul>
                            <span><em><?php echo $LanguageInstance->get( 'According to myself:' ) ?></em></span>
                            <ul class="list-unstyled feedback-list">
								<?php
								if ( ! empty( $feedback_form->selfReflection['achievements'] ) ) {
									foreach ( $feedback_form->selfReflection['achievements'] as $achievement ) {
										echo "<li class='panel panel-default'><div class='panel-body'>";
										echo "<span class='added-feedback'>$achievement</span>";
										echo '</div></li>';
									}
								}
								?>
                            </ul>
                            <h4><?php echo $LanguageInstance->get( '2. Three language errors I made.' ) ?></h4>
                            <span><em><?php echo $LanguageInstance->get( 'According to my partner:' ) ?></em></span>
                            <ul class="list-unstyled feedback-list">
								<?php
								if ( ! empty( $feedBackFormPartner->peerFeedback['errors'] ) ) {
									foreach ( $feedBackFormPartner->peerFeedback['errors'] as $error ) {
										echo "<li class='panel panel-default'><div class='panel-body'>";
										echo "<span class='added-feedback'>$error</span>";
										echo '</div></li>';
									}
								}
								?>
                            </ul>
                            <span><em><?php echo $LanguageInstance->get( 'According to myself:' ) ?></em></span>
                            <ul class="list-unstyled feedback-list">
								<?php
								if ( ! empty( $feedback_form->selfReflection['errors'] ) ) {
									foreach ( $feedback_form->selfReflection['errors'] as $error ) {
										echo "<li class='panel panel-default'><div class='panel-body'>";
										echo "<span class='added-feedback'>$error</span>";
										echo '</div></li>';
									}
								}
								?>
                            </ul>
                            <h4><?php echo $LanguageInstance->get( '3. Other comments.' ) ?></h4>
                            <span><em><?php echo $LanguageInstance->get( 'According to my partner:' ) ?></em></span>

                            <textarea rows="3" cols="200" class="form-control" readonly="readonly">
                                    <?php
                                    if ( ! empty( $feedBackFormPartner->peerFeedback['comments'] ) ) {
	                                    echo $feedBackFormPartner->peerFeedback['comments'];
                                    }
                                    ?>
                                    </textarea>
                            <span><em><?php echo $LanguageInstance->get( 'According to my myself:' ) ?></em></span>

                            <textarea rows="3" cols="200" class="form-control" readonly="readonly">
                                    <?php
                                    if ( ! empty( $feedback_form->selfReflection['comments'] ) ) {
	                                    echo $feedback_form->selfReflection['comments'];
                                    }
                                    ?>
                                    </textarea>

                        </div>
                        <div class="col-md-6" id="partner-review-rating-wrapper">
                            <br class="hidden-sm hidden-xs">
                            <!-- Rate your partner form -->
                            <div class='well partner-review-rating-container'>
                                <h3><?php echo $LanguageInstance->get( 'Rate Partner’s Feedback Form' ) ?></h3>
                                <form action='' method='POST'>
                                    <div class="form-group">
                                        <input name="partner_rate" id="partner_rate" type="text" class="rating"
                                               data-min="0" data-max="5" data-step="1" data-size="sm"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="partner_comment"
                                               class="control-label"><?php echo $LanguageInstance->get( 'Comments' ); ?>
                                            :</label>
                                        <div class="input-group">
                                                <textarea rows="3" cols="200" class="form-control" id="partner_comment"
                                                          name="partner_comment"
                                                          placeholder="<?php echo $LanguageInstance->get( 'Add comments' ) ?>"
                                                    <?php if ( ! empty( $feedbackDetails->rating_partner_feedback_form->partner_comment ) ) {
	                                                    echo 'readonly';
                                                    } ?>
                                                ><?php
	                                                if ( ! empty( $feedbackDetails->rating_partner_feedback_form->partner_comment ) ) {
		                                                echo $feedbackDetails->rating_partner_feedback_form->partner_comment;
	                                                }
	                                                ?></textarea>
                                        </div>
                                    </div>
                                    <input type='hidden' name='rating_partner' value='1'/>
                                    <input type='hidden' name='id_feedback' value='<?php echo $id_feedback; ?>'/>
									<?php if ( empty( $feedbackDetails->rating_partner_feedback_form ) ) { ?>
                                        <button type="submit"
                                                class="btn btn-success"><?php echo $LanguageInstance->get( 'Send' ); ?></button>
									<?php } ?>
                                    <span class="small"><?php echo $LanguageInstance->get( 'cannot_be_modified' ); ?></span>
                                </form>
                            </div>
                        </div>
                    </div>
					<?php
				} else {
					$feedBackIdTab = isset( $feedbackDetails->id ) ? '?id_feedback=' . $feedbackDetails->id . '&tab=other' : ''; ?>
                    <p><?php echo $LanguageInstance->get( 'partner_feedback_not_available' ); ?></p>
                    <button id="checkFeedbacks"
                            type="button"
                            onclick="window.location = 'feedback.php<?php echo $feedBackIdTab; ?>'"
                            class="btn btn-success">
						<?php echo $LanguageInstance->get( 'Check if feedback has been submitted' ) ?>
                    </button>
					<?php
				} ?>
            </div>
		<?php } else { ?>
            <div id='other' class="tab-pane">
				<?php
				if ( ! empty( $partnerFeedback ) ) {
					$feedBackFormPartner = unserialize( $partnerFeedback );
					if ( $_SESSION[ USE_WAITING_ROOM_NO_TEAMS ] ) { ?>
                        <div class="form-group">
                            <label for="grammaticalresource"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Grammatical Resource' ) ?></label>
							<?php echo $feedBackFormPartner->grammaticalresource ?> %
                        </div>
                        <div class="form-group">
                            <label for="lexicalresource"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Lexical Resource' ) ?></label>
							<?php echo $feedBackFormPartner->lexicalresource ?> %
                        </div>
                        <div class="form-group">
                            <label for="discoursemangement"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Discourse Mangement' ) ?></label>
							<?php echo $feedBackFormPartner->discoursemangement ?> %
                        </div>
                        <div class="form-group">
                            <label for="interactivecommunication"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Interactive Communication' ) ?></label>
							<?php echo $feedBackFormPartner->interactivecommunication ?> %
                        </div>
                        <div class="form-group">
                            <label for="pronunciation"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Pronunciation' ) ?></label>
							<?php echo $feedBackFormPartner->pronunciation ?> %
                        </div>
                        <!--div class="form-group">
                                                        <label for="other_observations" class="control-label"><?php echo $LanguageInstance->get( 'Other Observations' ) ?></label>
                                                        <div class="input-group">
                                                          <textarea  readonly rows="3" cols="200" class="form-control" id="other_observations" name="other_observations" placeholder="<?php echo $LanguageInstance->get( 'Indicate other observations' ) ?>"><?php echo $feedBackFormPartner->other_observations ?></textarea>
                                                        </div>
                                                    </div-->
						<?php
					} else {
						?>
                        <div class="form-group">
                            <label for="fluency"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Fluency' ) ?></label>
							<?php echo $feedBackFormPartner->fluency ?> %
                        </div>
                        <div class="form-group">
                            <label for="accuracy"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Accuracy' ) ?></label>
							<?php echo $feedBackFormPartner->accuracy ?> %
                        </div>
                        <div class="form-group">
                            <label for="grade"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Overall Grade' ) ?></label>
                            <select disabled id="grade" name="grade" required>
                                <option><?php echo $LanguageInstance->get( 'Select one' ) ?></option>
                                <option value="A" <?php echo $feedBackFormPartner->grade === 'A' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Excellent' ) ?></option>
                                <option value="B" <?php echo $feedBackFormPartner->grade === 'B' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Very Good' ) ?></option>
                                <option value="C" <?php echo $feedBackFormPartner->grade === 'C' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Good' ) ?></option>
                                <option value="D" <?php echo $feedBackFormPartner->grade === 'D' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Pass' ) ?></option>
                                <option value="F" <?php echo $feedBackFormPartner->grade === 'F' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Fail' ) ?></option>
                            </select>
                        </div>
                        <!--div class="row"><h3><?php //echo $LanguageInstance->get('Room for improvement')
						?></h3></div-->

                        <div class="form-group">
                            <label for="pronunciation"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Pronunciation' ) ?></label>
                            <div class="input-group">
                                <textarea readonly rows="3" cols="200" class="form-control" id="pronunciation"
                                          name='pronunciation'
                                          placeholder="<?php echo $LanguageInstance->get( 'Indicate the level of pronunciation' ) ?>"
                                          required><?php echo $feedBackFormPartner->pronunciation ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="vocabulary"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Vocabulary' ) ?></label>
                            <div class="input-group">
                                <textarea readonly rows="3" cols="200" class="form-control" id="vocabulary"
                                          name='vocabulary'
                                          placeholder="<?php echo $LanguageInstance->get( 'Indicate the level of vocabulary' ) ?>"
                                          required><?php echo $feedBackFormPartner->vocabulary ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="grammar"
                                   class="control-label"><?php echo $LanguageInstance->get( 'Grammar' ) ?></label>
                            <div class="input-group">
                                <textarea readonly rows="3" cols="200" class="form-control" id="grammar" name="grammar"
                                          placeholder="<?php echo $LanguageInstance->get( 'Indicate the level of grammar' ) ?>"
                                          required><?php echo $feedBackFormPartner->grammar ?></textarea>
                            </div>
                        </div>
                        <!--div class="form-group">
								<label for="other_observations" class="control-label"><?php echo $LanguageInstance->get( 'Other Observations' ) ?></label>
								<div class="input-group">
								  <textarea  readonly rows="3" cols="200" class="form-control" id="other_observations" name="other_observations" placeholder="<?php echo $LanguageInstance->get( 'Indicate other observations' ) ?>"><?php echo $feedBackFormPartner->other_observations ?></textarea>
								</div>
							  </div-->
					<?php } ?>
                    <!-- Rate your partner form -->
                    <div class='row well'>
                        <h3><?php echo $LanguageInstance->get( 'Rating Partner’s Feedback Form' ) ?></h3>
                        <form action='' method='POST'>
                            <div class="form-group">
                                <label for="partner_rate"
                                       class="control-label"><?php echo $LanguageInstance->get( 'Rate your partners feedback' ) ?></label>
                                <input name="partner_rate" id="partner_rate" type="text" class="rating" data-min="0"
                                       data-max="5" data-step="1" data-size="sm"/>
                            </div>
                            <div class="form-group">
                                <label for="partner_comment"
                                       class="control-label"><?php echo $LanguageInstance->get( 'Comments' ) ?>:</label>
                                <div class="input-group">
                                    <textarea rows="3" cols="200" class="form-control" id="partner_comment"
                                              name="partner_comment"
                                              placeholder="<?php echo $LanguageInstance->get( 'Indicate comments' ) ?>" <?php if ( ! empty( $feedbackDetails->rating_partner_feedback_form->partner_comment ) ) {
	                                    echo "readonly";
                                    } ?> ><?php if ( ! empty( $feedbackDetails->rating_partner_feedback_form->partner_comment ) ) {
		                                    echo $feedbackDetails->rating_partner_feedback_form->partner_comment;
	                                    } ?></textarea>
                                </div>
                            </div>
                            <input type='hidden' name='rating_partner' value='1'/>
                            <input type='hidden' name='id_feedback' value='<?php echo $id_feedback ?>'/>
							<?php if ( empty( $feedbackDetails->rating_partner_feedback_form ) ) { ?>
                                <button type="submit"
                                        class="btn btn-success"><?php echo $LanguageInstance->get( 'Send' ) ?></button>
							<?php } ?>
                            <span class="small"><?php echo $LanguageInstance->get( 'cannot_be_modified' ) ?></span>
                        </form>
                    </div>
					<?php
				} else {
					echo "<p>" . $LanguageInstance->get( 'partner_feedback_not_available' ) . "</p>";
					if ( isset( $feedbackDetails->id ) ) {
						$feedBackIdTab = "?id_feedback=" . $feedbackDetails->id . "&tab=other";
					} else {
						$feedBackIdTab = "";
					}
					?>
                    <button id="checkFeedbacks" type="button"
                            onclick="window.location = 'feedback.php<?php echo $feedBackIdTab; ?>'"
                            class="btn btn-success"><?php echo $LanguageInstance->get( 'Check if feedback are submitted' ) ?></button>
				<?php } ?>
            </div>
            <div id="main-container_old" class='tab-pane active'>
                <div class='row'>
                    <div class='col-md-12'>
                        <!-- main -->
                        <div id="main_old">

							<?php if ( $_SESSION[ USE_WAITING_ROOM_NO_TEAMS ] ) { ?>
                            <!-- content -->
                            <div id="content_old">
                                <form data-toggle="validator" role="form" method="POST">
                                    <div class="form-group">
                                        <label for="grammatical-resource-rubric" class="control-label">Descriptor de
                                            Rúbrica <?php echo $LanguageInstance->get( 'Grammatical Resource' ) ?>
                                            :</label>
                                        <select id="grammatical-resource-rubric" name="grammatical-resource-rubric">
                                            <option value="">Select one</option>
                                            <option value="A">Bla bla bla bla bla bla bla bla bla bla bla bla bla
                                            </option>
                                            <option value="B">Ble ble ble ble ble ble ble ble ble ble ble ble ble
                                            </option>
                                            <option value="C">Bli bli bli bli bli bli bli bli bli bli bli bli bli
                                            </option>
                                        </select>
                                        <!-- trigger modal -->
                                        <div class="info" data-toggle="modal"
                                             data-target="#grammatical-resource-infoModal"
                                             style="display:initial; cursor:pointer"><img src="images/info.png"></div>
                                        <!-- Modal -->
                                        <div class="modal fade" id="grammatical-resource-infoModal" tabindex="-1"
                                             role="dialog" aria-hidden="true" style="display: none;">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                        <h4 class="modal-title" id="grammatical-resource-modal-label">
                                                            More information</h4>
                                                    </div>
                                                    <div class="modal-body" id="grammatical-resource-modal-body">
                                                        <div>
                                                            <ul>
                                                                <li><b>Bla bla bla bla bla bla bla bla bla bla bla bla
                                                                        bla</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                                <li><b>Ble ble ble ble ble ble ble ble ble ble ble ble
                                                                        ble</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                                <li><b>Bli bli bli bli bli bli bli bli bli bli bli bli
                                                                        bli</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-success"
                                                                data-dismiss="modal">Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="grammatical-resource-text-info" style="display: none;">
                                            <span id="grammatical-resource-text-info-span"></span>
                                            <div class="info" data-toggle="modal"
                                                 data-target="#grammatical-resource-info-modal"
                                                 style="display:initial; cursor:pointer"><img src="images/info.png">
                                            </div>
                                            <!-- Modal -->
                                            <div class="modal fade" id="grammatical-resource-info-modal" tabindex="-1"
                                                 role="dialog" aria-hidden="true" style="display: none;">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                            <h4 class="modal-title"
                                                                id="grammatical-resource-info-modal-label">Bla bla bla
                                                                bla bla bla bla bla bla bla bla bla bla</h4>
                                                        </div>
                                                        <div class="modal-body"
                                                             id="grammatical-resource-info-modal-body">
                                                            <div>Lorem ipsum dolor sit amet, consectetur adipiscing
                                                                elit. Vivamus sollicitudin diam dui, nec efficitur orci
                                                                iaculis a. Nunc quis lectus eget dui pharetra rhoncus id
                                                                id tellus. Praesent sed ornare turpis, a auctor lectus.
                                                                Ut imperdiet tempor lorem ut condimentum. Suspendisse
                                                                sed ornare lectus. Aenean eget nunc eu purus bibendum
                                                                tristique ac sed eros. Duis pretium tellus in neque
                                                                tempus, a suscipit tellus maximus. Duis sodales nulla in
                                                                quam auctor, vitae congue felis vestibulum. Phasellus eu
                                                                lobortis erat, condimentum vehicula leo. Interdum et
                                                                malesuada fames ac ante ipsum primis in faucibus. Sed
                                                                gravida turpis vel neque sagittis, in mattis lacus
                                                                vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-success"
                                                                    data-dismiss="modal">Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="grammaticalresource"
                                               class="control-label"><?php echo $LanguageInstance->get( 'Grammatical Resource' ) ?>
                                            *</label>
                                        <input data-slider-id='ex1Slider' <?php echo ( ! $can_edit ) ? "data-slider-enabled='0'" : "" ?>
                                               class="sliderTandem" name="grammaticalresource" id="grammaticalresource"
                                               type="text" data-slider-min="0" data-slider-max="100"
                                               data-slider-step="1"
                                               data-slider-value="<?php echo $feedback_form_new->grammaticalresource ?>"/>%
                                        <p class="help-block"><?php echo $LanguageInstance->get( 'Please move the slider to set a value' ) ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="lexical-resource-rubric" class="control-label">Descriptor de
                                            Rúbrica <?php echo $LanguageInstance->get( 'Lexical Resource' ) ?>:</label>
                                        <select id="lexical-resource-rubric" name="lexical-resource-rubric">
                                            <option value="">Select one</option>
                                            <option value="A">Bla bla bla bla bla bla bla bla bla bla bla bla bla
                                            </option>
                                            <option value="B">Ble ble ble ble ble ble ble ble ble ble ble ble ble
                                            </option>
                                            <option value="C">Bli bli bli bli bli bli bli bli bli bli bli bli bli
                                            </option>
                                        </select>
                                        <!-- trigger modal -->
                                        <div class="info" data-toggle="modal" data-target="#lexical-resource-infoModal"
                                             style="display:initial; cursor:pointer"><img src="images/info.png"></div>
                                        <!-- Modal -->
                                        <div class="modal fade" id="lexical-resource-infoModal" tabindex="-1"
                                             role="dialog" aria-hidden="true" style="display: none;">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                        <h4 class="modal-title" id="lexical-resource-modal-label">More
                                                            information</h4>
                                                    </div>
                                                    <div class="modal-body" id="lexical-resource-modal-body">
                                                        <div>
                                                            <ul>
                                                                <li><b>Bla bla bla bla bla bla bla bla bla bla bla bla
                                                                        bla</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                                <li><b>Ble ble ble ble ble ble ble ble ble ble ble ble
                                                                        ble</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                                <li><b>Bli bli bli bli bli bli bli bli bli bli bli bli
                                                                        bli</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-success"
                                                                data-dismiss="modal">Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="lexical-resource-text-info" style="display: none;">
                                            <span id="lexical-resource-text-info-span"></span>
                                            <div class="info" data-toggle="modal"
                                                 data-target="#lexical-resource-info-modal"
                                                 style="display:initial; cursor:pointer"><img src="images/info.png">
                                            </div>
                                            <!-- Modal -->
                                            <div class="modal fade" id="lexical-resource-info-modal" tabindex="-1"
                                                 role="dialog" aria-hidden="true" style="display: none;">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                            <h4 class="modal-title"
                                                                id="lexical-resource-info-modal-label">Bla bla bla bla
                                                                bla bla bla bla bla bla bla bla bla</h4>
                                                        </div>
                                                        <div class="modal-body" id="lexical-resource-info-modal-body">
                                                            <div>Lorem ipsum dolor sit amet, consectetur adipiscing
                                                                elit. Vivamus sollicitudin diam dui, nec efficitur orci
                                                                iaculis a. Nunc quis lectus eget dui pharetra rhoncus id
                                                                id tellus. Praesent sed ornare turpis, a auctor lectus.
                                                                Ut imperdiet tempor lorem ut condimentum. Suspendisse
                                                                sed ornare lectus. Aenean eget nunc eu purus bibendum
                                                                tristique ac sed eros. Duis pretium tellus in neque
                                                                tempus, a suscipit tellus maximus. Duis sodales nulla in
                                                                quam auctor, vitae congue felis vestibulum. Phasellus eu
                                                                lobortis erat, condimentum vehicula leo. Interdum et
                                                                malesuada fames ac ante ipsum primis in faucibus. Sed
                                                                gravida turpis vel neque sagittis, in mattis lacus
                                                                vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-success"
                                                                    data-dismiss="modal">Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="lexicalresource"
                                               class="control-label"><?php echo $LanguageInstance->get( 'Lexical Resource' ) ?>
                                            *</label>
                                        <input data-slider-id='ex2Slider' <?php echo ( ! $can_edit ) ? "data-slider-enabled='0'" : "" ?>
                                               class="sliderTandem" name="lexicalresource" id="lexicalresource"
                                               type="text" data-slider-min="0" data-slider-max="100"
                                               data-slider-step="1"
                                               data-slider-value="<?php echo $feedback_form_new->lexicalresource ?>"/>%
                                        <p class="help-block"><?php echo $LanguageInstance->get( 'Please move the slider to set a value' ) ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="discoursemangement"
                                               class="control-label"><?php echo $LanguageInstance->get( 'Discourse Mangement' ) ?>
                                            *</label>
                                        <input data-slider-id='ex3Slider' <?php echo ( ! $can_edit ) ? "data-slider-enabled='0'" : "" ?>
                                               class="sliderTandem" name="discoursemangement" id="discoursemangement"
                                               type="text" data-slider-min="0" data-slider-max="100"
                                               data-slider-step="1"
                                               data-slider-value="<?php echo $feedback_form_new->discoursemangement ?>"/>%
                                        <p class="help-block"><?php echo $LanguageInstance->get( 'Please move the slider to set a value' ) ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="pronunciation-resource-rubric" class="control-label">Descriptor de
                                            Rúbrica <?php echo $LanguageInstance->get( 'Pronunciation' ) ?>:</label>
                                        <select id="pronunciation-resource-rubric" name="pronunciation-resource-rubric">
                                            <option value="">Select one</option>
                                            <option value="A">Bla bla bla bla bla bla bla bla bla bla bla bla bla
                                            </option>
                                            <option value="B">Ble ble ble ble ble ble ble ble ble ble ble ble ble
                                            </option>
                                            <option value="C">Bli bli bli bli bli bli bli bli bli bli bli bli bli
                                            </option>
                                        </select>
                                        <!-- trigger modal -->
                                        <div class="info" data-toggle="modal"
                                             data-target="#pronunciation-resource-infoModal"
                                             style="display:initial; cursor:pointer"><img src="images/info.png"></div>
                                        <!-- Modal -->
                                        <div class="modal fade" id="pronunciation-resource-infoModal" tabindex="-1"
                                             role="dialog" aria-hidden="true" style="display: none;">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                        <h4 class="modal-title" id="pronunciation-resource-modal-label">
                                                            More information</h4>
                                                    </div>
                                                    <div class="modal-body" id="pronunciation-resource-modal-body">
                                                        <div>
                                                            <ul>
                                                                <li><b>Bla bla bla bla bla bla bla bla bla bla bla bla
                                                                        bla</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                                <li><b>Ble ble ble ble ble ble ble ble ble ble ble ble
                                                                        ble</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                                <li><b>Bli bli bli bli bli bli bli bli bli bli bli bli
                                                                        bli</b> Lorem ipsum dolor sit amet, consectetur
                                                                    adipiscing elit. Vivamus sollicitudin diam dui, nec
                                                                    efficitur orci iaculis a. Nunc quis lectus eget dui
                                                                    pharetra rhoncus id id tellus. Praesent sed ornare
                                                                    turpis, a auctor lectus. Ut imperdiet tempor lorem
                                                                    ut condimentum. Suspendisse sed ornare lectus.
                                                                    Aenean eget nunc eu purus bibendum tristique ac sed
                                                                    eros. Duis pretium tellus in neque tempus, a
                                                                    suscipit tellus maximus. Duis sodales nulla in quam
                                                                    auctor, vitae congue felis vestibulum. Phasellus eu
                                                                    lobortis erat, condimentum vehicula leo. Interdum et
                                                                    malesuada fames ac ante ipsum primis in faucibus.
                                                                    Sed gravida turpis vel neque sagittis, in mattis
                                                                    lacus vulputate. Cras quis nibh nunc. Nullam ac
                                                                    euismod nisl.
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-success"
                                                                data-dismiss="modal">Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="pronunciation-resource-text-info" style="display: none;">
                                            <span id="pronunciation-resource-text-info-span"></span>
                                            <div class="info" data-toggle="modal"
                                                 data-target="#pronunciation-resource-info-modal"
                                                 style="display:initial; cursor:pointer"><img src="images/info.png">
                                            </div>
                                            <!-- Modal -->
                                            <div class="modal fade" id="pronunciation-resource-info-modal" tabindex="-1"
                                                 role="dialog" aria-hidden="true" style="display: none;">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                            <h4 class="modal-title"
                                                                id="pronunciation-resource-info-modal-label">Bla bla bla
                                                                bla bla bla bla bla bla bla bla bla bla</h4>
                                                        </div>
                                                        <div class="modal-body"
                                                             id="pronunciation-resource-info-modal-body">
                                                            <div>Lorem ipsum dolor sit amet, consectetur adipiscing
                                                                elit. Vivamus sollicitudin diam dui, nec efficitur orci
                                                                iaculis a. Nunc quis lectus eget dui pharetra rhoncus id
                                                                id tellus. Praesent sed ornare turpis, a auctor lectus.
                                                                Ut imperdiet tempor lorem ut condimentum. Suspendisse
                                                                sed ornare lectus. Aenean eget nunc eu purus bibendum
                                                                tristique ac sed eros. Duis pretium tellus in neque
                                                                tempus, a suscipit tellus maximus. Duis sodales nulla in
                                                                quam auctor, vitae congue felis vestibulum. Phasellus eu
                                                                lobortis erat, condimentum vehicula leo. Interdum et
                                                                malesuada fames ac ante ipsum primis in faucibus. Sed
                                                                gravida turpis vel neque sagittis, in mattis lacus
                                                                vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-success"
                                                                    data-dismiss="modal">Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pronunciation"
                                               class="control-label"><?php echo $LanguageInstance->get( 'Pronunciation' ) ?>
                                            *</label>
                                        <input data-slider-id='ex4Slider' <?php echo ( ! $can_edit ) ? "data-slider-enabled='0'" : "" ?>
                                               class="sliderTandem" name="pronunciation" id="pronunciation" type="text"
                                               data-slider-min="0" data-slider-max="100" data-slider-step="1"
                                               data-slider-value="<?php echo $feedback_form_new->pronunciation ?>"/>%
                                        <p class="help-block"><?php echo $LanguageInstance->get( 'Please move the slider to set a value' ) ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="interactivecommunication"
                                               class="control-label"><?php echo $LanguageInstance->get( 'Interactive Communication' ) ?>
                                            *</label>
                                        <input data-slider-id='ex5Slider' <?php echo ( ! $can_edit ) ? "data-slider-enabled='0'" : "" ?>
                                               class="sliderTandem" name="interactivecommunication"
                                               id="interactivecommunication" type="text" data-slider-min="0"
                                               data-slider-max="100" data-slider-step="1"
                                               data-slider-value="<?php echo $feedback_form_new->interactivecommunication ?>"/>%
                                        <p class="help-block"><?php echo $LanguageInstance->get( 'Please move the slider to set a value' ) ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="other_observations"
                                               class="control-label"><?php echo $LanguageInstance->get( 'Other Observations' ) ?></label>
                                        <div class="input-group">
                                            <textarea rows="3" cols="200" class="form-control" id="other_observations"
                                                      name="other_observations"
                                                      placeholder="<?php echo $LanguageInstance->get( 'Indicate other observations' ) ?>"><?php echo $feedback_form_new->other_observations ?></textarea>
                                        </div>
                                    </div>
									<?php if ( $can_edit ) { ?>
                                        <div class="form-group">
                                            <small><?php echo $LanguageInstance->get( 'Required fields are noted with an asterisk (*)' ) ?></small>
                                        </div>
                                        <div class="form-group">
                                            <input type='hidden' name='save_feedback_new' value='1'>
                                            <button id="submitBtn" type="submit"
                                                    class="btn btn-success"><?php echo $LanguageInstance->get( 'Send' ) ?></button>
                                            <span class="small"><?php echo $LanguageInstance->get( 'cannot_be_modified' ) ?></span>
                                        </div>
									<?php } ?>
                                    <input type='hidden' name='id_feedback' value='<?php echo $id_feedback ?>'/>
                                </form>
								<?php }else{ ?>
                                <!-- content -->
                                <div id="content_old">
                                    <form data-toggle="validator" role="form" method="POST">
                                        <div class="form-group">
                                            <label for="fluency"
                                                   class="control-label"><?php echo $LanguageInstance->get( 'Fluency' ) ?>
                                                *</label>
                                            <input data-slider-id='ex1Slider' <?php echo ( ! $can_edit ) ? "data-slider-enabled='0'" : "" ?>
                                                   class="sliderTandem" name="fluency" id="fluency" type="text"
                                                   data-slider-min="0" data-slider-max="100" data-slider-step="1"
                                                   data-slider-value="<?php echo $feedback_form->fluency ?>"/>%
                                            <p class="help-block"><?php echo $LanguageInstance->get( 'Please move the slider to set a value' ) ?></p>
                                        </div>
                                        <div class="form-group">
                                            <label for="accuracy"
                                                   class="control-label"><?php echo $LanguageInstance->get( 'Accuracy' ) ?>
                                                *</label>
                                            <input data-slider-id='ex2Slider' <?php echo ( ! $can_edit ) ? "data-slider-enabled='0'" : "" ?>
                                                   class="sliderTandem" name="accuracy" id="accuracy" type="text"
                                                   data-slider-min="0" data-slider-max="100" data-slider-step="1"
                                                   data-slider-value="<?php echo $feedback_form->accuracy ?>"/>%
                                            <p class="help-block"><?php echo $LanguageInstance->get( 'Please move the slider to set a value' ) ?></p>
                                        </div>

                                        <div class="form-group">
                                            <label for="grade"
                                                   class="control-label"><?php echo $LanguageInstance->get( 'Overall Grade' ) . ':'; ?>
                                                *</label>
                                            <select id="grade" name="grade"
                                                    required <?php echo ( ! $can_edit ) ? "disabled" : "" ?>>
                                                <option value=""><?php echo $LanguageInstance->get( 'Select one' ) ?></option>
                                                <option value="A" <?php echo $feedback_form->grade === 'A' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Excellent' ) ?></option>
                                                <option value="B" <?php echo $feedback_form->grade === 'B' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Very Good' ) ?></option>
                                                <option value="C" <?php echo $feedback_form->grade === 'C' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Good' ) ?></option>
                                                <option value="D" <?php echo $feedback_form->grade === 'D' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Pass' ) ?></option>
                                                <option value="F" <?php echo $feedback_form->grade === 'F' ? 'selected' : '' ?>><?php echo $LanguageInstance->get( 'Fail' ) ?></option>
                                            </select>
                                        </div>

                                        <!-- @ybilbao 3iPunt TODO -->
										<?php
										if ( ! empty( $rubrics ) ) {
											foreach ( $rubrics as $rubric ) { ?>
                                                <div class="form-group">
                                                <label for="grade"
                                                       class="control-label"><?php echo $rubric['name']; ?></label>

                                                <!-- trigger modal -->
                                                <div class="info" data-toggle="modal"
                                                     data-target="#infoRubric<?php echo $rubric['id']; ?>"
                                                     style="display:initial; cursor:pointer"><img src="img/info.png"/>
                                                </div>
                                                <!-- Modal -->
                                                <div class="modal fade" id="infoRubric<?php echo $rubric['id']; ?>"
                                                     tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                        aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                                <h4 class="modal-title"
                                                                    id="myModalLabel"><?php echo $LanguageInstance->get( 'More information' ) ?></h4>
                                                            </div>
                                                            <div class="modal-body rubric-modal-body">
                                                                <div>
                                                                    <b><?php echo $rubric['name']; ?></b><br/>
																	<?php echo $rubric['description']; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-group">
                                                    <textarea rows="3" cols="200"
                                                              class="form-control" <?php echo ( ! $can_edit ) ? "disabled" : "" ?>
                                                              id="<?php echo $rubric['field_name']; ?>"
                                                              name='<?php echo $rubric['field_name']; ?>'
                                                              placeholder=""><?php echo $feedback_form && $feedback_form->{$rubric['field_name']} ? $feedback_form->{$rubric['field_name']} : ''; ?></textarea>
                                                </div>
                                                </div><?php
											}

										}
										?>


                                        <hr>


										<?php if ( $can_edit ) { ?>
                                            <div class="form-group">
                                                <small><?php echo $LanguageInstance->get( 'Required fields are noted with an asterisk (*)' ) ?></small>
                                            </div>
                                            <div class="form-group">
                                                <input type='hidden' name='save_feedback' value='1'>
                                                <button type="submit"
                                                        class="btn btn-success"><?php echo $LanguageInstance->get( 'Send' ) ?></button>
                                                <span class="small"><?php echo $LanguageInstance->get( 'cannot_be_modified' ) ?></span>
                                            </div>
											<?php //<input type="submit" name="id" value="<?php echo $id_feedback" /> ?>
										<?php } ?>
                                        <input type='hidden' name='id_feedback' value='<?php echo $id_feedback ?>'/>
                                    </form>
									<?php } ?>
                                    <!-- /content -->
                                </div>
                                <!-- /main -->
                            </div>
                        </div>
                    </div>
                    <!-- /main-container -->
                </div>
            </div>
		<?php } // End if feedback selfreflection else case. ?>

        <!-- Contact Modal -->
        <div class="modal fade" id="contact-modal" tabindex="-1" role="dialog" aria-hidden="true"
             style="display: none;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title"><?php echo $LanguageInstance->get( 'Contact' ) ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="subject"><?php echo $LanguageInstance->get( 'Subject' ) ?>:</label>
                            <input type="text" class="form-control" id="subject">
                        </div>
                        <div class="form-group">
                            <label for="comment"><?php echo $LanguageInstance->get( 'Comment' ) ?>:</label>
                            <textarea class="form-control" rows="5" id="comment"></textarea>
                        </div>
                        <div id="contact-modal-warning" class="alert alert-warning" style="display:none;">
							<?php echo $LanguageInstance->get( 'Missing parameters' ) ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="send-email-btn"
                                class="btn btn-primary"><?php echo $LanguageInstance->get( 'Send' ) ?></button>
                        <button type="button" class="btn btn-success"
                                data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Contact Modal -->

		<?php if ( $feedback_selfreflection_form ) { ?>
            <!-- More information modals -->
            <div class="modal fade" id="selfreflection-feedback-info-1" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title"
                                id="grammatical-resource-modal-label"><?php echo $LanguageInstance->get( 'One or more things I did well.' ) ?></h4>
                        </div>
                        <div class="modal-body">
							<?php echo '<div>' . $LanguageInstance->get( 'Write here one or more things that you think you did well while you were doing the speaking activity with your partner. For example,' ) . '</div><br>';
							echo '<li>' . $LanguageInstance->get( 'I didn’t hesitate much during the conversation. When I couldn’t remember a word, I explained what I wanted to say in a different way.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'I used some linking words that I don’t normally use like despite and although.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'I asked my partner lots of questions. Also, I was able to answer my partner’s questions and give my opinion.' ) . '</li></ul><br>';
							echo '<div><i>' . $LanguageInstance->get( 'You can watch the videos of you and your partner doing the Tandem activity by clicking on view video at any time while you are completing the Peer review form.' ) . '</i></div>'; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"
                                    data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="selfreflection-feedback-info-2" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title"
                                id="grammatical-resource-modal-label"><?php echo $LanguageInstance->get( 'Three language errors I made.' ) ?></h4>
                        </div>
                        <div class="modal-body">
							<?php echo '<div>' . $LanguageInstance->get( 'Write here three language errors you remember making while you were doing the speaking activity with your partner. For example,' ) . '</div><br>';
							echo '<li>' . $LanguageInstance->get( 'I didn\'t pronounce public correctly.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'I said my last travel but I should have said my last holiday.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'I said people is and I should have said people are.' ) . '</li></ul><br>';
							echo '<div><i>' . $LanguageInstance->get( 'You can watch the videos of you and your partner doing the Tandem activity by clicking on view video at any time while you are completing the Peer review form.' ) . '</i></div>'; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"
                                    data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="selfreflection-feedback-info-3" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title"
                                id="grammatical-resource-modal-label"><?php echo $LanguageInstance->get( 'Other comments' ) ?></h4>
                        </div>
                        <div class="modal-body">
							<?php echo '<div>' . $LanguageInstance->get( 'Write here any additional comments you would like to say about the Tandem activity you have just done. For example,' ) . '</div><br>';
							echo '<li>' . $LanguageInstance->get( 'The conversation was difficult at the beginning but then I realised I had lots of things to say about the topic. In the end I enjoyed it.' ) . '</li></ul><br>';
							echo '<div><i>' . $LanguageInstance->get( 'You can watch the videos of you and your partner doing the Tandem activity by clicking on view video at any time while you are completing the Peer review form.' ) . '</i></div>'; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"
                                    data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="partner-feedback-info-1" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title"
                                id="grammatical-resource-modal-label"><?php echo $LanguageInstance->get( 'One or more things your partner did well.' ) ?></h4>
                        </div>
                        <div class="modal-body">
							<?php echo '<div>' . $LanguageInstance->get( 'Write here one or more things that you think your partner did well while you were doing the speaking activity. For example,' ) . '</div><br>';
							echo '<li>' . $LanguageInstance->get( 'When you didn’t understand what I was saying, you asked me to explain what I meant.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'You always gave examples to justify your opinions.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'You used some synonyms like busy and crowded.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'Most of the time your pronunciation was easy to understand.' ) . '</li></ul><br>';
							echo '<div><i>' . $LanguageInstance->get( 'You can watch the videos of you and your partner doing the Tandem activity by clicking on view video at any time while you are completing the Peer review form.' ) . '</i></div>'; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"
                                    data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="partner-feedback-info-2" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title"
                                id="grammatical-resource-modal-label"><?php echo $LanguageInstance->get( 'Three language errors your partner made.' ) ?></h4>
                        </div>
                        <div class="modal-body">
							<?php echo '<div>' . $LanguageInstance->get( 'Write here three language errors that you noticed your partner make while you were doing the speaking activity. For example,' ) . '</div><br>';
							echo '<li>' . $LanguageInstance->get( 'Sometimes you used the present tense when you were talking about the past.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'You said depends of when you should have said depends on.' ) . '</li>';
							echo '<li>' . $LanguageInstance->get( 'You pronounced culture and disturb incorrectly.' ) . '</li></ul><br>';
							echo '<div><i>' . $LanguageInstance->get( 'You can watch the videos of you and your partner doing the Tandem activity by clicking on view video at any time while you are completing the Peer review form.' ) . '</i></div>'; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"
                                    data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="partner-feedback-info-3" tabindex="-1" role="dialog"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title"
                                id="grammatical-resource-modal-label"><?php echo $LanguageInstance->get( 'Other comments' ) ?></h4>
                        </div>
                        <div class="modal-body">
							<?php echo '<div>' . $LanguageInstance->get( 'Write here any additional comments you would like to tell your partner about the Tandem activity you have just done. For example,' ) . '</div><br>';
							echo '<li>' . $LanguageInstance->get( 'I thought we did part two very well and we had interesting conversation.' ) . '</li></ul><br>';
							echo '<div><i>' . $LanguageInstance->get( 'You can watch the videos of you and your partner doing the Tandem activity by clicking on view video at any time while you are completing the Peer review form.' ) . '</i></div>'; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"
                                    data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
		<?php } ?>
		<?php if ( $user_obj->instructor && $user_obj->instructor == 1 ) {

			$tandem               = $gestorBD->obteTandem( $feedbackDetails->id_tandem );
			$date_before          = $tandem['created'];
			$date_after           = empty( $tandem['finalized'] ) ? $tandem['created'] : $tandem['finalized'];
			$interval_to_subtract = 15;
			$interval_to_add      = empty( $tandem['finalized'] ) ? 30 : 5;
			?>
            <!-- Session tandem info -->
            <div class="modal fade" id="info-modal" tabindex="-1" role="dialog" aria-hidden="true"
                 style="display: none;">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h4 class="modal-title"><?php echo $LanguageInstance->get( 'Session Tandem Information' ) ?></h4>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <h5><?php echo $LanguageInstance->get( "Tandem info" ) . ': ' ?></h5>
                                <ul>
                                    <li><?php echo $LanguageInstance->get( 'Tandem id' ); ?>
                                        : <?php echo $feedbackDetails->id_tandem; ?></li>
                                    <li><?php echo $LanguageInstance->get( 'Exercise' ); ?>
                                        : <?php echo $extra_feedback_details['exercise']; ?></li>
                                </ul
                            </div>
                            <div class="form-group">
                                <h5><?php echo $LanguageInstance->get( "Name" ) . ': ' . $gestorBD->getUserRelatedTandem( $tandem['id_user_host'] ); ?></h5>
                                <ul>
                                    <li><?php echo $LanguageInstance->get( 'user_agent_host' ) ?>
                                        : <?php echo $tandem['user_agent_host'] ?></li>
                                </ul
                            </div>
                            <div class="form-group">
                                <h5><?php echo $LanguageInstance->get( "Name" ) . ': ' . $gestorBD->getUserRelatedTandem( $tandem['id_user_guest'] ); ?></h5>
                                <ul>
                                    <li><?php echo $LanguageInstance->get( 'user_agent_guest' ) ?>
                                        : <?php echo $tandem['user_agent_guest'] ?></li>
                                </ul>
                            </div>
                            <div class="form-group">
                                <h5><?php echo $LanguageInstance->get( "Tandem log information" ); ?></h5>
                                <table class="table">
                                    <tr>
                                        <th><?php echo $LanguageInstance->get( "Fullname" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Email" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Page" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Parameters" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Date" ); ?></th>
                                    </tr>
									<?php
									$wr_hist = $gestorBD->get_log_actions_by_tandem( $feedbackDetails->id_tandem );
									if ( $wr_hist && count( $wr_hist ) > 0 ) {
										foreach ( $wr_hist as $item ) {
											?>
                                            <tr>
                                                <td><?php echo $item['fullname']; ?></td>
                                                <td><?php echo $item['email']; ?></td>
                                                <td><?php echo $item['ts']; ?></td>
                                                <td><?php echo $item['page']; ?></td>
                                                <td><?php print_r( json_decode( $item['params'] ) ); ?></td>

                                            </tr>
										<?php }
									}
									?>
                                </table>
                            </div>
                            <div class="form-group">
                                <h5><?php echo $LanguageInstance->get( "Tandem Users information:" ) ?></h5>
                                <table class="table">
                                    <tr>
                                        <th><?php echo $LanguageInstance->get( "Fullname" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Email" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Page" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Parameters" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Date" ); ?></th>
                                    </tr>
									<?php
									$wr_hist = $gestorBD->get_log_actions_by_users( $tandem['id_user_host'],
										$tandem['id_user_guest'], $date_before, $date_after, $interval_to_subtract,
										$interval_to_add );
									if ( $wr_hist && count( $wr_hist ) > 0 ) {
										foreach ( $wr_hist as $item ) {
											?>
                                            <tr>
                                                <td><?php echo $item['fullname']; ?></td>
                                                <td><?php echo $item['email']; ?></td>
                                                <td><?php echo $item['ts']; ?></td>
                                                <td><?php echo $item['page']; ?></td>
                                                <td><?php print_r( json_decode( $item['params'] ) ); ?></td>

                                            </tr>
										<?php }
									}
									?>
                                </table>
                            </div>
                            <div class="form-group">
                                <h5><?php echo $LanguageInstance->get( "User information:" ) . " " . $gestorBD->getUserRelatedTandem( $tandem['id_user_host'] ) ?></h5>
                                <table class="table">
                                    <tr>
                                        <th><?php echo $LanguageInstance->get( "Page" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Parameters" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Date" ); ?></th>
                                    </tr>
									<?php
									$wr_hist = $gestorBD->get_log_actions_by_user( $tandem['id_user_host'],
										$date_before, $date_after, $interval_to_subtract, $interval_to_add );
									if ( $wr_hist && count( $wr_hist ) > 0 ) {
										foreach ( $wr_hist as $item ) {
											?>
                                            <tr>
                                                <td><?php echo $item['ts']; ?></td>
                                                <td><?php echo $item['page']; ?></td>
                                                <td><?php print_r( json_decode( $item['params'] ) ); ?></td>

                                            </tr>
										<?php }
									}
									?>
                                </table>
                            </div>
                            <div class="form-group">
                                <h5><?php echo $LanguageInstance->get( "User information:" ) . " " . $gestorBD->getUserRelatedTandem( $tandem['id_user_guest'] ) ?></h5>
                                <table class="table">
                                    <tr>
                                        <th><?php echo $LanguageInstance->get( "Page" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Parameters" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Date" ); ?></th>
                                    </tr>
									<?php
									$wr_hist = $gestorBD->get_log_actions_by_user( $tandem['id_user_guest'],
										$date_before, $date_after, $interval_to_subtract, $interval_to_add );
									if ( $wr_hist && count( $wr_hist ) > 0 ) {
										foreach ( $wr_hist as $item ) {
											?>
                                            <tr>
                                                <td><?php echo $item['ts']; ?></td>
                                                <td><?php echo $item['page']; ?></td>
                                                <td><?php print_r( json_decode( $item['params'] ) ); ?></td>

                                            </tr>
										<?php }
									}
									?>
                                </table>
                            </div>
                            <div class="form-group">
                                <h5><?php echo $LanguageInstance->get( "Waiting room information" ); ?></h5>
                                <table class="table">
                                    <tr>
                                        <th><?php echo $LanguageInstance->get( "Status" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Language" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Exercise Name" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Number User Waiting" ); ?></th>
                                        <th><?php echo $LanguageInstance->get( "Date" ); ?></th>
                                    </tr>
									<?php
									$wr_hist = $gestorBD->get_waiting_room_user_history_by_tandem( $feedbackDetails->id_tandem );
									if ( $wr_hist && count( $wr_hist ) > 0 ) {
										foreach ( $wr_hist as $item ) {
											$assigned = $item['status'] == 'assigned' && $item['id_exercise'] == $tandem['id_exercise']; ?>
                                            <tr>
                                                <td <?php echo $assigned ? 'style="font-weight:bold"' : '' ?>><?php echo $LanguageInstance->get( $item['status'] == 'assigned' && $item['id_exercise'] != $tandem['id_exercise'] ? 'offered' : $item['status'] ); ?></td>
                                                <td <?php echo $assigned ? 'style="font-weight:bold"' : '' ?>><?php echo $item['language']; ?></td>
                                                <td <?php echo $assigned ? 'style="font-weight:bold"' : '' ?>><?php echo $item['name']; ?></td>
                                                <td <?php echo $assigned ? 'style="font-weight:bold"' : '' ?>><?php echo $item['number_user_waiting']; ?></td>
                                                <td <?php echo $assigned ? 'style="font-weight:bold"' : '' ?>><?php echo $item['created_history']; ?></td>
                                            </tr>
										<?php }
									}
									?>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"
                                    data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Session tandem info -->
		<?php } ?>

		<?php include_once __DIR__ . '/js/google_analytics.php' ?>

        <!-- Placed at the end of the document so the pages load faster -->
        <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
        <script src="js/validator.min.js"></script>
        <script src="js/bootstrap-slider2.js"></script>
        <script src="js/star-rating.min.js"></script>
        <script src="js/bootstrap-tour.min.js"></script>
        <script src="js/bootstrap-tour-standalone.min.js"></script>
        <script src="js/jquery-areyousure/jquery.are-you-sure.js"></script>
        <script src="js/jquery-areyousure/ays-beforeunload-shim.js"></script>
        <script>
            $('.sliderTandem').slider({
                formatter: function (value) {
                    return 'Current value: ' + value;
                }
            });
            $(document).ready(function () {

                // On page close, if forms have not been sent, warn the user about possibly losing introduced data.
                $('.feedback-form').areYouSure();

                $('#send-email-btn').click(function () {
                    var msg = $('#comment').val();
                    var subject = $('#subject').val();
                    if ((msg !== "") && (subject !== "")) {
                        $('#contact-modal-warning').css('display', 'none');
                        $('#contact-modal').modal('toggle');
                        $.ajax({
                            type: 'POST',
                            url: "send-email.php",
                            data: {
                                msg: msg,
                                subject: subject,
                                partner_id: '<?php echo $feedbackDetails->id_partner ?>'
                            },
                            success: function (data) {

                                $('#comment').val("");
                            }
                        });
                    } else {
                        $('#contact-modal-warning').css('display', 'block');
                    }
                });

                var $buttonHelp = $('#button-help');
				<?php if (! $feedback_selfreflection_form) { ?>
                $buttonHelp.bind('click', function () {
                    // Instance the tour
                    var tour = new Tour({
                        name: 'tour',
                        storage: false,
                        steps: [
                            {
                                element: "#ex1Slider",
                                title: "Grammatical Resource",
                                content: "Review your partner grammatical resource"
                            },
                            {
                                element: "#ex2Slider",
                                title: "Lexical Resource",
                                content: "Review your partner lexical resource"
                            },
                            {
                                element: "#ex3Slider",
                                title: "Discourse Mangement",
                                content: "Review your partner discourse mangement"
                            },
                            {
                                element: "#ex4Slider",
                                title: "Pronunciation",
                                content: "Review your partner pronunciation"
                            },
                            {
                                element: "#ex5Slider",
                                title: "Interactive Communication",
                                content: "Review your partner interactive communication"
                            },
                            /*{
                              element: "#other_observations",
                              placement: 'top',
                              title: "Other Observations",
                              content: "Review your partner other observations"
                            },*/
                            {
                                element: "#submitBtn",
                                title: "Submit",
                                content: "Submit yout partner review"
                            }
                        ]
                    });
                    // Initialize the tour
                    tour.init();
                    // Start the tour
                    tour.start();
                });
				<?php } else { ?>
                $buttonHelp.bind('click', function () {
                    // Instance the tour
                    var peerReviewTour = new Tour({
                        name: 'Peer review tour',
                        storage: false,
                        backdrop: true,
                        steps: [
                            {
                                element: "#main-container_old",
                                title: "Feedback forms",
                                placement: "top",
                                content: "Add comments here about your own performance.",
                                prev: -1,
                                next: 1
                            },
                            {
                                element: "#selfreflection-feedback-button-1",
                                title: "Add items",
                                placement: "top",
                                content: "Click here to add another item.",
                                prev: 0,
                                next: 2
                            },
                            {
                                element: "#save-feedback-data",
                                title: "Save feedback",
                                placement: "left",
                                content: "Once you are satisfied with your feedback, click here to save and submit.",
                                prev: 1,
                                next: -1
                            }
                        ],
                        onEnd: function () {
                            peerReviewTour = undefined;
                        }
                    });
                    // Little hack, for some reasons buttons are not working properly
                    var $body = $('body');
                    $body.on('click', '.btn[data-role=prev]', function () {
                        if (typeof peerReviewTour !== 'undefined') {
                            if (!$(this).hasClass('disabled')) {
                                peerReviewTour.prev();
                            }
                        }
                    });
                    $body.on('click', '.btn[data-role=next]', function () {
                        if (typeof peerReviewTour !== 'undefined') {
                            if (!$(this).hasClass('disabled')) {
                                peerReviewTour.next();
                            }
                        }
                    });
                    $body.on('click', '.btn[data-role=end]', function () {
                        if (typeof peerReviewTour !== 'undefined') {
                            peerReviewTour.end();
                        }
                    });
                    // Initialize the tour
                    peerReviewTour.init();
                    // Start the tour
                    peerReviewTour.start(true);
                });
				<?php } ?>

                $(".sliderdisabled").slider("disable");
				<?php
				//We need to translate the star rating plugin.
				if($_SESSION['lang'] === "es_ES"){ ?>
                $("#partner_rate").rating('refresh', {
                    clearCaption: 'Sin valoración', starCaptions: {
                        1: '1 estrella', 2: '2 estrellas', 3: '3 estrellas', 4: '4 estrellas', 5: '5 estrellas',
                    }
                });
				<?php }
				if ( ! empty( $feedbackDetails->rating_partner_feedback_form->partner_rate ) ) {
					echo "$('#partner_rate').rating('update', " . $feedbackDetails->rating_partner_feedback_form->partner_rate . ");";
					echo "$('#partner_rate').rating('refresh', {disabled: true});";
				}
				?>

                //if there is an anchor , then lets activate that tab if it exists
				<?php if ( isset( $_REQUEST['tab'] ) ) {
				echo '$(".nav-tabs a[href=#' . $_REQUEST['tab'] . ']").tab("show");';
			}
				?>

                setJwPlayerVideoUrl = function (url) {
                    if (url) {
                        jwplayer("myElement").setup({
                            file: url,
                            //image: "http://example.com/uploads/myPoster.jpg",
                            width: 640,
                            height: 360,
                            type: "mp4"
                        });

                        $("#jwVideoModal").modal('show');
                    }
                };

                //close the jwvideo when the modal is closed
                $('#jwVideoModal').on('hidden.bs.modal', function () {
                    jwplayer().stop()
                });

                $('#grammatical-resource-rubric').on('change', function () {
                    if (this.value !== "") {
                        switch (this.value) {
                            case 'A':
                                $('#grammatical-resource-text-info-span').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#grammatical-resource-info-modal-label').html('Bla bla bla bla bla bla bla bla bla bla bla bla bla');
                                $('#grammatical-resource-info-modal-body').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                            case 'B':
                                $('#grammatical-resource-text-info-span').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#grammatical-resource-info-modal-label').html('Ble ble ble ble ble ble ble ble ble ble ble ble ble');
                                $('#grammatical-resource-info-modal-body').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                            case 'C':
                                $('#grammatical-resource-text-info-span').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#grammatical-resource-info-modal-label').html('Bli bli bli bli bli bli bli bli bli bli bli bli bli');
                                $('#grammatical-resource-info-modal-body').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                        }
                        $('#grammatical-resource-text-info').css('display', 'block');
                    } else {
                        $('#grammatical-resource-text-info').css('display', 'none');
                    }
                });
                $('#lexical-resource-rubric').on('change', function () {
                    if (this.value !== "") {
                        switch (this.value) {
                            case 'A':
                                $('#lexical-resource-text-info-span').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#lexical-resource-info-modal-label').html('Bla bla bla bla bla bla bla bla bla bla bla bla bla');
                                $('#lexical-resource-info-modal-body').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                            case 'B':
                                $('#lexical-resource-text-info-span').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#lexical-resource-info-modal-label').html('Ble ble ble ble ble ble ble ble ble ble ble ble ble');
                                $('#lexical-resource-info-modal-body').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                            case 'C':
                                $('#lexical-resource-text-info-span').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#lexical-resource-info-modal-label').html('Bli bli bli bli bli bli bli bli bli bli bli bli bli');
                                $('#lexical-resource-info-modal-body').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                        }
                        $('#lexical-resource-text-info').css('display', 'block');
                    } else {
                        $('#lexical-resource-text-info').css('display', 'none');
                    }
                });
                $('#pronunciation-resource-rubric').on('change', function () {
                    if (this.value !== "") {
                        switch (this.value) {
                            case 'A':
                                $('#pronunciation-resource-text-info-span').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#pronunciation-resource-info-modal-label').html('Bla bla bla bla bla bla bla bla bla bla bla bla bla');
                                $('#pronunciation-resource-info-modal-body').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                            case 'B':
                                $('#pronunciation-resource-text-info-span').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#pronunciation-resource-info-modal-label').html('Ble ble ble ble ble ble ble ble ble ble ble ble ble');
                                $('#pronunciation-resource-info-modal-body').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                            case 'C':
                                $('#pronunciation-resource-text-info-span').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                $('#pronunciation-resource-info-modal-label').html('Bli bli bli bli bli bli bli bli bli bli bli bli bli');
                                $('#pronunciation-resource-info-modal-body').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                break;
                        }
                        $('#pronunciation-resource-text-info').css('display', 'block');
                    } else {
                        $('#pronunciation-resource-text-info').css('display', 'none');
                    }
                });
            });
        </script>
		<?php if ( $feedback_selfreflection_form && $can_edit ) { ?>
            <script>
                var feedbackString = {
                    errorCommentStr: '<?php echo $LanguageInstance->get( 'errorCommentStr' ); ?>',
                    errorCharactersStr: '<?php echo $LanguageInstance->get( 'errorCharactersStr' ); ?>',
                    errorItemsStr: '<?php echo $LanguageInstance->get( 'errorItemsStr' ); ?>',
                    errorSavedStr: '<?php echo $LanguageInstance->get( 'Unable to save your feedback. Try again later.' ); ?>',
                    savedOkStr: '<?php echo $LanguageInstance->get( 'Your feedback was saved.' ); ?>'

                };
                var feedbackValidation = {
                    minWellItems: <?php echo defined( 'FEEDBACK_VALIDATION_MIN_WELL_ITEMS' ) ? FEEDBACK_VALIDATION_MIN_WELL_ITEMS : 1; ?>,
                    minErrorItems: <?php echo defined( 'FEEDBACK_VALIDATION_MIN_ERROR_ITEMS' ) ? FEEDBACK_VALIDATION_MIN_ERROR_ITEMS : 1; ?>,
                    itemsMinChars: <?php echo defined( 'FEEDBACK_VALIDATION_MIN_CHARS' ) ? FEEDBACK_VALIDATION_MIN_CHARS : 3; ?>,
                    commentsRequired: <?php echo ( defined( 'FEEDBACK_VALIDATION_COMMENTS_REQUIRED' ) && FEEDBACK_VALIDATION_COMMENTS_REQUIRED ) ? 'true' : 'false'; ?>
                };
            </script>
            <script src="js/feedback-selfreflection.js?version=4"></script>
		<?php } ?>
        <div class="modal fade" id="jwVideoModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                    class="sr-only"><?php echo $LanguageInstance->get( 'Close' ) ?></span></button>
                        <h4 class="modal-title"><?php echo $LanguageInstance->get( 'Video player' ) ?></h4>
                    </div>
                    <div class="modal-body">
                        <script src="https://jwpsrv.com/library/MjW8iGEHEeSfCBLddj37mA.js"></script>
                        <div id="myElement"><?php echo $LanguageInstance->get( 'Loading the player' ) ?>...</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal"><?php echo $LanguageInstance->get( 'Close' ) ?></button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
</body>
</html>