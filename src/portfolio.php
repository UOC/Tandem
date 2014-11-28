<?php
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';
include(dirname(__FILE__) . '/classes/pdf.php');


$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
//$portfolio = isset($_SESSION[PORTFOLIO]) ? $_SESSION[PORTFOLIO] : false;
require_once dirname(__FILE__) . '/classes/IntegrationTandemBLTI.php';
//si no existeix objecte usuari o no existeix curs redireccionem cap a l'index....preguntar Antoni cap a on redirigir...
	
$gestorBD = new GestorBD();
if (empty($user_obj) || !isset($user_obj->id)) {
//Tornem a l'index
	header('Location: index.php');
	die();
} else {
	

	$dateStart = !empty($_POST['dateStart']) ? $_POST['dateStart'] : date("Y-m-d");
	$dateEnd = !empty($_POST['dateEnd']) ? $_POST['dateEnd'] : date("Y-m-d");

	$finishedTandem = -1;
	if ($user_obj->instructor ==1 && !empty($_POST['finishedTandem'])){
		$finishedTandem = $_POST['finishedTandem'];
	}	

	$showFeedback = -1;
	if (!empty($_POST['showFeedback'])){
		$showFeedback = $_POST['showFeedback'];
	}	
	$selectedUser = 0;
	//lets see if we have a cookie for the selected user
	if(!empty($_COOKIE['selecteduser']) && $user_obj->instructor == 1){
		$selectedUser = $_COOKIE['selecteduser'];
	}	
	if(!empty($_POST['selectUser']) && $user_obj->instructor == 1){
			$selectedUser = (int)$_POST['selectUser'];
			setcookie("selecteduser",$selectedUser);
	}
	//the instructor wants to view some userfeedback
	if(!empty($selectedUser) && $user_obj->instructor == 1){
		$feedbacks = $gestorBD->getAllUserFeedbacks($selectedUser,$course_id, $showFeedback, $finishedTandem, $dateStart, $dateEnd);		
	}else  	{
		$feedbacks = $gestorBD->getAllUserFeedbacks($user_obj->id,$course_id, $showFeedback, $finishedTandem);	
	}
}

//lets check if the user has filled the first profile form.
$firstProfileForm  = $gestorBD->getUserPortfolioProfile("first",$user_obj->id);

//lets save the registration form
if(isset($_POST['extra-info-form'])){
    $inputs  = array("skills_grade","fluency","accuracy","improve_pronunciation","improve_vocabulary","improve_grammar","s2_pronunciation_txt","s2_vowels_txt","s2_consonants_txt","s2_stress_txt","s2_intonation_txt","s2_vocabulary_txt","s2_vocab_txt","s2_false_friends_txt","s2_grammar_txt","s2_verb_agreement_txt","s2_noun_agreement_txt","s2_sentence_txt","s2_connectors_txt","s2_aspects_txt");
	$save = new stdclass();
	foreach($inputs as $in){
		if(!empty($_POST[$in])){
			$save->$in = strip_tags($_POST[$in]);
		}
	}
	$data = serialize($save);
	//first lets make sure they dont already have filled this formulary
	if(!$firstProfileForm){
		$gestorBD->consulta("insert into user_portfolio_profile(user_id,data,type,created) values ('".$user_obj->id."','".mysql_real_escape_string($data)."','first',NOW())");
		$firstProfileForm['data'] = $data;
	}
	//if we have this value then we are updating
	if(!empty($_POST['portfolio_form_id'])){
		$gestorBD->consulta("update user_portfolio_profile set data ='".mysql_real_escape_string($data)."' where id= ".$gestorBD->escapeString($_POST['portfolio_form_id'])." ");
		$firstProfileForm['data'] = $data;
	}
	//Get the previous stored data
	$firstProfileForm  = $gestorBD->getUserPortfolioProfile("first",$user_obj->id);	
}


if($user_obj->instructor == 1 && !empty($_POST['get_pdf'])){ 
	if(!empty($selectedUser)) $userForPdf = $selectedUser; else $userForPdf = $user_obj->id;
	generatePdf($userForPdf,$course_id);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="js/jquery-ui-1.11.2.custom/jquery-ui.min.css">
<link href="css/tandem-waiting-room.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" media="all" href="css/slider.css" />
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
 <script src="js/jquery-ui-1.11.2.custom/jquery-ui.min.js"></script>
<script src="js/bootstrap-slider2.js"></script>
<script>
	$(document).ready(function(){
		$(".viewFeedback").click(function(){
			$this = $(this);
			var feedbackId = $this.data("feedback-id");
			window.location = "feedback.php?id_feedback="+feedbackId;
		})
		$('.alert').tooltip();

		<?php if($user_obj->instructor == 1){ ?>
		$("#selectUser").change(function(){
			$("#selectUserForm").submit();
		});
		$("#finishedTandem").change(function(){
			$("#selectUserForm").submit();
		});
		$("#showFeedback").change(function(){
			$("#selectUserForm").submit();
		});
		<?php } else { ?>
		$("#showFeedback").change(function(){
			$("#showTandemsFeedbackform").submit();
		});
		<?php } ?>
		<?php if(!$firstProfileForm && $user_obj->instructor != 1){ ?>
			  $("#registry-modal-form").modal("show");
		<?php } ?>
		//slider
		$('.slider').slider({min: '0',max : '100'});

		$("#viewProfileForm").click(function(){
			$("#registry-modal-form").modal("show");
		});
        (function( $ ) {
    		$("#dateStart").datepicker({dateFormat: 'yy-mm-dd',altFormat :'dd-mm-yy',firstDay: 1});
            $("#dateEnd").datepicker({dateFormat: 'yy-mm-dd',altFormat :'dd-mm-yy',firstDay: 1});
   		})( jQuery );


	});
</script>
<style>
.container{margin-top:20px;}
</style>
</head>
<body>
<div class="container">
	<div class='row'>
		<div class='col-md-6'>
			<?php if (defined('SHOW_RANKING') && SHOW_RANKING==1) {?>
			<button class="btn btn-success" type='button' onclick="window.location ='ranking.php';"><?php echo $LanguageInstance->get('Go to the ranking') ?></button>
			<?php } ?>
			<?php //if(empty($firstProfileForm)){ ?>
				<button class="btn btn-success" type='button' id='viewProfileForm'><?php echo $LanguageInstance->get('View your profile') ?></button>
			<?php //} ?>
		</div>
		<div class='col-md-6 text-right'>
				<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a>
		</div>
	</div>
  	<div class="row">
	  	<div class='col-md-6'>
	  		<h1 class='title'><?php echo $LanguageInstance->get('My portfolio feedback');?></h1>
	  	</div>
  			<div class='col-md-6 text-right'>
  				<div class='welcomeMessage'>
					<?php 
						$getUserRankingPosition = $gestorBD->getUserRankingPosition($user_obj->id,$_SESSION['lang'],$course_id);			
						$positionInRankingTxt =  $LanguageInstance->get('Hello %1');
						$positionInRankingTxt = str_replace("%1",$gestorBD->getUserName($user_obj->id),$positionInRankingTxt);
						if (defined('SHOW_RANKING') && SHOW_RANKING==1) {

							if($getUserRankingPosition > 0)
								$positionInRankingTxt .= $LanguageInstance->get(', your position in the ranking is ')."<b>".$getUserRankingPosition."</b>";
						}
						
						echo $positionInRankingTxt;			
					?>
				</div>
			</div>
  	</div>
  	<div class='row'>
  		<div class='col-md-12'>
  			<div class="alert alert-info" role="alert"><?php echo $LanguageInstance->get('portfolio_info')?></div>
  		</div>  		
  	</div>  	  	
  	<?php if(!empty($firstProfileForm)){ ?>
   	<div class="row well">  	
   		<div class="col-md-6">
   			<div class="list_group">
	   			<div class="list-group-item">
	   				<?php 
	   				echo $LanguageInstance->get('Grade your speaking skills');
	   				echo ": <strong>".getSkillsLevel($firstProfileForm['data']->skills_grade, $LanguageInstance)."</strong>";
	   				?>
	   			</div> 
	   			<div class="list-group-item">
	   				<?php echo $LanguageInstance->get('Fluency');
	   				$profileFluency = isset($firstProfileForm['data']->fluency) ? $firstProfileForm['data']->fluency : '0'; 
	   					 echo ": <strong>".$profileFluency."%</strong>";
	   				?>
	   			</div> 
	   			<div class="list-group-item">
	   				<?php echo $LanguageInstance->get('Accuracy');
	   				$accuracyProfile = isset($firstProfileForm['data']->accuracy) ? $firstProfileForm['data']->accuracy : '0';
	   					 echo ": <strong>".$accuracyProfile."%</strong>";
	   				?>
	   			</div> 
  			</div>
  			</div>
  			<div class="col-md-6">			
  			<ul class="list_group">
	   			<li class="list-group-item">	   			 
	   			<?php echo $LanguageInstance->get('My pronunciation');
	   			 $myPronunciation = isset($firstProfileForm['data']->improve_pronunciation) ? $firstProfileForm['data']->improve_pronunciation :'';
	   				echo ": <strong>".$myPronunciation."</strong>";
	   			?>
	   			</li> 
	   			<li class="list-group-item">
	   			<?php echo $LanguageInstance->get('My vocabulary');
	   			 $myVocabulary = !empty($firstProfileForm['data']->improve_vocabulary) ? $firstProfileForm['data']->improve_vocabulary : '';
	   				echo ": <strong>". $myVocabulary."</strong>";
	   			?>
	   			</li> 
	   			<li class="list-group-item">
	   			<?php echo $LanguageInstance->get('My grammar');
	   			$myGrammar = !empty($firstProfileForm['data']->improve_grammar)?$firstProfileForm['data']->improve_grammar:'';
	   				echo ": <strong>".$myGrammar."</strong>";
	   			?>
	   			</li> 
  			</ul>
  		</div>
  	</div>
  	<?php } ?>
  	<?php
if($user_obj->instructor == 1 ){ 
 $usersList = $gestorBD->getAllUsers($course_id);
?>
	<div class='row'>		
		<div class='col-md-6'>
			<form action='' method="POST" id='selectUserForm' class="form-inline" role='form'>
				<div class="form-group">
					<select name='selectUser' id="selectUser" class='form-control'>
					<option value='0'><?php echo $LanguageInstance->get('Select user')?></option>

					<option value='-1' <?php echo (isset($selectedUser) && $selectedUser ==-1?'selected':'')?>><?php echo $LanguageInstance->get('All users')?></option>
					<?php
						foreach($usersList as  $u){
							$selected = '';
							if(isset($selectedUser) && $selectedUser == $u['id']) $selected = ' selected="selected"';
							echo "<option value='".$u['id']."' $selected>".$u['fullname']."</option>";
						}					  	
					?>
					</select>
					<span class="help-block"><?php echo $LanguageInstance->get('Select a user to view their portfolio');?></span>
				</div>
				&nbsp;
				<div class="form-group">
					<select name='showFeedback' id="showFeedback" class='form-control'>
					<option value='-1' <?php echo (isset($showFeedback) && $showFeedback ==-1?'selected':'')?>><?php echo $LanguageInstance->get('All Feedbacks')?></option>
					<option value='1' <?php echo (isset($showFeedback) && $showFeedback ==1?'selected':'')?>><?php echo $LanguageInstance->get('Complete')?></option>
					<option value='2' <?php echo (isset($showFeedback) && $showFeedback ==2?'selected':'')?>><?php echo $LanguageInstance->get('Incomplete')?></option>
					</select>
					<span class="help-block"><?php echo $LanguageInstance->get('Show feedback status');?></span>
				</div>				
				&nbsp;
				<div class="form-group">
					<select name='finishedTandem' id="finishedTandem" class='form-control'>
					<option value='-1' <?php echo (isset($finishedTandem) && $finishedTandem ==-1?'selected':'')?>><?php echo $LanguageInstance->get('All')?></option>
					<option value='1' <?php echo (isset($finishedTandem) && $finishedTandem ==1?'selected':'')?>><?php echo $LanguageInstance->get('Finished')?></option>
					<option value='2' <?php echo (isset($finishedTandem) && $finishedTandem ==2?'selected':'')?>><?php echo $LanguageInstance->get('Unfinished')?></option>
					</select>
					<span class="help-block"><?php echo $LanguageInstance->get('Select tandem finished');?></span>
				</div>
                <div class='selectDatesForTandems'>
                    <div class="form-group">                        
                        <label  class="sr-only"> <?php echo $LanguageInstance->get('Start Date');?></label>
                        <p class="form-control-static"> <?php echo $LanguageInstance->get('Start Date');?></p>                           
                    </div>       
                    <div class="form-group">
                        <input type='text' class="form-control"  name='dateStart' id='dateStart' value='<?php echo $dateStart?>'>                         
                    </div>
                   <div class="form-group">                        
                        <label  class="sr-only"> <?php echo $LanguageInstance->get('Start End');?></label>
                        <p class="form-control-static"> <?php echo $LanguageInstance->get('End Date');?></p>                           
                    </div>       
                    <div class="form-group">
                        <input type='text' class="form-control"  name='dateEnd' id='dateEnd' value='<?php echo $dateEnd?>'>                         
                    </div>
                        <button type="submit" class="btn btn-default"><?php echo $LanguageInstance->get('View');?></button>
                </div>        
			</form>
		</div>
		<div class='col-md-6 text-right' >
			<div class="row">
				<div class='col-md-8  text-right' >
					<form action='' method='POST' role='form' id='pdfForm'>
						<input type='hidden'  name='get_pdf' value='1' />
						<input type='submit' value='<?php echo $LanguageInstance->get('Download a PDF file with all Tandems');?>' class='btn btn-success' />
					</form> 
				</div>
				<div class='col-md-4' >	
					<form action='portfolio_excel.php' method='POST' role='form' id='excelForm'>
						<input type='hidden'  name='showFeedback' value='<?php echo $showFeedback?>' />
						<input type='hidden'  name='finishedTandem' value='<?php echo $finishedTandem?>' />
						<input type='hidden'  name='selectUser' value='<?php echo $selectedUser?>' />
						<input type='hidden'  name='dateStart' value='<?php echo $dateStart?>' />
						<input type='submit' value='<?php echo $LanguageInstance->get('Export to excel');?>' class='btn btn-success' />
					</form> 
				</div>	
			</div>	
		</div>
	</div>
<?php } else {?>
<div class='row'>		
		<div class='col-md-6'>
			<p>
				<form action='' method="POST" id='showTandemsFeedbackform' class="form-inline" role='form'>
				<div class="form-group">
					<select name='showFeedback' id="showFeedback" class='form-control'>
					<option value='-1' <?php echo (isset($showFeedback) && $showFeedback ==1?'selected':'')?>><?php echo $LanguageInstance->get('All Feedbacks')?></option>
					<option value='1' <?php echo (isset($showFeedback) && $showFeedback ==1?'selected':'')?>><?php echo $LanguageInstance->get('Complete')?></option>
					<option value='2' <?php echo (isset($showFeedback) && $showFeedback ==2?'selected':'')?>><?php echo $LanguageInstance->get('Incomplete')?></option>
					</select>
					<span class="help-block"><?php echo $LanguageInstance->get('Show feedback status');?></span>
				</div>
				</form>
			</p>
		</div>
	</div>
<?php } 
$number = 1;?>
<div class='row'>
  <div class="col-md-12">
  	<table class="table">
  	<tr>
  	<th></th>
  	<?php if ($user_obj->instructor == 1 ){ ?>
	<th><?php echo $LanguageInstance->get('Name');?></th>
  	<?php } ?>
  	<th><?php echo $LanguageInstance->get('Overall rating');?></th>
  	<th><?php echo $LanguageInstance->get('exercise');?></th>
  	<th><?php echo $LanguageInstance->get('Created');?></th>
  	<th><?php echo $LanguageInstance->get('Total Duration');?></th>
  	<th><?php echo $LanguageInstance->get('Duration per task');?></th>
  	<th><?php echo $LanguageInstance->get('Actions');?></th>
  	</tr>
 	<?php
	  if(!empty($feedbacks)){
	  	foreach($feedbacks as $f){
	  	//we have all the tasks total_time in an array, but we need the T1=00:00 format.
	  	$tt = array();
	  	foreach($f['total_time_tasks'] as $key => $val){
	  		$tt[] = "T".++$key." = ".$val;
	  	}
	  	$tr ="";
	  	if(empty($f['feedback_form'])){
	  		$tr = 'title ="'.$LanguageInstance->get('Insert your feedback').'" class="alert alert-danger" data-placement="top" data-toggle="tooltip" ';
	  	}
	  	echo "<tr $tr>";
	  	echo "<td>".($number++)."</td>";
  		if ($user_obj->instructor == 1 ){ 
			echo "<td>".$f['fullname']."</th>";
  		} 
	  	echo "<td class='text-center'>".getSkillsLevel($f['overall_grade'], $LanguageInstance)."</td>
	  		  <td>".$f['exercise']."</td>
	  		  <td>".$f['created']."</td>
  			  <td>".$f['total_time']."</td>
  			  <td style='font-size:10px'>".implode("<br />",$tt)."</td>
  			  <td><button data-feedback-id='".$f['id']."' class='btn btn-success btn-sm viewFeedback' >".$LanguageInstance->get('View')."</button></td>
  		  </tr>";	
	  	}
	  }
  	?>
  </table>
  </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade bs-example-modal-lg" id="registry-modal-form" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo $LanguageInstance->get('Personal profile');?></h4>
      </div>
      <div class="modal-body">
     <!-- EXTRA INFO FORM  -->
	<form  id='extra-info' role="form" method="POST">	
	 <div class="form-group">
   		<label for="input1" ><?php echo $LanguageInstance->get('Grade your speaking skills');?></label>   	 	
      		<select name='skills_grade' class="form-control">  
      			<option><?php echo $LanguageInstance->get('Select one')?></option>
						  		<option value="A" <?php echo (isset($firstProfileForm['data']->skills_grade) && $firstProfileForm['data']->skills_grade=='A')?'selected':''?>><?php echo $LanguageInstance->get('Excellent')?></option>
						  		<option value="B" <?php echo (isset($firstProfileForm['data']->skills_grade) && $firstProfileForm['data']->skills_grade=='B')?'selected':''?>><?php echo $LanguageInstance->get('Very Good')?></option>
						  		<option value="C" <?php echo (isset($firstProfileForm['data']->skills_grade) && $firstProfileForm['data']->skills_grade=='C')?'selected':''?>><?php echo $LanguageInstance->get('Good')?></option>
						  		<option value="D" <?php echo (isset($firstProfileForm['data']->skills_grade) && $firstProfileForm['data']->skills_grade=='D')?'selected':''?>><?php echo $LanguageInstance->get('Pass')?></option>
      		</select>    	
  	</div>
  	<h4><?php echo $LanguageInstance->get('During the course I want to improve my');?></h4>
  	  	<div class="form-group">
	   		<label for="input2" >
	   			<?php echo $LanguageInstance->get('Fluency');?>&nbsp;&nbsp;
	   		</label>
	   		<input type='text' name='fluency' class='slider'  data-slider-id="ex1Slider" data-slider-value='<?php echo isset($firstProfileForm['data']->fluency) ? $firstProfileForm['data']->fluency : '' ?>' /> %
  		</div>	
  		<div class="form-group">
	   		<label for="input2">
	   			<?php echo $LanguageInstance->get('Accuracy');?>&nbsp;&nbsp;
	   		</label>
	   		<input type='text' name='accuracy' class='slider' data-slider-id="ex2Slider" value="" data-slider-value='<?php echo isset($firstProfileForm['data']->accuracy) ? $firstProfileForm['data']->accuracy : '' ?>' data-slider-min="0" data-slider-max="5" data-slider-step="1"  data-slider-orientation="horizontal"  /> %
  		</div> 
  	<h4><?php echo $LanguageInstance->get('During the course I also want to improve');?></h4>
	  	<div class="form-group">
	   		<label for="input2">
	   			<?php echo $LanguageInstance->get('My pronunciation');?>
	   		</label>	   	
	 		<textarea name='improve_pronunciation' class="form-control" rows="3"><?php echo isset($firstProfileForm['data']->improve_pronunciation) ? $firstProfileForm['data']->improve_pronunciation : '' ?></textarea>	    
	  	</div>  	
	  	<div class="form-group">
	   		<label for="input3" >
	   			<?php echo $LanguageInstance->get('My vocabulary');?>
	   		</label>	   	 	
	 			<textarea name='improve_vocabulary' class="form-control" rows="3"><?php echo isset($firstProfileForm['data']->improve_vocabulary) ? $firstProfileForm['data']->improve_vocabulary : '' ?></textarea>	    	
	  	</div>  
	  	<div class="form-group">
	   		<label for="input4" >
	   			<?php echo $LanguageInstance->get('My grammar');?>
	   		</label>	   	 	
	 			<textarea name='improve_grammar' class="form-control" rows="3"><?php echo isset($firstProfileForm['data']->improve_grammar) ? $firstProfileForm['data']->improve_grammar : '' ?></textarea>	    	
	  	</div>  
		<input type='hidden' name='extra-info-form' value='1' />
		<?php 
		if(!empty($firstProfileForm['id'])){
			echo "<input type='hidden' name='portfolio_form_id' value='".$firstProfileForm['id']."' />";
		}
		?>
	</form>
      </div>
      <div class="modal-footer">
      <span class="small"><?php echo $LanguageInstance->get('cannot_be_modified')?></span>
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $LanguageInstance->get('Close');?></button>
        <button type="button" id='submit-extra-info' class="btn btn-success"><?php echo $LanguageInstance->get('Save changes');?></button>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function(){
	$("#extra-info input[type=checkbox]").change(function(){
		
		$textarea = $(this).parent().next("textarea");
		$textarea.toggleClass("hide");
		$textarea.val("");
	});

	$("#submit-extra-info").click(function(){
		$("#extra-info").submit();
	});

	//find all checked checkboxes and open the textarea
	$("#extra-info input[type=checkbox]:checked").each(function(){
		$textarea = $(this).parent().next("textarea");
		$textarea.toggleClass("hide");
	})
})
</script>
</body>
</html>