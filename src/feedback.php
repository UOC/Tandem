<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


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
} else {
	require_once(dirname(__FILE__) . '/classes/constants.php');
	$id_feedback = isset($_GET['id_feedback']) && $_GET['id_feedback']>0?$_GET['id_feedback']:$_SESSION[ID_FEEDBACK];

	if (!$id_feedback ) {
		die($LanguageInstance->get('Missing feedback parameter'));
	}

	$gestorBD = new GestorBD();  

	if(!empty($_POST['rating_partner'])){			
		$rating_partner_feedback_form = new stdClass();		
		$rating_partner_feedback_form->partner_rate = isset($_POST['partner_rate'])?$_POST['partner_rate']:0;
		$rating_partner_feedback_form->partner_comment = isset($_POST['partner_comment'])?$_POST['partner_comment']:'';
		$gestorBD->updateRatingPartnerFeedbackTandemDetail($id_feedback, $rating_partner_feedback_form);
	}

	$feedbackDetails = $gestorBD->getFeedbackDetails($id_feedback);
	if (!$feedbackDetails) {
		die($LanguageInstance->get('Can not find feedback'));
	}
	
	if ($user_obj->id!=$feedbackDetails->id_user && $user_obj->id!=$feedbackDetails->id_partner &&
		!$user_obj->instructor && !$user_obj->admin) { //check if is the user of feedback if not can't not set feedback
		die($LanguageInstance->get('no estas autoritzat'));
	}
	  
	$message= false;
	$can_edit = true;

	$feedback_form = new stdClass();
	$feedback_form->fluency = 0;
	$feedback_form->accuracy = 0;
	$feedback_form->grade = "";
	$feedback_form->pronunciation = "";
	$feedback_form->vocabulary = "";
	$feedback_form->grammar = "";
	$feedback_form->other_observations = "";
	if ($feedbackDetails->feedback_form) { //if it is false can edit
		$can_edit = false;
		$message = '<div class="alert alert-info" role="alert">'.$LanguageInstance->get('The information is stored you can only review it').'</div>';
		$feedback_form = $feedbackDetails->feedback_form;
	} else{
		if ($user_obj->id==$feedbackDetails->id_user) { //check if is the user of feedback if not can't not set feedback
			if (!empty($_POST['save_feedback'])) {
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

						//ok so they have created filled the feedback , so now we can save the ranking data for this tandem.
						$gestorBD->insertRankingData($user_obj->id,$course_id,$_SESSION['lang'],$feedbackDetails->id_tandem);
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
		<style>
		#footer-container{margin-top:-222px;position:inherit;}
		#wrapper{padding:0px 58px}
		</style>
</head>
<body>
    <!-- Begin page content -->
    <div id="wrapper" class="container">
      <div class="page-header">
      <button class="btn btn-success" type='button' onclick="window.location ='portfolio.php';"><?php echo $LanguageInstance->get('Back to list') ?></button>
        <h1><?php echo $LanguageInstance->get('peer_review_form') ?></h1>
      </div>
      <?php if ($message){
      	echo $message;
      }?>
      <p>
	   	<button id="viewVideo" onclick="window.open('ltiConsumer.php?id=100&<?php echo $feedbackDetails->id_external_tool>0? (ID_EXTERNAL.'='. $feedbackDetails->id_external_tool):''?>&<?php echo $feedbackDetails->id_tandem>0? (CURRENT_TANDEM.'='. $feedbackDetails->id_tandem):''?>')" type="button" class="btn btn-success"><?php echo $LanguageInstance->get('View video session') ?></button>
	   </p>
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
						      <textarea rows="3" cols="200" class="form-control" id="partner_comment"  name="partner_comment" placeholder="<?php echo $LanguageInstance->get('Comments')?>"  <?php if(!empty($feedbackDetails->rating_partner_feedback_form->partner_comment)){ echo "readonly";} ?> ><?php if(!empty($feedbackDetails->rating_partner_feedback_form->partner_comment)){ echo $feedbackDetails->rating_partner_feedback_form->partner_comment;}?></textarea>
						    </div>
						  </div>
						   <input type='hidden' name='rating_partner' value='1' />
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
						  <div class="form-group">
						    <label for="pronunciation" class="control-label"><?php echo $LanguageInstance->get('Pronunciation')?></label>
						    <div class="input-group">
						      <textarea <?php echo (!$can_edit) ? "readonly" : "" ?> rows="3" cols="200" class="form-control" id="pronunciation" name='pronunciation' placeholder="<?php echo $LanguageInstance->get('Indicate the level of pronunciation')?>" ><?php echo $feedback_form->pronunciation?></textarea>
						    </div>
						  </div>
						  <div class="form-group">
						    <label for="vocabulary" class="control-label"><?php echo $LanguageInstance->get('Vocabulary')?></label>
						    <div class="input-group">
						      <textarea <?php echo (!$can_edit) ? "readonly" : "" ?> rows="3" cols="200" class="form-control" id="vocabulary"  name='vocabulary' placeholder="<?php echo $LanguageInstance->get('Indicate the level of vocabulary')?>" ><?php echo $feedback_form->vocabulary?></textarea>
						    </div>
						  </div>
						  <div class="form-group">
						    <label for="grammar" class="control-label"><?php echo $LanguageInstance->get('Grammar')?></label>
						    <div class="input-group">
						      <textarea <?php echo (!$can_edit) ? "readonly" : "" ?> rows="3" cols="200" class="form-control" id="grammar"  name="grammar" placeholder="<?php echo $LanguageInstance->get('Indicate the level of grammar')?>" ><?php echo $feedback_form->grammar?></textarea>
						    </div>
						  </div>
						  <div class="form-group">
						    <label for="other_observations" class="control-label"><?php echo $LanguageInstance->get('Other Observations')?></label>
						    <div class="input-group">
						      <textarea <?php echo (!$can_edit) ? "readonly" : "" ?> rows="3" cols="200" class="form-control" id="other_observations" name="other_observations" placeholder="<?php echo $LanguageInstance->get('Indicate other observations')?>"><?php echo $feedback_form->other_observations?></textarea>
						    </div>
						  </div>
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
						</form>
				<!-- /content -->
			</div>
			<!-- /main -->
		</div>
		</div>
		</div>
		<!-- /main-container -->
	</div>
    </div>
	
	<?php include_once dirname(__FILE__) . '/js/google_analytics.php' ?>

<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script src="js/validator.min.js"></script>
<script src="js/bootstrap-slider2.js"></script>
<script src="js/star-rating.min.js"></script>
<script>
$('.sliderTandem').slider({
	formatter: function(value) {
		return 'Current value: ' + value;
	}
});
$(document).ready(function(){
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
} );		
</script>   
</body>
</html>
<?php  } ?>