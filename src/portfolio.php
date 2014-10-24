<?php
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';


$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
//$portfolio = isset($_SESSION[PORTFOLIO]) ? $_SESSION[PORTFOLIO] : false;
require_once dirname(__FILE__) . '/classes/IntegrationTandemBLTI.php';
//si no existeix objecte usuari o no existeix curs redireccionem cap a l'index....preguntar Antoni cap a on redirigir...

if (!$user_obj) {
//Tornem a l'index
	header('Location: index.php');
} else {
	require_once(dirname(__FILE__) . '/classes/constants.php');	
	$gestorBD = new GestorBD();

	
	if(!empty($_POST['selectUser']) && $user_obj->instructor == 1){
			$selectedUser = (int)$_POST['selectUser'];
	}
	//the instructor wants to view some userfeedback
	if(!empty($selectedUser)){
		$feedbacks = $gestorBD->getAllUserFeedbacks($selectedUser);		
	}else  	
		$feedbacks = $gestorBD->getAllUserFeedbacks($user_obj->id);	


}



?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<link href="css/tandem-waiting-room.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script>
	$(document).ready(function(){
		$(".viewFeedback").click(function(){
			$this = $(this);
			var feedbackId = $this.data("feedback-id");
			window.location = "feedback.php?id_feedback="+feedbackId;
		})
		$('.alert').tooltip();

		$("#selectUser").change(function(){
			$("#selectUserForm").submit();
		})
	});
</script>
<style>
.container{margin-top:20px;}
</style>
</head>
<body>
<div class="container">
	<div class='row'>
		<div class='col-md-12'>
			<button class="btn btn-success" type='button' onclick="window.location ='ranking.php';"><?php echo $LanguageInstance->get('Go to the ranking') ?></button>
				<?php 
					$getUserRankingPosition = $gestorBD->getUserRankingPosition($user_obj->id);			
					$positionInRankingTxt =  $LanguageInstance->get('Hello %1, your position in the ranking is ');
					$positionInRankingTxt = str_replace("%1",$gestorBD->getUserName($user_obj->id),$positionInRankingTxt);
					echo $positionInRankingTxt."<b>".$getUserRankingPosition."</b>";			
				?>
		</div>
	</div>
  	<div class="row">
	  	<div class='col-md-6'>
	  		<h1 class='title'><?php echo $LanguageInstance->get('My portfolio feedback');?></h1>
	  	</div>
	  	<div class='col-md-6'>
		  	<p class='text-right'>
				<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a>					
		  	</p>
	  	</div>
  	</div>
<?php 
if($user_obj->instructor == 1){ 
 $usersList = $gestorBD->getAllUsers();
?>
	<div class='row'>		
		<div class='col-md-12'>
			<p>
				<form action='' method="POST" id='selectUserForm' class="form-inline" role='form'>
				<div class="form-group">
					<select name='selectUser' id="selectUser" class='form-control'>
					<option value='0'>Select user</option>
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
				</form>
			</p>
		</div>
	</div>
<?php } ?>
<div class='row'>
  <div class="col-md-12">
  	<table class="table">
  	<tr>
  	<th><?php echo $LanguageInstance->get('Overall rating');?></th>
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
	  	echo "<tr $tr><td class='text-center'>".$f['overall_grade']."</td>
	  			  <td>".$f['created']."</td>
	  			  <td>".$f['total_time']."</td>
	  			  <td style='font-size:10px'>".implode("<br />",$tt)."</td>
	  			  <td><button data-feedback-id='".$f['id']."' class='btn btn-success btn-sm viewFeedback' >View</button></td>
	  		  </tr>";	
	  	}
	  }
  	?>
  </table>
  </div>
  </div>
</div>
</body>
</html>