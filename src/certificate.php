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
include_once(dirname(__FILE__) . '/classes/pdfCertificate.php');

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
	$message = false;
	$gestorBD = new GestorBD();
	require_once(dirname(__FILE__) . '/classes/constants.php');

	$can_get_certificate = false;
	$message_alert_class = 'danger';
	//$hours = defined('CERTIFICATION_MINIMUM_HOURS') ? CERTIFICATION_MINIMUM_HOURS : 3;
	$number_of_tandems_minium = defined('CERTIFICATION_MINIMUM_TANDEMS') ? CERTIFICATION_MINIMUM_TANDEMS : 12;
	// Donar feedback del nombre mínim de tandems.
	$user_id = $user_obj->id;
	if ($user_obj->instructor==1){
		if (isset($_REQUEST['course_id'])){
			$course_id = $_REQUEST['course_id'];
		}
		if (isset($_REQUEST['user_id'])){
			$user_id = $_REQUEST['user_id'];
		}
	}
	$lang = $_SESSION['lang'];
    //$pass_total_time = false;
    $pass_number_of_tandems = false;
    $pass_fill_questionnaire = true ;//false;
    //update the current points and add the final questionnaire
    $gestorBD->updateUserRankingPoints($user_id,$course_id,$lang);
    $row = $gestorBD->getUserRankingPoints($user_id, $course_id, false, false);
    if ($row->points) {
        //$pass_total_time = $row->total_time>(60*60*$hours);
        $pass_number_of_tandems = $row->number_of_tandems>=$number_of_tandems_minium;
        $pass_fill_questionnaire = true;//$gestorBD->theUserCompletedFinalQuestionnaire($course_id, $user_id);
        $can_get_certificate = $pass_number_of_tandems && $pass_fill_questionnaire;
    }
    if ($user_obj->instructor==1 && isset($_GET['get_certificate']) && $_GET['get_certificate']==1){
    	$can_get_certificate = 1;
    }

	if ($can_get_certificate){

		$message_alert_class = 'info';
		$fullname = $user_obj->fullname;
		if ($user_obj->instructor==1) {
			if (isset($_REQUEST['user_id'])){
				$fullname = $gestorBD->getUserName($user_id);
			}
		}

		$identifier = '';
		if(!empty($_POST['certificate_information'])){
			$certificate_data = new stdClass();
			$certificate_data->fullname = isset($_POST['fullname'])?$_POST['fullname']:$fullname;
			$certificate_data->identifier = isset($_POST['identifier'])?$_POST['identifier']:'';
			$data = serialize($certificate_data);
		    $gestorBD->saveFormUserProfile('certificate', $user_id, $data, false, false);
		    $message = $LanguageInstance->get('Data saved. You can download the certificate');
		}
		$certification_data  = $gestorBD->getUserPortfolioProfile('certificate',$user_id);
		if ($certification_data) {
			$fullname = $certification_data['data']->fullname;
			$identifier = $certification_data['data']->identifier;
			if (!$message) {
				$message = $LanguageInstance->get('You can download the certificate');
			}
			if(!empty($_POST['download_certificate'])){
				if ($user_obj->instructor==1){
					$user_data = $gestorBD->getRankingUserData($user_id,$course_id);
					$lang = $user_data['lang'];
				}

				$positionRanking = $gestorBD->getUserRankingPosition($user_id,$lang,$course_id);
				generateCertificatePdf($user_id,$course_id, $positionRanking, $certification_data);
			}
		}
	} else {
        $message = '';
        if (!$pass_number_of_tandems) {
            $message = $LanguageInstance->get('You cannot get the certificate because you have not completed the minimum of 12 tandems and/or you have not given all the feedbacks required to pass the course.');
        }
        if (!$pass_fill_questionnaire) {
            $message .= (strlen($message)>0?'</div><br><div class="alert alert-danger">':'').
                $LanguageInstance->get('You need to fill in the final survey in order to get the certificate.<br><a href="https://mooc.speakapps.org/mooc/mod/questionnaire/view.php?id=621" class="btn">Click here to go to the survey</a>');
        }
	}
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
</head>
<body>
    <!-- Begin page content -->
    <div id="wrapper" class="container">
      <div class="page-header">
      	<div class='row'>
     		<div class='col-md-6'>
	        	<h1>
	        		<?php echo $LanguageInstance->get(!$certification_data?'Generate your certificate':'Download your certificate') ?></h1>
	    	</div>
       	</div>
	    </div>
      <?php if ($message){
          echo '<div class="row"><div class="col-md-12">';
      	echo '<div class="alert alert-'.$message_alert_class.'" role="alert">'.$message.'</div><br>';
      }
      if ($can_get_certificate){
 ?>
	     <div class='row'>
	     	<div class="col-md-12">
		     	<form method="POST">
		     	<?php if (!$certification_data) {?>
				 	<div class="form-group">
							<label for="fullname" ><?php echo $LanguageInstance->get('Name and surname');?></label>
							<input type="text" name="fullname" required value="<?php echo $fullname?>" />
							<?php echo $LanguageInstance->get(' write your name and surname correctly');?>

						</div>
					<div class="form-group">
							<label for="identifier" ><?php echo $LanguageInstance->get('ID:');?></label>
							<input type="text" name="identifier" value="<?php echo $identifier?>" />
							<?php echo $LanguageInstance->get(' only if you want to see it in the certificate');?>
						</div>
					<input type="hidden" name="certificate_information" value="1" />
					<span class="small"><?php echo $LanguageInstance->get('cannot_be_modified')?></span>
					<button type="submit" id='submit-extra-info' class="btn btn-success"><?php echo $LanguageInstance->get('Save changes');?></button>
				<?php } else { ?>
					<button type="submit" id='submit-extra-info' name="download_certificate" value="1" class="btn btn-success"><?php echo $LanguageInstance->get('Get Certificate');?></button>
				<?php } ?>
				<?php	if ($user_obj->instructor==1){			?>
					<input type="hidden" name="user_id" value="<?php echo $user_id?>" />
					<input type="hidden" name="course_id" value="<?php echo $course_id?>" />
				<?php		} ?>
		     	</form>
		     </div>
		</div>
		<?php } ?>
    </div>

	<?php include_once dirname(__FILE__) . '/js/google_analytics.php' ?>

<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script src="js/validator.min.js"></script>
<script>
$(document).ready(function(){

});
</script>
</body>
</html>
<?php  } ?>
