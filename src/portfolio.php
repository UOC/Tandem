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
	$feedbacks = $gestorBD->getAllUserFeedbacks($user_obj->id);

}
?>
<!DOCTYPE html>
<html>
<head>
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script>
	$(document).ready(function(){
		$(".viewFeedback").click(function(){
			$this = $(this);
			var feedbackId = $this.data("feedback-id");
			window.location = "feedback.php?id_feedback="+feedbackId;
		})
	});
</script>
</head>
<body>
<div class="container" style='margin-top:20px'>
  <div class="row">
  	<div class='col-md-6'>
  	<h2 class='title'><?php echo $LanguageInstance->get('My feedbacks portfolio');?></h2>
  	</div>
  	<div class='col-md-6'>
  	<p class='text-right'>
		<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a>					
  	</p>
  	</div>
  <div class="col-md-12">
  	<table class="table table-striped">
  	<tr>
  	<th><?php echo $LanguageInstance->get('My language');?></th>
  	<th><?php echo $LanguageInstance->get('Partner Language');?></th>
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
	  	echo "<tr><td>".substr($f['language'],0,-3)."</td>
	  			  <td>".substr($f['partner_language'],0,-3)."</td>
	  			  <td>".$f['created']."</td>
	  			  <td >".$f['total_time']."</td>
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