<?php
require_once __DIR__ . '/classes/lang.php';
require_once __DIR__ . '/classes/constants.php';
require_once __DIR__ . '/classes/gestorBD.php';
require_once __DIR__ . '/IMSBasicLTI/uoc-blti/lti_utils.php';
require_once __DIR__ . '/classes/pdf.php';
require_once __DIR__ . '/classes/IntegrationTandemBLTI.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;

// Si no existeix objecte usuari o no existeix curs redireccionem cap a l'index...
if (empty($user_obj) || !isset($user_obj->id)) {
    //Tornem a l'index
    header('Location: index.php');
    die();
}

$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
$disable_profile_form = isset($_SESSION[DISABLE_PROFILE_FORM]) ? $_SESSION[DISABLE_PROFILE_FORM] : false;
$feedback_selfreflection_form = isset($_SESSION[FEEDBACK_SELFREFLECTION_FORM]) ? $_SESSION[FEEDBACK_SELFREFLECTION_FORM] : false;
$show_ranking = defined('SHOW_RANKING') && SHOW_RANKING == 1 && (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS] || $disable_profile_form);
//$portfolio = isset($_SESSION[PORTFOLIO]) ? $_SESSION[PORTFOLIO] : false;
$dateStart = !empty($_POST['dateStart']) ? $_POST['dateStart'] : date('Y-m-d', strtotime(date('Y-m-d') . ' -1 months'));
$dateEnd = !empty($_POST['dateEnd']) ? $_POST['dateEnd'] : date('Y-m-d');

$gestorBD = new GestorBD();

$finishedTandem = -1;
if ($user_obj->instructor && $user_obj->instructor == 1 && !empty($_POST['finishedTandem'])) {
    $finishedTandem = $_POST['finishedTandem'];
}

$gestorBD->set_userInTandem($user_obj->id,$course_id,0);

$tandemType = -1;
if ($user_obj->instructor && $user_obj->instructor == 1 && !empty($_POST['tandemType'])) {
    $tandemType = (int) $_POST['tandemType'];
}

$showFeedback = -1;
if (!empty($_POST['showFeedback'])) {
    $showFeedback = $_POST['showFeedback'];
}

$selectedUser = 0;
//lets see if we have a cookie for the selected user
if (!empty($_COOKIE['selecteduser']) && $user_obj->instructor == 1) {
    $selectedUser = (int) $_COOKIE['selecteduser'];
}
if (!empty($_POST['selectUser']) && $user_obj->instructor == 1) {
    $selectedUser = (int) $_POST['selectUser'];
    setcookie('selecteduser', $selectedUser);
}

$viewUserId = $user_obj->id;
if (!empty($selectedUser) && $selectedUser != 0 && $user_obj->instructor == 1) {
    // The instructor wants to view an specific user feedback,
    // so now the page will be printed as a "view as" this user.
    $viewUserId = $selectedUser;
}


$allFeedbacks = $gestorBD->getAllUserFeedbacks($viewUserId, $course_id, 1, -1, '', '', $_SESSION[USE_WAITING_ROOM_NO_TEAMS]);
$feedbacks = $gestorBD->getAllUserFeedbacks($viewUserId, $course_id, $showFeedback, $finishedTandem, $dateStart, $dateEnd, $_SESSION[USE_WAITING_ROOM_NO_TEAMS], $tandemType);

if ($viewUserId == -1) {
    $viewUserId = $user_obj->id;
}

// lets check if the user has filled the first profile form.
$firstProfileForm = $gestorBD->getUserPortfolioProfile('first', $user_obj->id);
$newProfileForm = $gestorBD->getUserPortfolioProfile('new', $user_obj->id);
$secondProfileForm = false;
// $show_second_form = SHOW_SECOND_FORM;
$show_second_form = false;
if ($show_second_form) {
    $secondProfileForm = $gestorBD->getUserPortfolioProfile('second', $user_obj->id);
}

$showDelete = false;
if ($user_obj->admin || $user_obj->instructor) {
    $showDelete = true;
}

//lets save the registration form
if (isset($_POST['extra-info-form'])) {
    $inputs = array('skills_grade', 'fluency', 'accuracy', 'improve_pronunciation', 'improve_vocabulary', 'improve_grammar', 's2_pronunciation_txt', 's2_vowels_txt', 's2_consonants_txt', 's2_stress_txt', 's2_intonation_txt', 's2_vocabulary_txt', 's2_vocab_txt', 's2_false_friends_txt', 's2_grammar_txt', 's2_verb_agreement_txt', 's2_noun_agreement_txt', 's2_sentence_txt', 's2_connectors_txt', 's2_aspects_txt');
    $save = new stdclass();
    foreach ($inputs as $in) {
        if (!empty($_POST[$in])) {
            $save->$in = strip_tags($_POST[$in]);
        }
    }
    $data = serialize($save);
    $gestorBD->saveFormUserProfile('first', $user_obj->id, $data, $firstProfileForm, isset($_POST['portfolio_form_id']) ? $_POST['portfolio_form_id'] : false);
    $firstProfileForm = $gestorBD->getUserPortfolioProfile('first', $user_obj->id);
} else if (!empty($_SESSION[USE_WAITING_ROOM_NO_TEAMS]) && isset($_POST['extra-info-form-new'])) {
    $inputs = array('grammaticalresource', 'lexicalresource', 'discoursemangement', 'pronunciation', 'interactivecommunication');
    $save = new stdclass();
    foreach ($inputs as $in) {
        if (!empty($_POST[$in])) {
            $save->$in = strip_tags($_POST[$in]);
        }
    }
    $data = serialize($save);
    $gestorBD->saveFormUserProfile('new', $user_obj->id, $data, $firstProfileForm, isset($_POST['portfolio_form_id']) ? $_POST['portfolio_form_id'] : false);
    $newProfileForm = $gestorBD->getUserPortfolioProfile('new', $user_obj->id);
} elseif ($show_second_form && isset($_POST['extra-info-form-second'])) {
    $inputs = array('my_language_level', 'my_current_language_level', 'achived_objectives_proposed', 'fluency', 'accuracy', 'vocabulary', 'grammar', 'what_I_can_do_better', 'how_I_can_do_improve', 'received_feedback_help_to_improve', 'what_feedback_do_received', 'feedback_to_partner_help_me', 'how_feedback_to_partner_help_me', 'have_more_confidence', 'can_apply_the_learning', 'know_how_apply_it');
    $save = new stdclass();
    foreach ($inputs as $in) {
        if (!empty($_POST[$in])) {
            $save->$in = strip_tags($_POST[$in]);
        }
    }
    $data = serialize($save);
    $gestorBD->saveFormUserProfile('second', $user_obj->id, $data, $secondProfileForm, isset($_POST['portfolio_form_id_second']) ? $_POST['portfolio_form_id_second'] : false);
    //Get the previous stored data
    $secondProfileForm = $gestorBD->getUserPortfolioProfile('second', $user_obj->id);
}

// PDF was requested
if (!empty($_POST['get_pdf'])) {
    if (!empty($selectedUser)) {
        $userForPdf = $selectedUser;
    } else {
        $userForPdf = $user_obj->id;
    }
    generatePDF($userForPdf, $course_id);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" media="all" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="js/jquery-ui-1.11.2.custom/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/tandem-waiting-room.css">
    <link rel="stylesheet" type="text/css" media="all" href="css/slider.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/star-rating.min.css"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/portfolio.css?version=20191022"/>
    <link rel="stylesheet" type="text/css" media="all" href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" />
</head>
<body>
<div class="container">
    <div class='row'>
        <div class='col-md-9'>
	        <?php if (!empty($_SESSION[LMS_RETURN_URL])) { ?>
                <a class="btn btn-success" type='button'
                   href="<?php echo $_SESSION[LMS_RETURN_URL]?>"><?php echo $LanguageInstance->get('Return to course') ?></a>
	        <?php } ?>
            <?php if ($show_ranking) { ?>
                <button class="btn btn-success" type='button'
                        onclick="window.location ='ranking.php';"><?php echo $LanguageInstance->get('Go to the ranking') ?></button>
            <?php } ?>
            <?php if (!$disable_profile_form) { ?>
                <button class="btn btn-success" type='button'
                        id='viewProfileForm'><?php echo $LanguageInstance->get('View your profile') ?></button>
                <?php if ($show_second_form) { ?>
                    <button class="btn btn-success" type='button'
                            id='viewProfileFormSecond'><?php echo $LanguageInstance->get('Self Assessment') ?></button>
                    <?php if ($secondProfileForm && isset($secondProfileForm['data'], $secondProfileForm['data']->my_language_level)) { ?>
                        <!--form action='' method='POST' role='form' id='pdfForm'>
                            <input type='hidden' name='get_pdf' value='1'/>
                            <button class="btn btn-success" type='submit'
                                    id='viewProfileFormSecond'><?php echo $LanguageInstance->get('Download a PDF file with all Tandems') ?></button>
                        </form-->
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </div>
        <div class='col-md-3 text-right'>
            <a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo') ?>"/></a>
        </div>
    </div>
    <div class="row">
        <div class='col-md-6'>
            <h1 class='title'><?php echo $LanguageInstance->get('My portfolio feedback'); ?></h1>
        </div>
        <div class='col-md-6 text-right'>
            <div class='welcomeMessage'>
                <?php
                if ($show_ranking) {
                    $userStudiesSpanish = 0 === strpos($_SESSION[LANG], 'en');
                    $globalRankingPosition = 0;
                    $spanishRankingPosition = 0;
                    $englishRankingPosition = 0;
                    $userRankingPosition = 0;
                    if ($_SESSION[USE_WAITING_ROOM_NO_TEAMS]) {
                        $globalRankingPosition = $gestorBD->getUserRankingPosition($viewUserId, '', $course_id, true);
                        $userRankingPosition = $globalRankingPosition;
                    } else {
                        if ($userStudiesSpanish) {
                            $spanishRankingPosition = $gestorBD->getUserRankingPosition($viewUserId, 'es_ES', $course_id, false);
                            $userRankingPosition = $spanishRankingPosition;
                        } else {
                            $englishRankingPosition = $gestorBD->getUserRankingPosition($viewUserId, 'en_US', $course_id, false);
                            $userRankingPosition = $englishRankingPosition;
                        }
                    }
                    $positionInRankingTxt = $LanguageInstance->get('Hello %1');
                    $positionInRankingTxt = str_replace('%1', $gestorBD->getUserName($viewUserId), $positionInRankingTxt);
                    if ($userRankingPosition > 0) {
                        $positionInRankingTxt .= $LanguageInstance->get(', your position in the ranking is ') . '<b>' . $userRankingPosition . '</b>';
                    }
                    echo $positionInRankingTxt;
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    if ($selectedUser !== -1) {
        if ($disable_profile_form && $show_ranking) {
            $userPointsData = $gestorBD->getUserRankingPoints($viewUserId, $course_id);
            $userPointsDataNoFilterDate = $gestorBD->getUserRankingPoints($viewUserId, $course_id, false, false);?>
            <div class="row well">
                <div class="col-md-6">
                    <div class="list_group">
                        <?php if (isset($userPointsData->points)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('points'); ?>:
                                <strong><?php echo $userPointsData->points; ?></strong>
                            </div>
                        <?php } ?>
                        <?php if (isset($userPointsDataNoFilterDate->number_of_tandems)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Number of Tandems'); ?>:
                                <strong><?php echo $userPointsDataNoFilterDate->number_of_tandems; ?></strong>
                                <?php if (defined('CERTIFICATION_MINIMUM_TANDEMS') && !empty(CERTIFICATION_MINIMUM_TANDEMS)) {
                                    $tandemsProgress = round($userPointsDataNoFilterDate->number_of_tandems * 100 / CERTIFICATION_MINIMUM_TANDEMS);
                                    $tandemsProgress = $tandemsProgress > 100 ? 100 : $tandemsProgress; ?>
                                    <div class="progress" style="margin: 5px 0 0 0;">
                                        <div class="progress-bar progress-bar-success"
                                             role="progressbar"
                                             aria-valuenow="<?php echo $tandemsProgress; ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width:<?php echo $tandemsProgress; ?>%">
                                            <?php echo $tandemsProgress . '%'; ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php if (isset($userPointsDataNoFilterDate->total_time)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Total Duration'); ?>:
                                <strong><?php echo gmdate('H:i:s', (int) $userPointsDataNoFilterDate->total_time); ?></strong>
                                <?php if (defined('CERTIFICATION_MINIMUM_HOURS') && !empty(CERTIFICATION_MINIMUM_HOURS)) {
                                    $hoursProgress = round($userPointsDataNoFilterDate->total_time * 100 / (CERTIFICATION_MINIMUM_HOURS * 3600));
                                    $hoursProgress = $hoursProgress > 100 ? 100 : $hoursProgress; ?>
                                    <div class="progress" style="margin: 5px 0 0 0;">
                                        <div class="progress-bar progress-bar-success"
                                             role="progressbar"
                                             aria-valuenow="<?php echo $hoursProgress; ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width:<?php echo $hoursProgress; ?>%">
                                            <?php echo $hoursProgress . '%'; ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <div class="list-group-item">
                            <?php
                            $total_tandems_by_person = $gestorBD->get_num_tandems_by_person($course_id, $viewUserId);
                            echo $LanguageInstance->get('Number of different partners'); ?>:
                            <strong><?php echo count($total_tandems_by_person); ?></strong>
                            <?php
                                $numDistinctPartnersProgress = round(count($total_tandems_by_person) * 100 / TandemBadges::NUM_TANDEMS_SOCIAL_PERSON);
                                $numDistinctPartnersProgress = $numDistinctPartnersProgress > 100 ? 100 : $numDistinctPartnersProgress; ?>
                                <div class="progress" style="margin: 5px 0 0 0;">
                                    <div class="progress-bar progress-bar-success"
                                         role="progressbar"
                                         aria-valuenow="<?php echo $numDistinctPartnersProgress; ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         style="width:<?php echo $numDistinctPartnersProgress; ?>%">
                                        <?php echo $numDistinctPartnersProgress . '%'; ?>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="list_group">
                        <?php if (!$feedback_selfreflection_form && isset($userPointsData->user_fluency)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Fluency'); ?>:
                                <strong><?php echo $userPointsData->user_fluency; ?>%</strong>
                            </div>
                        <?php } ?>
                        <?php if (!$feedback_selfreflection_form && isset($userPointsData->user_accuracy)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Accuracy'); ?>:
                                <strong><?php echo $userPointsData->user_accuracy; ?>%</strong>
                            </div>
                        <?php } ?>
                        <?php if (!$feedback_selfreflection_form && isset($userPointsData->user_overall_grade)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Overall Grade'); ?>:
                                <strong><?php echo $userPointsData->user_overall_grade; ?>%</strong>
                            </div>
                        <?php } ?>
                        <?php if (isset($userPointsData->number_of_tandems_with_feedback)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Feedback received'); ?>:
                                <strong><?php echo $userPointsDataNoFilterDate->number_of_tandems_with_feedback; ?></strong>
                            </div>
                        <?php } ?>
                        <?php if (isset($allFeedbacks)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Feedback given');
                                $numberOfGivenFeedbacks = count($allFeedbacks); ?>:
                                <strong><?php echo $numberOfGivenFeedbacks; ?></strong>
                                <?php if (defined('CERTIFICATION_MINIMUM_FEEDBACKS') && !empty(CERTIFICATION_MINIMUM_FEEDBACKS)) {
                                    $feedbacksProgress = round($numberOfGivenFeedbacks * 100 / CERTIFICATION_MINIMUM_FEEDBACKS);
                                    $feedbacksProgress = $feedbacksProgress > 100 ? 100 : $feedbacksProgress; ?>
                                    <div class="progress" style="margin: 5px 0 0 0;">
                                        <div class="progress-bar progress-bar-success"
                                             role="progressbar"
                                             aria-valuenow="<?php echo $feedbacksProgress; ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width:<?php echo $feedbacksProgress; ?>%">
                                            <?php echo $feedbacksProgress . '%'; ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

		                    <?php // get badges
		                    $user_data = $gestorBD->getRankingUserData($viewUserId, $course_id);
		                    $badges = array();
		                    if ($user_data && isset($user_data['lang'])) {
			                    $level_image = TandemBadges::get_level_badge_image($user_data['badge_level']);
			                    if ($level_image) {
				                    $badges['level_badge_'.$user_data['badge_level']] = $level_image;
                                }
			                    $badge_feedback_expert = $user_data['badge_feedback_expert'] == 1 ? 'badge_feedback_expert.png': false;
			                    if ($badge_feedback_expert) {
				                    $badges['badge_feedback_expert'] = $badge_feedback_expert;
			                    }
			                    $badge_loyalty_image = $user_data['badge_loyalty'] == 1 ? 'badge_loyalty.png': false;
			                    if ($badge_loyalty_image) {
				                    $badges['badge_loyalty'] = $badge_loyalty_image;
			                    }
			                    $badge_social_image = $user_data['badge_social'] == 1 ? 'badge_social.png': false;
			                    if ($badge_social_image) {
				                    $badges['badge_social'] = $badge_social_image;
			                    }
			                    $badge_week_ranking = $user_data['badge_week_ranking'] == 1 ? 'badge_week_ranking.png': false;
			                    if ($badge_week_ranking) {
				                    $badges['badge_week_ranking'] = $badge_week_ranking;
			                    }
			                    $badge_forms = $user_data['badge_forms'] == 1 ? 'badge_forms.png': false;
			                    if ($badge_forms) {
				                    $badges['badge_forms'] = $badge_forms;
			                    }
		                    }
		                    if (count($badges) > 0) {
		                    ?>
                        <div class="list-group-item">
                            <?php
		                    foreach ($badges as $key => $badge) {
		                        $badge_name = $LanguageInstance->get($key);
		                        $badge_desc = $LanguageInstance->get($key.'_desc');
		                        echo '<img src="images/badges/'.$badge.'" class="tandem-badge" title="'.$badge_name.'" alt="'.$badge_desc.'"/>';
                            }
		                    ?>
                        </div>
                        <?php } ?>
                        <?php if (isset($userPointsDataNoFilterDate->user_feedback_stars)) { ?>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get("Partners' valoration of your feedback"); ?>:
                                <input name="partner_rate" id="partner_rate" type="text"/>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if (!$disable_profile_form && !empty($newProfileForm)) { ?>
            <div class="row well">
                <div class="col-md-6">
                    <div class="list_group">
                        <div class="list-group-item">
                            <?php echo $LanguageInstance->get('Grammatical Resource');
                            $profileGrammaticalResource = isset($newProfileForm['data']->grammaticalresource) ? $newProfileForm['data']->grammaticalresource : '0';
                            echo ': <strong>' . $profileGrammaticalResource . '%</strong>';
                            ?>
                        </div>
                        <div class="list-group-item">
                            <?php echo $LanguageInstance->get('Lexical Resource');
                            $profileLexicalResource = isset($newProfileForm['data']->lexicalresource) ? $newProfileForm['data']->lexicalresource : '0';
                            echo ': <strong>' . $profileLexicalResource . '%</strong>';
                            ?>
                        </div>
                        <div class="list-group-item">
                            <?php echo $LanguageInstance->get('Discourse Mangement');
                            $profileDiscourseMangement = isset($newProfileForm['data']->discoursemangement) ? $newProfileForm['data']->discoursemangement : '0';
                            echo ': <strong>' . $profileDiscourseMangement . '%</strong>';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <ul class="list_group">
                        <li class="list-group-item">
                            <?php echo $LanguageInstance->get('Pronunciation');
                            $profilePronunciation = !empty($newProfileForm['data']->pronunciation) ? $newProfileForm['data']->pronunciation : '';
                            echo ': <strong>' . $profilePronunciation . '%</strong>';
                            ?>
                        </li>
                        <li class="list-group-item">
                            <?php echo $LanguageInstance->get('Interactive Communication');
                            $profileInteractiveCommunication = !empty($newProfileForm['data']->interactivecommunication) ? $newProfileForm['data']->interactivecommunication : '';
                            echo ': <strong>' . $profileInteractiveCommunication . '%</strong>';
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
            <?php
        } else {
            if (!$disable_profile_form && !empty($firstProfileForm)) { ?>
                <div class="row well">
                    <div class="col-md-6">
                        <div class="list_group">
                            <div class="list-group-item">
                                <?php
                                echo $LanguageInstance->get('Grade your speaking skills');
                                echo ': <strong>' . getSkillsLevel($firstProfileForm['data']->skills_grade,
                                        $LanguageInstance) . '</strong>';
                                ?>
                            </div>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Fluency');
                                $profileFluency = isset($firstProfileForm['data']->fluency) ? $firstProfileForm['data']->fluency : '0';
                                echo ': <strong>' . $profileFluency . '%</strong>';
                                ?>
                            </div>
                            <div class="list-group-item">
                                <?php echo $LanguageInstance->get('Accuracy');
                                $accuracyProfile = isset($firstProfileForm['data']->accuracy) ? $firstProfileForm['data']->accuracy : '0';
                                echo ': <strong>' . $accuracyProfile . '%</strong>';
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <ul class="list_group">
                            <li class="list-group-item">
                                <?php echo $LanguageInstance->get('My pronunciation');
                                $myPronunciation = isset($firstProfileForm['data']->improve_pronunciation) ? $firstProfileForm['data']->improve_pronunciation : '';
                                echo ': <strong>' . $myPronunciation . '</strong>';
                                ?>
                            </li>
                            <li class="list-group-item">
                                <?php echo $LanguageInstance->get('My vocabulary');
                                $myVocabulary = !empty($firstProfileForm['data']->improve_vocabulary) ? $firstProfileForm['data']->improve_vocabulary : '';
                                echo ': <strong>' . $myVocabulary . '</strong>';
                                ?>
                            </li>
                            <li class="list-group-item">
                                <?php echo $LanguageInstance->get('My grammar');
                                $myGrammar = !empty($firstProfileForm['data']->improve_grammar) ? $firstProfileForm['data']->improve_grammar : '';
                                echo ': <strong>' . $myGrammar . '</strong>';
                                ?>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php }
        }
    } ?>
    <div class='row'>
        <div class='col-md-12'>
            <div class="alert alert-info" role="alert"><?php echo $LanguageInstance->get('portfolio_info') ?></div>
        </div>
    </div>
    <?php if ($showDelete) { ?>
        <div class='row' id="deleteError" style="display: none">
            <div class='col-md-12'>
                <div class="alert alert-danger"
                     role="alert"><?php echo $LanguageInstance->get('Error deleting tandem') ?></div>
            </div>
        </div>
    <?php } ?>
    <?php
    if ($user_obj->instructor == 1) {
        $usersList = $gestorBD->getAllUsers($course_id); ?>
        <div class='row'>
            <div class='col-md-12'>
                <form action='' method="POST" id='selectUserForm' class="form-inline" role='form'>
                    <div class="form-group">
                        <select name='selectUser' id="selectUser" class='form-control input-sm selected-user-select2'>
                            <option value='0'><?php echo $LanguageInstance->get('Select user') ?></option>
                            <option value='-1' <?php echo(isset($selectedUser) && $selectedUser == -1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('All users') ?></option>
                            <?php
                            foreach ($usersList as $u) {
                                $selected = '';
                                if (isset($selectedUser) && $selectedUser == $u['id']) {
                                    $selected = ' selected="selected"';
                                }
                                $userFullname = trim($u['fullname']);
                                $encoding = mb_detect_encoding($userFullname);
                                if ($encoding && $encoding !== 'ISO-8859-1') {
                                    $userFullname = mb_convert_encoding($userFullname, 'ISO-8859-1', $encoding);
                                }
                                if (!empty($userFullname)) {
                                    echo "<option value='" . $u['id'] . "' $selected>" . $userFullname . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <span class="help-block"><?php echo $LanguageInstance->get('Select a user to view their portfolio'); ?></span>
                    </div>
                    &nbsp;
                    <div class="form-group">
                        <select name='showFeedback' id="showFeedback" class='form-control input-sm'>
                            <option value='-1' <?php echo(isset($showFeedback) && $showFeedback == -1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('All Feedbacks') ?></option>
                            <option value='1' <?php echo(isset($showFeedback) && $showFeedback == 1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('complete') ?></option>
                            <option value='2' <?php echo(isset($showFeedback) && $showFeedback == 2 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('incomplete') ?></option>
                        </select>
                        <span class="help-block"><?php echo $LanguageInstance->get('Show feedback status'); ?></span>
                    </div>
                    &nbsp;
                    <div class="form-group">
                        <select name='finishedTandem' id="finishedTandem" class='form-control input-sm'>
                            <option value='-1' <?php echo(isset($finishedTandem) && $finishedTandem == -1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('all') ?></option>
                            <option value='1' <?php echo(isset($finishedTandem) && $finishedTandem == 1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('finished') ?></option>
                            <option value='2' <?php echo(isset($finishedTandem) && $finishedTandem == 2 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('unfinished') ?></option>
                        </select>
                        <span class="help-block"><?php echo $LanguageInstance->get('Select tandem by status'); ?></span>
                    </div>
                    &nbsp;
                    <div class="form-group">
                        <select name='tandemType' id="tandemType" class='form-control input-sm'>
                            <option value='-1' <?php echo(isset($tandemType) && $tandemType == -1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('all') ?></option>
                            <option value='1' <?php echo(isset($tandemType) && $tandemType == 1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('Roulette Tandem') ?></option>
                            <option value='2' <?php echo(isset($tandemType) && $tandemType == 2 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('YouChoose Tandem') ?></option>
                        </select>
                        <span class="help-block"><?php echo $LanguageInstance->get('Select tandem type'); ?></span>
                    </div>
                    &nbsp;
                    <div class='selectDatesForTandems form-group'>
                        <div class="form-group">
                            <input type='text' class="form-control input-sm" name='dateStart' id='dateStart'
                                   value='<?php echo $dateStart ?>'>
                            <span class="help-block text-right"><?php echo $LanguageInstance->get('Start Date'); ?></span>
                        </div>
                        <div class="form-group">
                            <input type='text' class="form-control input-sm" name='dateEnd' id='dateEnd'
                                   value='<?php echo $dateEnd ?>'>
                            <span class="help-block text-right"> <?php echo $LanguageInstance->get('End Date'); ?></span>
                        </div>
                        <div class="form-group">
                            <button type="submit"
                                    class="btn btn-default input-sm"><?php echo $LanguageInstance->get('View'); ?></button>
                            <span class="help-block">  &nbsp;</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-3'>
                <!--form action='' method='POST' role='form' id='pdfForm'>
                    <input type='hidden' name='get_pdf' value='1'/>
                    <input type='submit'
                           value='<?php echo $LanguageInstance->get('Download a PDF file with all Tandems'); ?>'
                           class='btn btn-success'/>
                </form-->
            </div>
            <div class='col-md-9'>
                <form action='portfolio_excel.php' method='POST' role='form' id='excelForm'>
                    <input type='hidden' name='showFeedback' value='<?php echo $showFeedback ?>'/>
                    <input type='hidden' name='finishedTandem' value='<?php echo $finishedTandem ?>'/>
                    <input type='hidden' name='selectUser' value='<?php echo $selectedUser ?>'/>
                    <input type='hidden' name='dateStart' value='<?php echo $dateStart ?>'/>
                    <input type='hidden' name='dateEnd' value='<?php echo $dateEnd ?>'/>
                    <input type='submit' value='<?php echo $LanguageInstance->get('Export to excel'); ?>'
                           class='btn btn-success'/>
                </form>
            </div>
        </div>
        <p></p>
    <?php } else { ?>
        <div class='row'>
            <div class='col-md-6'>
                <form action='' method="POST" id='showTandemsFeedbackform' class="form-inline" role='form'>
                    <div class="form-group">
                        <select name='showFeedback' id="showFeedback" class='form-control'>
                            <option value='-1' <?php echo(isset($showFeedback) && $showFeedback == 1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('All Feedbacks') ?></option>
                            <option value='1' <?php echo(isset($showFeedback) && $showFeedback == 1 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('complete') ?></option>
                            <option value='2' <?php echo(isset($showFeedback) && $showFeedback == 2 ? 'selected' : '') ?>><?php echo $LanguageInstance->get('incomplete') ?></option>
                        </select>
                        <span class="help-block"><?php echo $LanguageInstance->get('Show feedback status'); ?></span>
                    </div>
                </form>
            </div>
        </div>
    <?php }
    $number = 1; ?>
    <div class='row'>
        <div class="col-md-12">
            <table class="table">
                <tr>
                    <th></th>
                    <?php if ($user_obj->instructor == 1) { ?>
                        <th><?php echo $LanguageInstance->get('Name'); ?></th>
                    <?php } ?>
                    <?php if (!$feedback_selfreflection_form) { ?>
                        <th><?php echo $LanguageInstance->get('Overall rating'); ?></th>
                    <?php } ?>
                    <th><?php echo $LanguageInstance->get('exercise'); ?></th>
                    <th><?php echo $LanguageInstance->get('Partner Name'); ?></th>
                    <th><?php echo $LanguageInstance->get('Created'); ?></th>
                    <th><?php echo $LanguageInstance->get('Total Duration'); ?></th>
                    <th><?php echo $LanguageInstance->get('Duration per task'); ?></th>
                    <th><?php echo $LanguageInstance->get('Feedback given'); ?></th>
                    <th><?php echo $LanguageInstance->get('Feedback received'); ?></th>
                    <?php if ($user_obj->instructor == 1) { ?>
                        <th><?php echo $LanguageInstance->get('Type'); ?></th>
                    <?php } ?>
                    <?php if ($_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]) { ?>
                        <th><?php echo $LanguageInstance->get('Pair language'); ?></th>
                    <?php } ?>
                    <th><?php echo $LanguageInstance->get('Actions'); ?></th>
                </tr>
                <?php
                if (!empty($feedbacks)) {
                    foreach ($feedbacks as $f) {
                        //we have all the tasks total_time in an array, but we need the T1=00:00 format.
                        $tt = array();
                        /** @var array[] $f */
                        foreach ($f['total_time_tasks'] as $key => $val) {
                            $tt[] = 'T' . ++$key . ' = ' . $val;
                        }
                        $feedback_given = true;
                        if (empty($f['feedback_form'])) {
                            $feedback_given = false;
                        }
                        $feedback_received = false;
                        $partnerFeedback = $gestorBD->checkPartnerFeedback($f['id_tandem'], 1);
                        if ($partnerFeedback) {
                            $feedback_received = true;
                        }
                        echo '<tr>';
                        echo '<td>' . ($number++) . '</td>';
                        if ($user_obj->instructor == 1) {
                            echo '<td>' . $f['fullname'] . '</th>';
                        }
                        if (!$feedback_selfreflection_form) {
                            echo "<td class='text-center'>" . getSkillsLevel($f['overall_grade'], $LanguageInstance) . '</td>';
                        }
                        echo '<td>' . $f['exercise'] . '</td>';
	  		            echo '<td>' . $f['partner_fullname'] . '</td>';
	  		            echo '<td>' . $f['created'] . '</td>';
  			            echo '<td>' . $f['total_time'] . '</td>';
  			            echo "<td style='font-size:10px'>" . implode('<br />', $tt) . '</td>';
                        if ($feedback_given) {
                            echo "<td><i class='glyphicon glyphicon-ok green'></i></td>";
                        } else {
                            //echo "<td><i class='glyphicon glyphicon-remove red'></i></td>";
                            echo "<td><i class='glyphicon glyphicon-remove red'></i>" . $LanguageInstance->get('Pending') . '</td>';
                        }
                        if ($feedback_received) {
                            echo "<td><i class='glyphicon glyphicon-ok green'></i></td>";
                        } else {
                            //echo "<td><i class='glyphicon glyphicon-remove red'></i></td>";
                            echo "<td><i class='glyphicon glyphicon-remove red'></i>" . $LanguageInstance->get('Pending') . '</td>';
                        }
                        if ($user_obj->instructor == 1) {
                            echo "<td>".$f['TandemResult']."</td>";
                        }
                        if ($_SESSION[USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE]) {
	                        echo "<td>".($f['language'] == $f['other_language']?$LanguageInstance->get('Same'):$LanguageInstance->get('Distinct')) ."</td>";
                        }
                echo "<td><button data-feedback-id='" . $f['id'] . "' class='btn btn-success btn-sm viewFeedback' " . ($showDelete ? 'target="_blank"' : '') . '>' . $LanguageInstance->get('View') . '</button></td>';
                        if ($showDelete) {
                            echo "<td><button data-delete-id='" . $f['id_tandem'] . "' class='btn btn-danger btn-sm deleteFeedback' >" . $LanguageInstance->get('delete') . '</button></td>';
                        }
                        echo '</tr>';
                    }
                }
                ?>
            </table>
        </div>
    </div>
</div>
<?php if (!$disable_profile_form) { // Start if disable_profile_form ?>
<!-- Modal -->
<div class="modal fade bs-example-modal-lg" id="registry-modal-form" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $LanguageInstance->get('Personal profile'); ?></h4>
            </div>
            <div class="modal-body">
                <!-- EXTRA INFO FORM  -->
                <form id='extra-info' role="form" method="POST">
                    <div class="form-group">
                        <label for="input1"><?php echo $LanguageInstance->get('Grade your speaking skills'); ?></label>
                        <select name='skills_grade' class="form-control">
                            <option><?php echo $LanguageInstance->get('Select one') ?></option>
                            <option value="A" <?php echo (isset($firstProfileForm['data']->skills_grade) && $firstProfileForm['data']->skills_grade == 'A') ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Excellent') ?></option>
                            <option value="B" <?php echo (isset($firstProfileForm['data']->skills_grade) && $firstProfileForm['data']->skills_grade == 'B') ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Very Good') ?></option>
                            <option value="C" <?php echo (isset($firstProfileForm['data']->skills_grade) && $firstProfileForm['data']->skills_grade == 'C') ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Good') ?></option>
                            <option value="D" <?php echo (isset($firstProfileForm['data']->skills_grade) && $firstProfileForm['data']->skills_grade == 'D') ? 'selected' : '' ?>><?php echo $LanguageInstance->get('Pass') ?></option>
                        </select>
                    </div>
                    <h4><?php echo $LanguageInstance->get('During the course I want to improve my'); ?></h4>
                    <div class="form-group">
                        <label for="input2">
                            <?php echo $LanguageInstance->get('Fluency'); ?>&nbsp;&nbsp;
                        </label>
                        <input type='text' name='fluency' class='slider' data-slider-id="ex1Slider"
                               data-slider-value='<?php echo isset($firstProfileForm['data']->fluency) ? $firstProfileForm['data']->fluency : '' ?>'/>
                        %
                    </div>
                    <div class="form-group">
                        <label for="input2">
                            <?php echo $LanguageInstance->get('Accuracy'); ?>&nbsp;&nbsp;
                        </label>
                        <input type='text' name='accuracy' class='slider' data-slider-id="ex2Slider" value=""
                               data-slider-value='<?php echo isset($firstProfileForm['data']->accuracy) ? $firstProfileForm['data']->accuracy : '' ?>'/>
                        %
                    </div>
                    <h4><?php echo $LanguageInstance->get('During the course I also want to improve'); ?></h4>
                    <div class="form-group">
                        <label for="input2">
                            <?php echo $LanguageInstance->get('My pronunciation'); ?>
                        </label>
                        <textarea name='improve_pronunciation' class="form-control"
                                  rows="3"><?php echo isset($firstProfileForm['data']->improve_pronunciation) ? $firstProfileForm['data']->improve_pronunciation : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="input3">
                            <?php echo $LanguageInstance->get('My vocabulary'); ?>
                        </label>
                        <textarea name='improve_vocabulary' class="form-control"
                                  rows="3"><?php echo isset($firstProfileForm['data']->improve_vocabulary) ? $firstProfileForm['data']->improve_vocabulary : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="input4">
                            <?php echo $LanguageInstance->get('My grammar'); ?>
                        </label>
                        <textarea name='improve_grammar' class="form-control"
                                  rows="3"><?php echo isset($firstProfileForm['data']->improve_grammar) ? $firstProfileForm['data']->improve_grammar : '' ?></textarea>
                    </div>
                    <input type='hidden' name='extra-info-form' value='1'/>
                    <?php
                    if (!empty($firstProfileForm['id'])) {
                        echo "<input type='hidden' name='portfolio_form_id' value='" . $firstProfileForm['id'] . "' />";
                    }
                    ?>
                </form>
            </div>
            <div class="modal-footer">
                <!--span class="small"><?php echo $LanguageInstance->get('cannot_be_modified') ?></span-->
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo $LanguageInstance->get('Close'); ?></button>
                <button type="button" id='submit-extra-info'
                        class="btn btn-success"><?php echo $LanguageInstance->get('Save changes'); ?></button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade bs-example-modal-lg" id="registry-modal-form-new" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $LanguageInstance->get('Personal profile'); ?></h4>
            </div>
            <div class="modal-body">
                <!-- EXTRA INFO FORM  -->
                <form id='extra-info-new' role="form" method="POST">
                    <h4><?php echo $LanguageInstance->get('During the course I want to improve my'); ?></h4>
                    <div class="form-group">
                        <label for="input2">
                            <?php echo $LanguageInstance->get('Grammatical Resource'); ?>&nbsp;&nbsp;
                        </label>
                        <input type='text' name='grammaticalresource' class='slider' data-slider-id="ex1Slider"
                               data-slider-value='<?php echo isset($newProfileForm['data']->grammaticalresource) ? $newProfileForm['data']->grammaticalresource : '' ?>'/>
                        %
                    </div>
                    <div class="form-group">
                        <label for="input2">
                            <?php echo $LanguageInstance->get('Lexical Resource'); ?>&nbsp;&nbsp;
                        </label>
                        <input type='text' name='lexicalresource' class='slider' data-slider-id="ex2Slider" value=""
                               data-slider-value='<?php echo isset($newProfileForm['data']->lexicalresource) ? $newProfileForm['data']->lexicalresource : '' ?>'/>
                        %
                    </div>
                    <div class="form-group">
                        <label for="input2">
                            <?php echo $LanguageInstance->get('Discourse Mangement'); ?>&nbsp;&nbsp;
                        </label>
                        <input type='text' name='discoursemangement' class='slider' data-slider-id="ex3Slider" value=""
                               data-slider-value='<?php echo isset($newProfileForm['data']->discoursemangement) ? $newProfileForm['data']->discoursemangement : '' ?>'/>
                        %
                    </div>
                    <div class="form-group">
                        <label for="input2">
                            <?php echo $LanguageInstance->get('Pronunciation'); ?>&nbsp;&nbsp;
                        </label>
                        <input type='text' name='pronunciation' class='slider' data-slider-id="ex4Slider" value=""
                               data-slider-value='<?php echo isset($newProfileForm['data']->pronunciation) ? $newProfileForm['data']->pronunciation : '' ?>'/>
                        %
                    </div>
                    <div class="form-group">
                        <label for="input2">
                            <?php echo $LanguageInstance->get('Interactive Communication'); ?>&nbsp;&nbsp;
                        </label>
                        <input type='text' name='interactivecommunication' class='slider' data-slider-id="ex5Slider"
                               value=""
                               data-slider-value='<?php echo isset($newProfileForm['data']->interactivecommunication) ? $newProfileForm['data']->interactivecommunication : '' ?>'/>
                        %
                    </div>
                    <input type='hidden' name='extra-info-form-new' value='1'/>
                    <?php
                    if (!empty($newProfileForm['id'])) {
                        echo "<input type='hidden' name='portfolio_form_id' value='" . $newProfileForm['id'] . "' />";
                    }
                    ?>
                </form>
            </div>
            <div class="modal-footer">
                <!--span class="small"><?php echo $LanguageInstance->get('cannot_be_modified') ?></span-->
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo $LanguageInstance->get('Close'); ?></button>
                <button type="button" id='submit-extra-info-new'
                        class="btn btn-success"><?php echo $LanguageInstance->get('Save changes'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php if ($show_second_form) { ?>
<!-- Modal -->
<div class="modal fade bs-example-modal-lg" id="registry-modal-form-second" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabelSecond" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title"
                    id="myModalLabel"><?php echo $LanguageInstance->get('Self Assessment Form'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="row">
                        <div class="col-lg-2"></div>
                        <div class="col-lg-8"><?php echo $LanguageInstance->get('The aim of this form is for you to self-assess your own learning'); ?>
                            .
                        </div>
                        <div class="col-lg-2"></div>
                    </div>
                    <div class="row">
                        <div class="col-lg-2"></div>
                        <div class="col-lg-8"><?php echo $LanguageInstance->get('Thinking about and being aware of your learning is key when learning a language'); ?>
                            .
                        </div>
                        <div class="col-lg-2"></div>
                    </div>
                    <div class="row">
                        <div class="col-lg-2"></div>
                        <div class="col-lg-8"><?php echo $LanguageInstance->get('If you are aware of your language level, you will know what to do to keep improving'); ?>
                            .
                        </div>
                        <div class="col-lg-2"></div>
                    </div>
                    <!-- EXTRA INFO FORM  -->
                    <form data-toggle="validator" id='extra-info-second' role="form" method="POST">
                        <div class="form-group">
                            <label for="my_language_level"
                                   id="label_my_language_level">1. <?php echo $LanguageInstance->get('My language level is'); ?>
                                *</label>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <input type="radio" required name='my_language_level'
                                                   value="Intermediate" <?php echo isset($secondProfileForm['data']->my_language_level) && $secondProfileForm['data']->my_language_level == 'Intermediate' ? 'checked' : '' ?>>
                                        </span>
                                        <span class="form-control"><?php echo $LanguageInstance->get('Intermediate'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <input type="radio" required name='my_language_level'
                                                   value="Advanced" <?php echo isset($secondProfileForm['data']->my_language_level) && $secondProfileForm['data']->my_language_level == 'Advanced' ? 'checked' : '' ?>>
                                        </span>
                                        <span class="form-control"><?php echo $LanguageInstance->get('Advanced'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <input type="radio" required name='my_language_level'
                                                value="Proficiency" <?php echo isset($secondProfileForm['data']->my_language_level) && $secondProfileForm['data']->my_language_level == 'Proficiency' ? 'checked' : '' ?>>
                                        </span>
                                        <span class="form-control"><?php echo $LanguageInstance->get('Proficiency'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="my_current_language_level">2.
                                <?php echo $LanguageInstance->get('Could you asses your target level now?'); ?><!--br><span class="small"><?php echo $LanguageInstance->get('During this course I have improved'); ?></span--></label>
                            <div class="form-group">
                                <label for="input2">
                                    <?php echo $LanguageInstance->get('Fluency'); ?>&nbsp;&nbsp;
                                </label>
                                <input type='text' required name='fluency' class='slider'
                                       data-slider-id="ex1SliderSecond"
                                       data-slider-value='<?php echo isset($secondProfileForm['data']->fluency) ? $secondProfileForm['data']->fluency : '' ?>'/>
                                %
                            </div>
                            <div class="form-group">
                                <label for="input2">
                                    <?php echo $LanguageInstance->get('Accuracy'); ?>&nbsp;&nbsp;
                                </label>
                                <input type='text' required name='accuracy' class='slider'
                                       data-slider-id="ex2SliderSecond" value=""
                                       data-slider-value='<?php echo isset($secondProfileForm['data']->accuracy) ? $secondProfileForm['data']->accuracy : '' ?>'/>
                                %
                            </div>
                            <div class="form-group">
                                <label for="input2">
                                    <?php echo $LanguageInstance->get('Vocabulary'); ?>&nbsp;&nbsp;
                                </label>
                                <input type='text' required name='vocabulary' class='slider'
                                       data-slider-id="ex3SliderSecond" value=""
                                       data-slider-value='<?php echo isset($secondProfileForm['data']->vocabulary) ? $secondProfileForm['data']->vocabulary : '' ?>'/>
                                %
                            </div>
                            <div class="form-group">
                                <label for="input2">
                                    <?php echo $LanguageInstance->get('Grammar'); ?>&nbsp;&nbsp;
                                </label>
                                <input type='text' required name='grammar' class='slider'
                                       data-slider-id="ex4SliderSecond" value=""
                                       data-slider-value='<?php echo isset($secondProfileForm['data']->grammar) ? $secondProfileForm['data']->grammar : '' ?>'/>
                                %
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="achived_objectives_proposed">
                                3. <?php echo $LanguageInstance->get('I have achieved the objectives I set at the beginning of the course'); ?>
                                &nbsp;&nbsp;
                            </label>
                            <div class="row">
                                <?php
                                for ($i = 1; $i <= 4; $i++) { ?>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <input type="radio" name='achived_objectives_proposed'
                                                       value="<?php echo $i ?>" <?php echo isset($secondProfileForm['data']->achived_objectives_proposed) && $secondProfileForm['data']->achived_objectives_proposed == $i ? 'checked' : '' ?>>
                                            </span>
                                            <span class="form-control"><?php echo getScaleGrade($LanguageInstance, $i) ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="what_I_can_do_better">
                                4. <?php echo $LanguageInstance->get('What things could you have done better in this course?'); ?>
                                <br><span
                                        class="small"><?php echo $LanguageInstance->get('(i.e. Searching for resources on my own, more participation , etc)') ?></span>
                            </label>
                            <textarea name='what_I_can_do_better' class="form-control"
                                      rows="3"><?php echo isset($secondProfileForm['data']->what_I_can_do_better) ? $secondProfileForm['data']->what_I_can_do_better : '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="how_I_can_do_improve">
                                5. <?php echo $LanguageInstance->get('After your participation in the course, are you more aware of how to improve your language level?'); ?>
                                <br><span
                                        class="small"><?php echo $LanguageInstance->get('(for instance, seeing films, reading books, etc)') ?></span>
                            </label>
                            <textarea name='how_I_can_do_improve' class="form-control"
                                      rows="3"><?php echo isset($secondProfileForm['data']->how_I_can_do_improve) ? $secondProfileForm['data']->how_I_can_do_improve : '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="received_feedback_help_to_improve">
                                6. <?php echo $LanguageInstance->get('The feedback I have been given has helped me to improve'); ?>
                                &nbsp;&nbsp;
                            </label>
                            <div class="row">
                                <?php
                                for ($i = 1; $i <= 4; $i++) { ?>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <input type="radio" name='received_feedback_help_to_improve'
                                                       value="<?php echo $i ?>" <?php echo isset($secondProfileForm['data']->received_feedback_help_to_improve) && $secondProfileForm['data']->received_feedback_help_to_improve == $i ? 'checked' : '' ?>>
                                            </span>
                                            <span class="form-control"><?php echo getScaleGrade($LanguageInstance, $i) ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="what_feedback_do_received">
                                7. <?php echo $LanguageInstance->get('What aspects of the feedback provided to you were helpful and what were not?'); ?>
                            </label>
                            <textarea name='what_feedback_do_received' class="form-control"
                                      rows="3"><?php echo isset($secondProfileForm['data']->what_feedback_do_received) ? $secondProfileForm['data']->what_feedback_do_received : '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-8">
                                    <label for="feedback_to_partner_help_me">
                                        8. <?php echo $LanguageInstance->get('Giving feedback to my partners has also helped me in the learning process'); ?>
                                        &nbsp;&nbsp;
                                    </label>
                                </div>
                                <div class="col-lg-4">
                                    <label for="how_feedback_to_partner_help_me">
                                        <?php echo $LanguageInstance->get('Explain how'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <?php for ($i = 1; $i <= 4; $i++) { ?>
                                    <div class="col-lg-2">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <input type="radio" name='feedback_to_partner_help_me'
                                                       value="<?php echo $i ?>" <?php echo isset($secondProfileForm['data']->feedback_to_partner_help_me) && $secondProfileForm['data']->feedback_to_partner_help_me == $i ? 'checked' : '' ?>>
                                            </span>
                                            <span class="form-control input-xs"
                                                  style="font-size:12px"><?php echo getScaleGrade($LanguageInstance, $i) ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="col-lg-4"><textarea name='how_feedback_to_partner_help_me'
                                                                class="form-control"
                                                                rows="3"><?php echo isset($secondProfileForm['data']->how_feedback_to_partner_help_me) ? $secondProfileForm['data']->how_feedback_to_partner_help_me : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="have_more_confidence">
                                9. <?php echo $LanguageInstance->get('I feel more confident when speaking the target language outside of the course'); ?>
                                &nbsp;&nbsp;
                            </label>
                            <div class="row">
                                <?php
                                for ($i = 1; $i <= 4; $i++) { ?>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <input type="radio" name='have_more_confidence'
                                                       value="<?php echo $i ?>" <?php echo isset($secondProfileForm['data']->have_more_confidence) && $secondProfileForm['data']->have_more_confidence == $i ? 'checked' : '' ?>>
                                            </span>
                                            <span class="form-control"><?php echo getScaleGrade($LanguageInstance, $i) ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="can_apply_the_learning">
                                10. <?php echo $LanguageInstance->get('Thanks to this course I am able to use the language in different contexts, such as my personal life, at a professional level, in social networks (Facebook, etc)'); ?>
                            </label>
                            <div class="row">
                                <?php
                                for ($i = 1; $i <= 4; $i++) { ?>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <input type="radio" name='can_apply_the_learning'
                                                       value="<?php echo $i ?>" <?php echo isset($secondProfileForm['data']->can_apply_the_learning) && $secondProfileForm['data']->can_apply_the_learning == $i ? 'checked' : '' ?>>
                                            </span>
                                            <span class="form-control"><?php echo getScaleGrade($LanguageInstance, $i) ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="know_how_apply_it">
                                11. <?php echo $LanguageInstance->get('I know how to use what I’ve learned from the course in my daily life'); ?>
                                &nbsp;&nbsp;
                            </label>
                            <div class="row">
                                <?php
                                for ($i = 1; $i <= 4; $i++) { ?>
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <input type="radio" name='know_how_apply_it'
                                                       value="<?php echo $i ?>" <?php echo isset($secondProfileForm['data']->know_how_apply_it) && $secondProfileForm['data']->know_how_apply_it == $i ? 'checked' : '' ?>>
                                            </span>
                                            <span class="form-control"><?php echo getScaleGrade($LanguageInstance, $i) ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <input type='hidden' name='extra-info-form-second' value='1'/>
                        <?php if (!empty($secondProfileForm['id'])) {
                            echo "<input type='hidden' name='portfolio_form_id_second' value='" . $secondProfileForm['id'] . "' />";
                        } ?>
                    </form>
                    <div class="row">
                        <div class="col-lg-8"></div>
                        <div id="error_second_form" class="col-lg-4"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <small><?php echo $LanguageInstance->get('Required fields are noted with an asterisk (*)') ?></small>
                    </div>
<!--                    <span class="small">--><?php //echo $LanguageInstance->get('cannot_be_modified'); ?><!--</span>-->
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo $LanguageInstance->get('Close'); ?></button>
                    <button type="button" id='submit-extra-info-second'
                            class="btn btn-success"><?php echo $LanguageInstance->get('Save changes'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
<?php } // End if disable_profile_form ?>
<?php if ($showDelete) { ?>
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo $LanguageInstance->get('Delete Tandem') ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php echo $LanguageInstance->get('Do you want to delete this tandem?') ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo $LanguageInstance->get('Cancel') ?></button>
                    <button type="button" id="deleteBtn" data-id-to-delete="" class="btn btn-primary"
                            data-dismiss="modal"><?php echo $LanguageInstance->get('delete') ?></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
<?php } ?>
    <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script src="js/jquery-ui-1.11.2.custom/jquery-ui.min.js"></script>
    <script src="js/validator.min.js"></script>
    <script src="js/bootstrap-slider2.js"></script>
    <script src="js/star-rating.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <!--suppress JSUnusedLocalSymbols -->
    <script>
        // Export variables for portfolio.js
        var showDelete = <?php echo ($showDelete ? 1 : 0); ?>;
        var isInstructor = <?php echo ($user_obj->instructor == 1 ? 1 : 0); ?>;
        var disableProfileForm = <?php echo ($disable_profile_form ? 1 : 0); ?>;
        var showRegistryForm = <?php echo ((!$_SESSION[USE_WAITING_ROOM_NO_TEAMS] && !$firstProfileForm && $user_obj->instructor != 1) ? 1 : 0); ?>;
        var showNewRegistryForm = <?php echo (($_SESSION[USE_WAITING_ROOM_NO_TEAMS] && !$newProfileForm && $user_obj->instructor != 1)  ? 1 : 0); ?>;
        var showSecondForm = <?php echo ($show_second_form ? 1 : 0); ?>;
        var showSecondRegistryForm = <?php echo ((!$secondProfileForm && $user_obj->instructor != 1) ? 1 : 0); ?>;
        var noTeams = <?php echo ($_SESSION[USE_WAITING_ROOM_NO_TEAMS] ? 1 : 0); ?>;
        var mustFillStr = "<?php echo $LanguageInstance->get('You must fill all required fields'); ?>";
        var userHasFeedbackStars = <?php echo (!empty($userPointsDataNoFilterDate->user_feedback_stars) ? 1 : 0); ?>;
        var starsStr = "<?php echo $LanguageInstance->get('Stars'); ?>";
        var userStars = <?php echo (!empty($userPointsDataNoFilterDate) && !empty($userPointsDataNoFilterDate->user_feedback_stars) ? number_format($userPointsDataNoFilterDate->user_feedback_stars, 1) : 0); ?>;
    </script>
    <script src="js/portfolio.js"></script>
    <?php include_once __DIR__ .'/js/google_analytics.php';?>
</body>
</html>