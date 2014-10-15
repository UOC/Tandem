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
	$id_feedback = $_SESSION[ID_FEEDBACK];
	$gestorBD = new GestorBD();    
	$error= false;
	
	if ($_POST) {
		//try to save it!
		if (isset($_POST['grade']) && strlen($_POST['grade'])>0 &&
			isset($_POST['pronunciation']) && strlen($_POST['pronunciation'])>0 &&
			isset($_POST['vocabulary']) && strlen($_POST['vocabulary'])>0 &&
			isset($_POST['grammar']) && strlen($_POST['grammar'])>0){
			$feedback_form = stdClass();
			$feedback_form->fluency = $_POST['fluency'];
			$feedback_form->accuracy = $_POST['accuracy'];
			$feedback_form->grade = $_POST['grade'];
			$feedback_form->pronunciation = $_POST['pronunciation'];
			$feedback_form->vocabulary = $_POST['vocabulary'];
			$feedback_form->grammar = $_POST['grammar'];
			$feedback_form->other_observations = $_POST['other_observations'];

			if ($gestorBD->createFeedbackTandemDetail($id_feedback, serialize($feedback_form))) {
				echo "saved"; //todo go to the list
			}
		} else {
			$error = '<div class="alert alert-danger" role="alert">'.$LanguageInstance->get('Fill all required params').'</div>';
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
		<link rel="stylesheet" type="text/css" media="all" href="css/slider.css" />
			
</head>
<body>

<div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Project name</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
                <li><a href="#">Action</a></li>
                <li><a href="#">Another action</a></li>
                <li><a href="#">Something else here</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Nav header</li>
                <li><a href="#">Separated link</a></li>
                <li><a href="#">One more separated link</a></li>
              </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <!-- Begin page content -->
    <div id="wrapper" class="container">
      <div class="page-header">
        <h1><?php echo $LanguageInstance->get('General Evaluation / General Impression') ?></h1>
      </div>
      <?php if ($error){
      	echo $error;
      }?>
		<div id="main-container_old">
					<!-- main -->
					<div id="main_old">
						<!-- content -->
						<div id="content_old">
		
					
						<form data-toggle="validator" role="form" method="POST">
						  <div class="form-group">
						    <label for="fluency" class="control-label"><?php echo $LanguageInstance->get('Fluency') ?></label>
						  	<input data-slider-id='ex1Slider' class="sliderTandem" name="fluency" id="fluency" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="0"/>%
						  </div>
						  <div class="form-group">
						    <label for="accuracy" class="control-label"><?php echo $LanguageInstance->get('Accuracy') ?></label>
						  	<input data-slider-id='ex2Slider' class="sliderTandem" name="accuracy" id="accuracy" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="0"/>%
						  </div>
						  <div class="form-group">
						    <label for="grade" class="control-label"><?php echo $LanguageInstance->get('Overall Grade:') ?></label>
						  	<select id="grade" name="grade" required>
						  		<option><?php echo $LanguageInstance->get('Select one')?></option>
						  		<option value="A"><?php echo $LanguageInstance->get('Excellent')?></option>
						  		<option value="B"><?php echo $LanguageInstance->get('Very Good')?></option>
						  		<option value="C"><?php echo $LanguageInstance->get('Good')?></option>
						  		<option value="D"><?php echo $LanguageInstance->get('Pass')?></option>
						  		<option value="F"><?php echo $LanguageInstance->get('Fail')?></option>
						  	</select>
						  </div>
						  <div class="row"><h3><?php echo $LanguageInstance->get('Room for improvement')?></h3></div>

						  <div class="form-group">
						    <label for="pronunciation" class="control-label"><?php echo $LanguageInstance->get('Pronunciation')?></label>
						    <div class="input-group">
						      <textarea rows="3" cols="200" class="form-control" id="pronunciation" placeholder="<?php echo $LanguageInstance->get('Indicate the level of pronunciation')?>" required></textarea>
						    </div>
						  </div>
						  <div class="form-group">
						    <label for="vocabulary" class="control-label"><?php echo $LanguageInstance->get('Vocabulary')?></label>
						    <div class="input-group">
						      <textarea rows="3" cols="200" class="form-control" id="vocabulary" placeholder="<?php echo $LanguageInstance->get('Indicate the level of vocabulary')?>" required></textarea>
						    </div>
						  </div>
						  <div class="form-group">
						    <label for="grammar" class="control-label"><?php echo $LanguageInstance->get('Grammar')?></label>
						    <div class="input-group">
						      <textarea rows="3" cols="200" class="form-control" id="grammar" placeholder="<?php echo $LanguageInstance->get('Indicate the level of grammar')?>" required></textarea>
						    </div>
						  </div>
						  <div class="form-group">
						    <label for="other_observations" class="control-label"><?php echo $LanguageInstance->get('Other Observations')?></label>
						    <div class="input-group">
						      <textarea rows="3" cols="200" class="form-control" id="other_observations" placeholder="<?php echo $LanguageInstance->get('Indicate Other Observations')?>"></textarea>
						    </div>
						  </div>
						  <div class="form-group">
						    <button type="submit" class="btn btn-primary"><?php echo $LanguageInstance->get('Send')?></button>
						  </div>
						  <input type="submit" name="id" value="<?php echo $id_feedback?>" />
						</form>
				<!-- /content -->
			</div>
			<!-- /main -->
		</div>
		<!-- /main-container -->
	</div>


    </div>

    <div class="footer">
      <div class="container">
			<div id="logo">
				<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo') ?>" /></a>
			</div>
			<div id="footer-container">
				<div id="footer">
					<div class="footer-tandem" title="<?php echo $LanguageInstance->get('tandem') ?>"></div>
					<div class="footer-logos">
						<img src="css/images/logo_LLP.png" alt="Lifelong Learning Programme" />
						<img src="css/images/logo_EAC.png" alt="Education, Audiovisual &amp; Culture" />
						<img src="css/images/logo_speakapps.png" alt="Speakapps" />
					</div>
				</div>
			</div>

      </div>
    </div>
	
	<!-- /footer -->
	<?php include_once dirname(__FILE__) . '/js/google_analytics.php' ?>

<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script src="js/validator.min.js"></script>
<script src="js/bootstrap-slider.js"></script>
<script>
		//$( document ).ready(function() {
			//$('.slider').slider()
			// With JQuery
			$('.sliderTandem').slider({
				formatter: function(value) {
					return 'Current value: ' + value;
				}
			});
		//});
    	</script>   
</body>
</html>
<?php } ?>