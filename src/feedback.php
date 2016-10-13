<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once dirname(__FILE__) . '/classes/utils.php';
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
$use_waiting_room = isset($_SESSION[USE_WAITING_ROOM]) ? $_SESSION[USE_WAITING_ROOM] : false;

require_once dirname(__FILE__) . '/classes/IntegrationTandemBLTI.php';
//si no existeix objecte usuari o no existeix curs redireccionem cap a l'index....preguntar Antoni cap a on redirigir...
if (!$user_obj || !$course_id) {
//Tornem a l'index
	header('Location: index.php');
	die();
} else {
		require_once(dirname(__FILE__) . '/classes/constants.php');
		$id_feedback = isset($_GET['id_feedback']) && $_GET['id_feedback']>0?$_GET['id_feedback']:(isset($_POST['id_feedback']) && $_POST['id_feedback']>0?$_POST['id_feedback']:$_SESSION[ID_FEEDBACK]);

		if (!$id_feedback ) {
			die($LanguageInstance->get('Missing feedback parameter'));
		}

		$gestorBD = new GestorBD();

		$feedbackDetails = $gestorBD->getFeedbackDetails($id_feedback);
		if (!$feedbackDetails) {
			die($LanguageInstance->get('Can not find feedback'));
		}
		$_SESSION[ID_FEEDBACK] = $id_feedback;

		//we need these extra info to show like the list of portfolio
		$extra_feedback_details = $gestorBD->getUserFeedback($id_feedback);

		if(!empty($_POST['rating_partner'])){
			$rating_partner_feedback_form = new stdClass();
			$rating_partner_feedback_form->partner_rate = isset($_POST['partner_rate'])?$_POST['partner_rate']:0;
			$rating_partner_feedback_form->partner_comment = isset($_POST['partner_comment'])?$_POST['partner_comment']:'';
			$gestorBD->updateRatingPartnerFeedbackTandemDetail($id_feedback, $rating_partner_feedback_form);

			//lets update this user ranking points
			$gestorBD->updateUserRankingPoints($user_obj->id,$course_id,$_SESSION['lang']);
			//now lets update the partner ranking points
			$gestorBD->updateUserRankingPoints($feedbackDetails->id_partner,$course_id,$feedbackDetails->partner_language);
		}


		if ($user_obj->id!=$feedbackDetails->id_user && $user_obj->id!=$feedbackDetails->id_partner &&
			!$user_obj->instructor && !$user_obj->admin) { //check if is the user of feedback if not can't not set feedback
			die($LanguageInstance->get('no estas autoritzat'));
		}

		$message= false;
		$can_edit = true;

                if ($_SESSION[USE_WAITING_ROOM_NO_TEAMS]){
                    $feedback_form_new = new stdClass();
                    $feedback_form_new->grammaticalresource = 0;
                    $feedback_form_new->lexicalresource = 0;
                    $feedback_form_new->discoursemangement = 0;
                    $feedback_form_new->pronunciation = 0;
                    $feedback_form_new->interactivecommunication = 0;
                    $feedback_form_new->other_observations = "";
                }else{
                    $feedback_form = new stdClass();
                    $feedback_form->fluency = 0;
                    $feedback_form->accuracy = 0;
                    $feedback_form->grade = "";
                    $feedback_form->pronunciation = "";
                    $feedback_form->vocabulary = "";
                    $feedback_form->grammar = "";
                    $feedback_form->other_observations = "";
                }
		if ($feedbackDetails->feedback_form) { //if it is false can edit
			$can_edit = false;
			$message = '<div class="alert alert-info" role="alert">'.$LanguageInstance->get('The information is stored you can only review it').'</div>';
			$feedback_form = $feedbackDetails->feedback_form;
		} else{
			if ($user_obj->id==$feedbackDetails->id_user) { //check if is the user of feedback if not can't not set feedback
                                if (!empty($_POST['save_feedback_new'])) {
                                    $feedback_form_new->grammaticalresource = $_POST['grammaticalresource'];
                                    $feedback_form_new->lexicalresource = $_POST['lexicalresource'];
                                    $feedback_form_new->discoursemangement = $_POST['discoursemangement'];
                                    $feedback_form_new->pronunciation = $_POST['pronunciation'];
                                    $feedback_form_new->interactivecommunication = $_POST['interactivecommunication'];
                                    $feedback_form_new->other_observations = $_POST['other_observations'];
                                    if ($gestorBD->createFeedbackTandemDetail($id_feedback, serialize($feedback_form_new))) {
                                            $message = '<div class="alert alert-success" role="alert">'.$LanguageInstance->get('Data saved successfully').'</div>';
                                            $can_edit = false;

                                            //lets update this user ranking points
                                            $gestorBD->updateUserRankingPoints($user_obj->id,$course_id,$_SESSION['lang']);
                                            //now lets update the partner ranking points
                                            $gestorBD->updateUserRankingPoints($feedbackDetails->id_partner,$course_id,$feedbackDetails->partner_language);
                                    }
                                }else if (!empty($_POST['save_feedback'])) {
					//try to save it!
					$feedback_form->fluency = isset($_POST['fluency'])?$_POST['fluency']:50;
					$feedback_form->accuracy = $_POST['accuracy'];
					$feedback_form->grade = $_POST['grade'];
					$feedback_form->pronunciation = $_POST['pronunciation'];
					$feedback_form->vocabulary = $_POST['vocabulary'];
					$feedback_form->grammar = $_POST['grammar'];
					$feedback_form->other_observations = $_POST['other_observations'];
					if (isset($_POST['fluency']) && strlen($_POST['fluency'])>0 &&
						isset($_POST['accuracy']) && strlen($_POST['accuracy'])>0 &&
						isset($_POST['grade']) && strlen($_POST['grade'])>0 /*&&
						isset($_POST['pronunciation']) && strlen($_POST['pronunciation'])>0 &&
						isset($_POST['vocabulary']) && strlen($_POST['vocabulary'])>0 &&
						isset($_POST['grammar']) && strlen($_POST['grammar'])>0*/){
						if ($gestorBD->createFeedbackTandemDetail($id_feedback, serialize($feedback_form))) {
							$message = '<div class="alert alert-success" role="alert">'.$LanguageInstance->get('Data saved successfully').'</div>';
							$can_edit = false;

							//lets update this user ranking points
							$gestorBD->updateUserRankingPoints($user_obj->id,$course_id,$_SESSION['lang']);
							//now lets update the partner ranking points
							$gestorBD->updateUserRankingPoints($feedbackDetails->id_partner,$course_id,$feedbackDetails->partner_language);
						}
					} else {
						$message = '<div class="alert alert-danger" role="alert">'.$LanguageInstance->get('fill_required_fields').'</div>';
					}
				}
			} else {
				$can_edit = false;
			}
		}
		$partnerFeedback = $gestorBD->checkPartnerFeedback($feedbackDetails->id_tandem,$id_feedback);

		$partnerName = $gestorBD->getPartnerName($id_feedback);

		//@ybilbao 3iPunt -> Get course rubricks
		$rubrics = $gestorBD->get_course_rubrics($course_id);
		//END
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>Tandem</title>
			<meta charset="UTF-8" />
			<link rel="stylesheet" type="text/css" media="all" href="css/autoAssignTandem.css" />
			<link rel="stylesheet" type="text/css" media="all" href="css/tandem-waiting-room.css" />
			<link rel="stylesheet" type="text/css" media="all" href="css/defaultInit.css" />
			<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
			<link rel="stylesheet" type="text/css" media="all" href="css/slider2.css" />
			<link rel="stylesheet" type="text/css" media="all" href="css/star-rating.min.css" />
                        <link rel="stylesheet" type="text/css" media="all" href="css/bootstrap-tour.min.css" />
                        <link rel="stylesheet" type="text/css" media="all" href="css/bootstrap-tour-standalone.min.css" />
			<style>
			#footer-container{margin-top:-222px;position:inherit;}
			#wrapper{padding:0px 58px}
			#jwVideoModal .modal-dialog
			{
				width: 680px; /* your width */
			}
			</style>
	</head>
	<body>
		<!-- Begin page content -->
		<div id="wrapper" class="container">
		  <div class="page-header">
		  <div class='row'>
			<div class='col-md-5'>
				<button class="btn btn-success" type='button' onclick="window.location ='portfolio.php';"><?php echo $LanguageInstance->get('Back to list') ?></button>
				<h1><?php echo $LanguageInstance->get('peer_review_form') ?></h1>
				<?php if ($user_obj->instructor == 1 ){ ?>
				<p><?php echo $LanguageInstance->get('Name') ?>: <?php echo $gestorBD->getUserName($feedbackDetails->id_user);?></p>
				<?php } ?>
				<p><?php echo $LanguageInstance->get('your_partners_name') ?>: <?php echo $partnerName;?>
                                <button type="button" id="button-contact" data-toggle="modal" data-target="#contact-modal" class="btn btn-info" title="<?php echo $LanguageInstance->get('Contact') ?>"><i class="glyphicon glyphicon-envelope"></i> <?php echo $LanguageInstance->get('Contact') ?></button>
                                </p>
			</div>
			<div class='col-md-6'>
			<p><br /><br />
			 <div><?php echo $LanguageInstance->get('Created'); ?>: <b><?php echo $extra_feedback_details['created']; ?></b></div>
			 <div><?php echo $LanguageInstance->get('Exercise'); ?>: <b><?php echo $extra_feedback_details['exercise']; ?></b></div>
			 <div><?php echo $LanguageInstance->get('Total Duration'); ?>: <b><?php echo $extra_feedback_details['total_time']; ?></b></div>
			 <div><?php echo $LanguageInstance->get('Duration per task'); ?>:
			 <span style='font-size:11px;font-weight:bold'><?php
			 $tt = array();
				foreach($extra_feedback_details['total_time_tasks'] as $key => $val){
					$tt[] = "T".++$key." = ".$val;
				}
			echo implode(", ",$tt);
			 ?></span>
			</div>
			</p>
			</div>
                        <div class='col-md-1'>
                            <button type="button" id="button-help" class="btn btn-info" title="<?php echo $LanguageInstance->get('Help') ?>"><i class="glyphicon glyphicon-question-sign"></i> <?php echo $LanguageInstance->get('Help') ?></button>
                        </div>
			</div>
		</div>
		  <?php if ($message){
			echo $message;
		  }

	 if($feedbackDetails->id_tandem == $feedbackDetails->id_external_tool ) {
			 if(!empty($feedbackDetails->external_video_url)){
				$url_video = $feedbackDetails->external_video_url;
				if (defined('AWS_URL')  && defined('AWS_S3_BUCKET') && defined('AWS_S3_FOLDER') && defined('AWS_S3_USER') && defined('AWS_S3_SECRET')) {
					$file_nameArray = explode('/', $url_video);
					$file_name = $file_nameArray[count($file_nameArray)-1];
					$file_name = str_replace('/','_',$file_name);
					$file_name = str_replace(':','_',$file_name);
					$file_name = str_replace('=','_',$file_name);
					$awsurl = AWS_URL.AWS_S3_BUCKET.'/'.AWS_S3_FOLDER.'/'.$file_name;
					if (is_url_exist($awsurl)) {
						$url_video = $awsurl;
					}
				}
		  ?>
			 <p>
				<button id="viewVideo" onclick="setJwPlayerVideoUrl('<?php echo $url_video;?>')" type="button" class="btn btn-success"><?php echo $LanguageInstance->get('View video session') ?></button>
	<button onclick="window.open('<?php echo $url_video;?>')"  class="btn btn-success"><?php echo $LanguageInstance->get('Download') ?></button>
			 </p>
			<?php }else{ ?>
			<p>
				<button class="btn btn-warning" disabled="disabled"><?php echo $LanguageInstance->get('Video is processing') ?></button>
			</p>
			<?php } ?>

		 <?php }else{
			 if($feedbackDetails->id_external_tool > 0){
		 ?>
			 <p>
				<button id="viewVideo" onclick="window.open('ltiConsumer.php?id=100&<?php echo $feedbackDetails->id_external_tool>0? (ID_EXTERNAL.'='. $feedbackDetails->id_external_tool):''?>&<?php echo $feedbackDetails->id_tandem>0? (CURRENT_TANDEM.'='. $feedbackDetails->id_tandem):''?>')" type="button" class="btn btn-success"><?php echo $LanguageInstance->get('View video session') ?></button>
			 </p>
		 <?php
			}else
			echo '<p ><button class="btn btn-warning" disabled="disabled">'.$LanguageInstance->get("The video could not be recorded").'</button></p>';
			}

		  ?>


		  <!-- Nav tabs -->
		 <div class='row'>
			 <div class='col-md-12'>
					<ul class="nav nav-tabs" role="tablist">
					  <li class="active"><a href="#main-container_old" role="tab" data-toggle="tab"><?php echo $LanguageInstance->get('Review your partner\'s contribution') ?></a></li>
					  <li><a href="#other" role="tab" data-toggle="tab"><?php echo $LanguageInstance->get('View received Feedback') ?></a></li>
					</ul>
			</div>
		</div>
		<div class="tab-content">
			<br />
			<div id='other' class="tab-pane">
				<?php
					if(!empty($partnerFeedback)){
						$feedBackFormPartner = unserialize($partnerFeedback);
                                                if ($_SESSION[USE_WAITING_ROOM_NO_TEAMS]){ ?>
                                                    <div class="form-group">
                                                        <label for="grammaticalresource" class="control-label"><?php echo $LanguageInstance->get('Grammatical Resource') ?></label>
                                                        <?php echo $feedBackFormPartner->grammaticalresource?> %
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="lexicalresource" class="control-label"><?php echo $LanguageInstance->get('Lexical Resource') ?></label>
                                                        <?php echo $feedBackFormPartner->lexicalresource?> %
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="discoursemangement" class="control-label"><?php echo $LanguageInstance->get('Discourse Mangement') ?></label>
                                                        <?php echo $feedBackFormPartner->discoursemangement?> %
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="interactivecommunication" class="control-label"><?php echo $LanguageInstance->get('Interactive Communication') ?></label>
                                                        <?php echo $feedBackFormPartner->interactivecommunication?> %
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="pronunciation" class="control-label"><?php echo $LanguageInstance->get('Pronunciation') ?></label>
                                                        <?php echo $feedBackFormPartner->pronunciation?> %
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="other_observations" class="control-label"><?php echo $LanguageInstance->get('Other Observations')?></label>
                                                        <div class="input-group">
                                                          <textarea  readonly rows="3" cols="200" class="form-control" id="other_observations" name="other_observations" placeholder="<?php echo $LanguageInstance->get('Indicate other observations')?>"><?php echo $feedBackFormPartner->other_observations?></textarea>
                                                        </div>
                                                    </div>
                                                <?php     
                                                }else{ 
				?>
							  <div class="form-group">
								<label for="fluency" class="control-label"><?php echo $LanguageInstance->get('Fluency') ?></label>
								<?php echo $feedBackFormPartner->fluency?> %
							  </div>
							  <div class="form-group">
								<label for="accuracy" class="control-label"><?php echo $LanguageInstance->get('Accuracy') ?></label>
								<?php echo $feedBackFormPartner->accuracy?> %
							  </div>
							  <div class="form-group">
								<label for="grade" class="control-label"><?php echo $LanguageInstance->get('Overall Grade') ?></label>
								<select disabled id="grade" name="grade" required>
									<option><?php echo $LanguageInstance->get('Select one')?></option>
									<option value="A" <?php echo $feedBackFormPartner->grade=='A'?'selected':''?>><?php echo $LanguageInstance->get('Excellent')?></option>
									<option value="B" <?php echo $feedBackFormPartner->grade=='B'?'selected':''?>><?php echo $LanguageInstance->get('Very Good')?></option>
									<option value="C" <?php echo $feedBackFormPartner->grade=='C'?'selected':''?>><?php echo $LanguageInstance->get('Good')?></option>
									<option value="D" <?php echo $feedBackFormPartner->grade=='D'?'selected':''?>><?php echo $LanguageInstance->get('Pass')?></option>
									<option value="F" <?php echo $feedBackFormPartner->grade=='F'?'selected':''?>><?php echo $LanguageInstance->get('Fail')?></option>
								</select>
							  </div>
							  <!--div class="row"><h3><?php //echo $LanguageInstance->get('Room for improvement')?></h3></div-->

							  <div class="form-group">
								<label for="pronunciation" class="control-label"><?php echo $LanguageInstance->get('Pronunciation')?></label>
								<div class="input-group">
								  <textarea readonly rows="3" cols="200" class="form-control" id="pronunciation" name='pronunciation' placeholder="<?php echo $LanguageInstance->get('Indicate the level of pronunciation')?>" required><?php echo $feedBackFormPartner->pronunciation?></textarea>
								</div>
							  </div>
							  <div class="form-group">
								<label for="vocabulary" class="control-label"><?php echo $LanguageInstance->get('Vocabulary')?></label>
								<div class="input-group">
								  <textarea readonly rows="3" cols="200" class="form-control" id="vocabulary"  name='vocabulary' placeholder="<?php echo $LanguageInstance->get('Indicate the level of vocabulary')?>" required><?php echo $feedBackFormPartner->vocabulary?></textarea>
								</div>
							  </div>
							  <div class="form-group">
								<label for="grammar" class="control-label"><?php echo $LanguageInstance->get('Grammar')?></label>
								<div class="input-group">
								  <textarea readonly rows="3" cols="200" class="form-control" id="grammar"  name="grammar" placeholder="<?php echo $LanguageInstance->get('Indicate the level of grammar')?>" required><?php echo $feedBackFormPartner->grammar?></textarea>
								</div>
							  </div>
							  <div class="form-group">
								<label for="other_observations" class="control-label"><?php echo $LanguageInstance->get('Other Observations')?></label>
								<div class="input-group">
								  <textarea  readonly rows="3" cols="200" class="form-control" id="other_observations" name="other_observations" placeholder="<?php echo $LanguageInstance->get('Indicate other observations')?>"><?php echo $feedBackFormPartner->other_observations?></textarea>
								</div>
							  </div>
                                                <?php } ?>
							  <!-- Rate your partner form -->
							<div class='row well'>
								<h3><?php echo $LanguageInstance->get('Rating Partner’s Feedback Form') ?></h3>
							 <form action='' method='POST'>
								<div class="form-group">
								 <label for="partner_rate" class="control-label"><?php echo $LanguageInstance->get('Rate your partners feedback') ?></label>
								<input name="partner_rate" id="partner_rate" type="text" class="rating" data-min="0" data-max="5" data-step="1" data-size="sm" />
							  </div>
							  <div class="form-group">
								<label for="partner_comment" class="control-label"><?php echo $LanguageInstance->get('Comments')?>:</label>
								<div class="input-group">
								  <textarea rows="3" cols="200" class="form-control" id="partner_comment"  name="partner_comment" placeholder="<?php echo $LanguageInstance->get('Indicate comments')?>"  <?php if(!empty($feedbackDetails->rating_partner_feedback_form->partner_comment)){ echo "readonly";} ?> ><?php if(!empty($feedbackDetails->rating_partner_feedback_form->partner_comment)){ echo $feedbackDetails->rating_partner_feedback_form->partner_comment;}?></textarea>
								</div>
							  </div>
							   <input type='hidden' name='rating_partner' value='1' />
							   <input type='hidden' name='id_feedback' value='<?php echo $id_feedback?>' />
							   <?php if(empty($feedbackDetails->rating_partner_feedback_form)){ ?>
								<button type="submit" class="btn btn-success"><?php echo $LanguageInstance->get('Send')?></button>
							   <?php } ?>
							   <span class="small"><?php echo $LanguageInstance->get('cannot_be_modified')?></span>
							 </form>
							</div>
					 <?php
					}else {
					echo "<p>". $LanguageInstance->get('partner_feedback_not_available')."</p>";
					if(isset($feedbackDetails->id))
						$feedBackIdTab = "?id_feedback=".$feedbackDetails->id."&tab=other";
					else
						$feedBackIdTab = "";
					?>
					<button id="checkFeedbacks" type="button" onclick="window.location = 'feedback.php<?php echo $feedBackIdTab;?>'" class="btn btn-success"><?php echo $LanguageInstance->get('Check if feedback are submitted') ?></button>
					<?php } ?>
			</div>
			<div id="main-container_old" class='tab-pane active'>
			<div class='row'>
			<div class='col-md-12'>
						<!-- main -->
						<div id="main_old">
                                                    
                                                    <?php if ($_SESSION[USE_WAITING_ROOM_NO_TEAMS]) { ?>
                                                        <!-- content -->
							<div id="content_old">
							<form data-toggle="validator" role="form" method="POST">
                                                        <div class="form-group">
								<label for="grammatical-resource-rubric" class="control-label">Descriptor de Rúbrica <?php echo $LanguageInstance->get('Grammatical Resource') ?>:</label>
								<select id="grammatical-resource-rubric" name="grammatical-resource-rubric">
									<option value="">Select one</option>
									<option value="A">Bla bla bla bla bla bla bla bla bla bla bla bla bla </option>
									<option value="B">Ble ble ble ble ble ble ble ble ble ble ble ble ble </option>
									<option value="C">Bli bli bli bli bli bli bli bli bli bli bli bli bli </option>
								</select>
								<!-- trigger modal -->
								<div class="info" data-toggle="modal" data-target="#grammatical-resource-infoModal" style="display:initial; cursor:pointer"><img src="images/info.png"></div>
								<!-- Modal -->
								<div class="modal fade" id="grammatical-resource-infoModal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
								  <div class="modal-dialog" role="document">
									<div class="modal-content">
									  <div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										  <span aria-hidden="true">×</span>
										</button>
										<h4 class="modal-title" id="grammatical-resource-modal-label">More information</h4>
									  </div>
									  <div class="modal-body" id="grammatical-resource-modal-body">
										<div><ul>
                                                                                        <li><b>Bla bla bla bla bla bla bla bla bla bla bla bla bla</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
											<li><b>Ble ble ble ble ble ble ble ble ble ble ble ble ble</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
											<li><b>Bli bli bli bli bli bli bli bli bli bli bli bli bli</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
										</ul></div>
									  </div>
									  <div class="modal-footer">
										<button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
									  </div>
									</div>
								  </div>
								</div>
                                                                <div id="grammatical-resource-text-info" style="display: none;">
                                                                  <span id="grammatical-resource-text-info-span"></span>
								  <div class="info" data-toggle="modal" data-target="#grammatical-resource-info-modal" style="display:initial; cursor:pointer"><img src="images/info.png"></div>
								  <!-- Modal -->
								  <div class="modal fade" id="grammatical-resource-info-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
									  <div class="modal-dialog" role="document">
										  <div class="modal-content">
											  <div class="modal-header">
												  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
													  <span aria-hidden="true">×</span>
												  </button>
												  <h4 class="modal-title" id="grammatical-resource-info-modal-label">Bla bla bla bla bla bla bla bla bla bla bla bla bla</h4>
											  </div>
											  <div class="modal-body" id="grammatical-resource-info-modal-body">
												  <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.
												  </div>
											  </div>
											  <div class="modal-footer">
												  <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
											  </div>
										  </div>
									  </div>
									  </div>
								  </div>
							  </div>
							  <div class="form-group">
								<label for="grammaticalresource" class="control-label"><?php echo $LanguageInstance->get('Grammatical Resource') ?> *</label>
								<input data-slider-id='ex1Slider' <?php echo (!$can_edit) ? "data-slider-enabled='0'" : "" ?> class="sliderTandem" name="grammaticalresource" id="grammaticalresource" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo $feedback_form_new->grammaticalresource?>"/>%
								<p class="help-block"><?php echo $LanguageInstance->get('Please move the slider to set a value') ?></p>
							  </div>
                                                          <div class="form-group">
								<label for="lexical-resource-rubric" class="control-label">Descriptor de Rúbrica <?php echo $LanguageInstance->get('Lexical Resource') ?>:</label>
								<select id="lexical-resource-rubric" name="lexical-resource-rubric">
									<option value="">Select one</option>
									<option value="A">Bla bla bla bla bla bla bla bla bla bla bla bla bla </option>
									<option value="B">Ble ble ble ble ble ble ble ble ble ble ble ble ble </option>
									<option value="C">Bli bli bli bli bli bli bli bli bli bli bli bli bli </option>
								</select>
								<!-- trigger modal -->
								<div class="info" data-toggle="modal" data-target="#lexical-resource-infoModal" style="display:initial; cursor:pointer"><img src="images/info.png"></div>
								<!-- Modal -->
								<div class="modal fade" id="lexical-resource-infoModal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
								  <div class="modal-dialog" role="document">
									<div class="modal-content">
									  <div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										  <span aria-hidden="true">×</span>
										</button>
										<h4 class="modal-title" id="lexical-resource-modal-label">More information</h4>
									  </div>
									  <div class="modal-body" id="lexical-resource-modal-body">
										<div><ul>
                                                                                        <li><b>Bla bla bla bla bla bla bla bla bla bla bla bla bla</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
											<li><b>Ble ble ble ble ble ble ble ble ble ble ble ble ble</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
											<li><b>Bli bli bli bli bli bli bli bli bli bli bli bli bli</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
										</ul></div>
									  </div>
									  <div class="modal-footer">
										<button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
									  </div>
									</div>
								  </div>
								</div>
                                                                <div id="lexical-resource-text-info" style="display: none;">
                                                                  <span id="lexical-resource-text-info-span"></span>
								  <div class="info" data-toggle="modal" data-target="#lexical-resource-info-modal" style="display:initial; cursor:pointer"><img src="images/info.png"></div>
								  <!-- Modal -->
								  <div class="modal fade" id="lexical-resource-info-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
									  <div class="modal-dialog" role="document">
										  <div class="modal-content">
											  <div class="modal-header">
												  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
													  <span aria-hidden="true">×</span>
												  </button>
												  <h4 class="modal-title" id="lexical-resource-info-modal-label">Bla bla bla bla bla bla bla bla bla bla bla bla bla</h4>
											  </div>
											  <div class="modal-body" id="lexical-resource-info-modal-body">
												  <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.
												  </div>
											  </div>
											  <div class="modal-footer">
												  <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
											  </div>
										  </div>
									  </div>
									  </div>
								  </div>
							  </div>
							  <div class="form-group">
								<label for="lexicalresource" class="control-label"><?php echo $LanguageInstance->get('Lexical Resource') ?> *</label>
								<input data-slider-id='ex2Slider' <?php echo (!$can_edit) ? "data-slider-enabled='0'" : "" ?> class="sliderTandem" name="lexicalresource" id="lexicalresource" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo $feedback_form_new->lexicalresource?>"/>%
								<p class="help-block"><?php echo $LanguageInstance->get('Please move the slider to set a value') ?></p>
							  </div> 
							  <div class="form-group">
								<label for="discoursemangement" class="control-label"><?php echo $LanguageInstance->get('Discourse Mangement') ?> *</label>
								<input data-slider-id='ex3Slider' <?php echo (!$can_edit) ? "data-slider-enabled='0'" : "" ?> class="sliderTandem" name="discoursemangement" id="discoursemangement" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo $feedback_form_new->discoursemangement?>"/>%
								<p class="help-block"><?php echo $LanguageInstance->get('Please move the slider to set a value') ?></p>
							  </div> 
                                                          <div class="form-group">
								<label for="pronunciation-resource-rubric" class="control-label">Descriptor de Rúbrica <?php echo $LanguageInstance->get('Pronunciation') ?>:</label>
								<select id="pronunciation-resource-rubric" name="pronunciation-resource-rubric">
									<option value="">Select one</option>
									<option value="A">Bla bla bla bla bla bla bla bla bla bla bla bla bla </option>
									<option value="B">Ble ble ble ble ble ble ble ble ble ble ble ble ble </option>
									<option value="C">Bli bli bli bli bli bli bli bli bli bli bli bli bli </option>
								</select>
								<!-- trigger modal -->
								<div class="info" data-toggle="modal" data-target="#pronunciation-resource-infoModal" style="display:initial; cursor:pointer"><img src="images/info.png"></div>
								<!-- Modal -->
								<div class="modal fade" id="pronunciation-resource-infoModal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
								  <div class="modal-dialog" role="document">
									<div class="modal-content">
									  <div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										  <span aria-hidden="true">×</span>
										</button>
										<h4 class="modal-title" id="pronunciation-resource-modal-label">More information</h4>
									  </div>
									  <div class="modal-body" id="pronunciation-resource-modal-body">
										<div><ul>
                                                                                        <li><b>Bla bla bla bla bla bla bla bla bla bla bla bla bla</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
											<li><b>Ble ble ble ble ble ble ble ble ble ble ble ble ble</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
											<li><b>Bli bli bli bli bli bli bli bli bli bli bli bli bli</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.</li>
										</ul></div>
									  </div>
									  <div class="modal-footer">
										<button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
									  </div>
									</div>
								  </div>
								</div>
                                                                <div id="pronunciation-resource-text-info" style="display: none;">
                                                                  <span id="pronunciation-resource-text-info-span"></span>
								  <div class="info" data-toggle="modal" data-target="#pronunciation-resource-info-modal" style="display:initial; cursor:pointer"><img src="images/info.png"></div>
								  <!-- Modal -->
								  <div class="modal fade" id="pronunciation-resource-info-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
									  <div class="modal-dialog" role="document">
										  <div class="modal-content">
											  <div class="modal-header">
												  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
													  <span aria-hidden="true">×</span>
												  </button>
												  <h4 class="modal-title" id="pronunciation-resource-info-modal-label">Bla bla bla bla bla bla bla bla bla bla bla bla bla</h4>
											  </div>
											  <div class="modal-body" id="pronunciation-resource-info-modal-body">
												  <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.
												  </div>
											  </div>
											  <div class="modal-footer">
												  <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
											  </div>
										  </div>
									  </div>
									  </div>
								  </div>
							  </div>
							  <div class="form-group">
								<label for="pronunciation" class="control-label"><?php echo $LanguageInstance->get('Pronunciation') ?> *</label>
								<input data-slider-id='ex4Slider' <?php echo (!$can_edit) ? "data-slider-enabled='0'" : "" ?> class="sliderTandem" name="pronunciation" id="pronunciation" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo $feedback_form_new->pronunciation?>"/>%
								<p class="help-block"><?php echo $LanguageInstance->get('Please move the slider to set a value') ?></p>
							  </div> 
                                                          <div class="form-group">
								<label for="interactivecommunication" class="control-label"><?php echo $LanguageInstance->get('Interactive Communication') ?> *</label>
								<input data-slider-id='ex5Slider' <?php echo (!$can_edit) ? "data-slider-enabled='0'" : "" ?> class="sliderTandem" name="interactivecommunication" id="interactivecommunication" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo $feedback_form_new->interactivecommunication?>"/>%
								<p class="help-block"><?php echo $LanguageInstance->get('Please move the slider to set a value') ?></p>
							  </div> 
                                                          <div class="form-group">
								<label for="other_observations" class="control-label"><?php echo $LanguageInstance->get('Other Observations')?></label>
								<div class="input-group">
								  <textarea rows="3" cols="200" class="form-control" id="other_observations" name="other_observations" placeholder="<?php echo $LanguageInstance->get('Indicate other observations')?>"><?php echo $feedback_form_new->other_observations?></textarea>
								</div>
							  </div>
							  <?php if ($can_edit) {?>
							  <div class="form-group">
								<small><?php echo $LanguageInstance->get('Required fields are noted with an asterisk (*)')?></small>
							  </div>
							  <div class="form-group">
							  <input type='hidden' name='save_feedback_new' value='1' >
								<button id="submitBtn" type="submit" class="btn btn-success"><?php echo $LanguageInstance->get('Send')?></button>
								 <span class="small"><?php echo $LanguageInstance->get('cannot_be_modified')?></span>
							  </div>
							  <?php } ?>
							  <input type='hidden' name='id_feedback' value='<?php echo $id_feedback?>' />
							</form>
                                                    <?php }else{ ?>
							<!-- content -->
							<div id="content_old">
							<form data-toggle="validator" role="form" method="POST">
							  <div class="form-group">
								<label for="fluency" class="control-label"><?php echo $LanguageInstance->get('Fluency') ?> *</label>
								<input data-slider-id='ex1Slider' <?php echo (!$can_edit) ? "data-slider-enabled='0'" : "" ?> class="sliderTandem" name="fluency" id="fluency" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo $feedback_form->fluency?>"/>%
								<p class="help-block"><?php echo $LanguageInstance->get('Please move the slider to set a value') ?></p>
							  </div>
							  <div class="form-group">
								<label for="accuracy" class="control-label"><?php echo $LanguageInstance->get('Accuracy') ?> *</label>
								<input data-slider-id='ex2Slider' <?php echo (!$can_edit) ? "data-slider-enabled='0'" : "" ?> class="sliderTandem" name="accuracy" id="accuracy" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="<?php echo $feedback_form->accuracy?>"/>%
								<p class="help-block"><?php echo $LanguageInstance->get('Please move the slider to set a value') ?></p>
							  </div>

							  <div class="form-group">
								<label for="grade" class="control-label"><?php echo $LanguageInstance->get('Overall Grade:') ?> *</label>
								<select id="grade" name="grade" required <?php echo (!$can_edit) ? "disabled" : "" ?>>
									<option value=""><?php echo $LanguageInstance->get('Select one')?></option>
									<option value="A" <?php echo $feedback_form->grade=='A'?'selected':''?>><?php echo $LanguageInstance->get('Excellent')?></option>
									<option value="B" <?php echo $feedback_form->grade=='B'?'selected':''?>><?php echo $LanguageInstance->get('Very Good')?></option>
									<option value="C" <?php echo $feedback_form->grade=='C'?'selected':''?>><?php echo $LanguageInstance->get('Good')?></option>
									<option value="D" <?php echo $feedback_form->grade=='D'?'selected':''?>><?php echo $LanguageInstance->get('Pass')?></option>
									<option value="F" <?php echo $feedback_form->grade=='F'?'selected':''?>><?php echo $LanguageInstance->get('Fail')?></option>
								</select>
							  </div>

							  	<!-- @ybilbao 3iPunt TODO -->
							  	<?php
									if(!empty($rubrics)){
										foreach ($rubrics as $rubric) {?>
											<div class="form-group">
												<label for="grade" class="control-label"><?php echo $rubric['name'];?></label>
												
												<!-- trigger modal -->
												<div class="info" data-toggle="modal" data-target="#infoRubric<?php echo $rubric['id'];?>" style="display:initial; cursor:pointer"><img src="img/info.png"/></div>
												<!-- Modal -->
												<div class="modal fade" id="infoRubric<?php echo $rubric['id'];?>" tabindex="-1" role="dialog" aria-hidden="true">
													<div class="modal-dialog" role="document">
														<div class="modal-content">
															<div class="modal-header">
																<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																	<span aria-hidden="true">&times;</span>
																</button>
																<h4 class="modal-title" id="myModalLabel">More information</h4>
															</div>
															<div class="modal-body rubric-modal-body" >
																<div>
																	<b><?php echo $rubric['name'];?></b><br />
																	<?php echo $rubric['description'];?>
																</div>
															</div>
												  		</div>
												  	</div>
												</div>
												<div class="input-group">
											 		<textarea rows="3" cols="200" class="form-control" id="vocabulary"  name='<?php echo $rubric['name'];?>' placeholder="" ></textarea>
												</div>
											</div><?php
										}

									}
								?>							


								<hr>


							  
							  <?php if ($can_edit) {?>
							  <div class="form-group">
								<small><?php echo $LanguageInstance->get('Required fields are noted with an asterisk (*)')?></small>
							  </div>
							  <div class="form-group">
							  <input type='hidden' name='save_feedback' value='1' >
								<button type="submit" class="btn btn-success"><?php echo $LanguageInstance->get('Send')?></button>
								 <span class="small"><?php echo $LanguageInstance->get('cannot_be_modified')?></span>
							  </div>
							  <?php //<input type="submit" name="id" value="<?php echo $id_feedback" /> ?>
							  <?php } ?>
							  <input type='hidden' name='id_feedback' value='<?php echo $id_feedback?>' />
							</form>
                                                    <?php } ?>
					<!-- /content -->
				</div>
				<!-- /main -->
			</div>
			</div>
			</div>
			<!-- /main-container -->
                        <!-- Contact Modal -->
                        <div class="modal fade" id="contact-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
                            <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                          </button>
                                          <h4 class="modal-title"><?php echo $LanguageInstance->get('Contact') ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="subject"><?php echo $LanguageInstance->get('Subject') ?>:</label>
                                            <input type="text" class="form-control" id="subject">
                                        </div>
                                        <div class="form-group">
                                            <label for="comment"><?php echo $LanguageInstance->get('Comment') ?>:</label>
                                            <textarea class="form-control" rows="5" id="comment"></textarea>
                                        </div>
                                        <div id="contact-modal-warning" class="alert alert-warning" style="display:none;">
                                            <?php echo $LanguageInstance->get('Missing parameters') ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                            <button type="button" id="send-email-btn" class="btn btn-primary"><?php echo $LanguageInstance->get('Send') ?></button>
                                            <button type="button" class="btn btn-success" data-dismiss="modal"><?php echo $LanguageInstance->get('Close') ?></button>
                                    </div>
                                  </div>
                            </div>
                        </div>
                        <!-- /Contact Modal -->
		</div>
		</div>

		<?php include_once dirname(__FILE__) . '/js/google_analytics.php' ?>

	<!-- Placed at the end of the document so the pages load faster -->
	<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<script src="js/validator.min.js"></script>
	<script src="js/bootstrap-slider2.js"></script>
	<script src="js/star-rating.min.js"></script>
        <script src="js/bootstrap-tour.min.js"></script>
        <script src="js/bootstrap-tour-standalone.min.js"></script>
	<script>
	$('.sliderTandem').slider({
		formatter: function(value) {
			return 'Current value: ' + value;
		}
	});
	$(document).ready(function(){
            
                $('#send-email-btn').click(function(){
                    var msg = $('#comment').val();
                    var subject = $('#subject').val();
                    if ((msg !== "")&&(subject !== "")){
                        $('#contact-modal-warning').css('display', 'none');
                        $('#contact-modal').modal('toggle');
                        $.ajax({
                                type: 'POST',
                                url: "send-email.php",
                                data : {
                                    msg : msg,
                                    subject: subject,
                                    partner_id: '<?php echo $feedbackDetails->id_partner ?>'
                                },
                                success: function(data){	        			
                                    $('#comment').val("");
                                }
                        });
                    }else{
                        $('#contact-modal-warning').css('display', 'block');
                    }
                });
                
                $('#button-help').click(function(){
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
                            {
                              element: "#other_observations",
                              placement: 'top',
                              title: "Other Observations",
                              content: "Review your partner other observations"
                            },
                            {
                              element: "#submitBtn",
                              title: "Submit",
                              content: "Submit yout partner review"
                            }
                      ]});
                    // Initialize the tour
                    tour.init();
                    // Start the tour
                    tour.start();
                });

		$(".sliderdisabled").slider("disable");
		<?php
		//We need to translate the star rating plugin.
		if($_SESSION['lang'] == "es_ES"){ ?>
		$("#partner_rate").rating('refresh', {clearCaption : 'Sin valoración', starCaptions : {
									1: '1 estrella',2: '2 estrellas',3: '3 estrellas',4: '4 estrellas',5: '5 estrellas',
								   }});
		<?php }
		   if(!empty($feedbackDetails->rating_partner_feedback_form->partner_rate)){
				echo "$('#partner_rate').rating('update', ".$feedbackDetails->rating_partner_feedback_form->partner_rate.");";
				echo "$('#partner_rate').rating('refresh', {disabled: true});";
			}
		 ?>

		 //if there is an anchor , then lets activate that tab if it exists
		 <?php if(isset($_REQUEST['tab'])) {
				echo '$(".nav-tabs a[href=#'.$_REQUEST['tab'].']").tab("show");';
			   }
		 ?>

		 setJwPlayerVideoUrl = function(url){
			if(url){
				jwplayer("myElement").setup({
					file: url,
					//image: "http://example.com/uploads/myPoster.jpg",
					width: 640,
					height: 360,
					type: "mp4",
				});

				$("#jwVideoModal").modal('show');
			}
		 }

		 //close the jwvideo when the modal is closed
		$('#jwVideoModal').on('hidden.bs.modal', function (){
			jwplayer().stop()
		});
                
                $('#grammatical-resource-rubric').on('change', function() {
                    if (this.value !== ""){
                        switch (this.value){
                            case 'A':   $('#grammatical-resource-text-info-span').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#grammatical-resource-info-modal-label').html('Bla bla bla bla bla bla bla bla bla bla bla bla bla');
                                        $('#grammatical-resource-info-modal-body').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                            case 'B':   $('#grammatical-resource-text-info-span').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#grammatical-resource-info-modal-label').html('Ble ble ble ble ble ble ble ble ble ble ble ble ble');
                                        $('#grammatical-resource-info-modal-body').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                            case 'C':   $('#grammatical-resource-text-info-span').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#grammatical-resource-info-modal-label').html('Bli bli bli bli bli bli bli bli bli bli bli bli bli');
                                        $('#grammatical-resource-info-modal-body').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                        }
                        $('#grammatical-resource-text-info').css('display', 'block');    
                    }else{
                        $('#grammatical-resource-text-info').css('display', 'none');
                    }
                });    
                $('#lexical-resource-rubric').on('change', function() {
                    if (this.value !== ""){
                        switch (this.value){
                            case 'A':   $('#lexical-resource-text-info-span').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#lexical-resource-info-modal-label').html('Bla bla bla bla bla bla bla bla bla bla bla bla bla');
                                        $('#lexical-resource-info-modal-body').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                            case 'B':   $('#lexical-resource-text-info-span').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#lexical-resource-info-modal-label').html('Ble ble ble ble ble ble ble ble ble ble ble ble ble');
                                        $('#lexical-resource-info-modal-body').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                            case 'C':   $('#lexical-resource-text-info-span').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#lexical-resource-info-modal-label').html('Bli bli bli bli bli bli bli bli bli bli bli bli bli');
                                        $('#lexical-resource-info-modal-body').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                        }
                        $('#lexical-resource-text-info').css('display', 'block');    
                    }else{
                        $('#lexical-resource-text-info').css('display', 'none');
                    }
                });    
                $('#pronunciation-resource-rubric').on('change', function() {
                    if (this.value !== ""){
                        switch (this.value){
                            case 'A':   $('#pronunciation-resource-text-info-span').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#pronunciation-resource-info-modal-label').html('Bla bla bla bla bla bla bla bla bla bla bla bla bla');
                                        $('#pronunciation-resource-info-modal-body').html('A - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                            case 'B':   $('#pronunciation-resource-text-info-span').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#pronunciation-resource-info-modal-label').html('Ble ble ble ble ble ble ble ble ble ble ble ble ble');
                                        $('#pronunciation-resource-info-modal-body').html('B - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                            case 'C':   $('#pronunciation-resource-text-info-span').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a.');
                                        $('#pronunciation-resource-info-modal-label').html('Bli bli bli bli bli bli bli bli bli bli bli bli bli');
                                        $('#pronunciation-resource-info-modal-body').html('C - Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sollicitudin diam dui, nec efficitur orci iaculis a. Nunc quis lectus eget dui pharetra rhoncus id id tellus. Praesent sed ornare turpis, a auctor lectus. Ut imperdiet tempor lorem ut condimentum. Suspendisse sed ornare lectus. Aenean eget nunc eu purus bibendum tristique ac sed eros. Duis pretium tellus in neque tempus, a suscipit tellus maximus. Duis sodales nulla in quam auctor, vitae congue felis vestibulum. Phasellus eu lobortis erat, condimentum vehicula leo. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed gravida turpis vel neque sagittis, in mattis lacus vulputate. Cras quis nibh nunc. Nullam ac euismod nisl.');
                                        break;
                        }
                        $('#pronunciation-resource-text-info').css('display', 'block');    
                    }else{
                        $('#pronunciation-resource-text-info').css('display', 'none');
                    }
                });    
	});
	</script>

	<div class="modal fade" id="jwVideoModal">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo $LanguageInstance->get('Close')?></span></button>
			<h4 class="modal-title"><?php echo $LanguageInstance->get('Video player')?></h4>
		  </div>
		  <div class="modal-body">
			<script src="http://jwpsrv.com/library/MjW8iGEHEeSfCBLddj37mA.js"></script>
			<div id="myElement"><?php echo $LanguageInstance->get('Loading the player')?>...</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $LanguageInstance->get('Close')?></button>
		  </div>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	</body>
	</html>
<?php
	}