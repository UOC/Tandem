<?php
require_once dirname(__FILE__) . '/classes/lang.php';
$select_room = isset($_GET['select_room']) && $_GET['select_room'] == 1;
$goto = 'autoAssignTandemRoom';
if ($select_room) {
	$goto = 'selectUserAndRoom';
}
if (isset($_GET['lang']) && isset($_GET['force']) && $_GET['force']) {
	$_SESSION[LANG] = $_GET['lang'];
	
	header('Location: '.$goto.'.php');
	die();
}

require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';
include(dirname(__FILE__) . '/classes/pdf.php');
require_once dirname(__FILE__) . '/classes/IntegrationTandemBLTI.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
	
$gestorBD = new GestorBD();
if (empty($user_obj) || $user_obj->instructor != 1  ) {
	header('Location: index.php');
	die();
} 

$currentActiveTandems = $gestorBD->currentActiveTandems($course_id);
$getUsersWaitingEs = $gestorBD->getUsersWaitingByLanguage($course_id,"es_ES");
$getUsersWaitingEn = $gestorBD->getUsersWaitingByLanguage($course_id,"en_US");

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<link href="css/tandem-waiting-room.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" media="all" href="css/slider.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="js/bootstrap-slider2.js"></script>
<style >
	.container{ margin-top:20px;}
</style>
<script>
		$(document).ready(function(){
	        var interval = setInterval(function(){
	        	$.ajax({
	        		type: 'POST',
	        		url: "getCurrentUserCount.php",
	        		data : {
	        		},
	        		dataType: "JSON",
	        		success: function(json){	        			
	        			if(json  &&  typeof json.users_en !== "undefined" &&  typeof json.users_es !== "undefined"){
	        				$('#UsersWaitingEn').html(json.users_en);
	        				$('#UsersWaitingEs').html(json.users_es);
	        			}
	        		}
	        	});
	        },2500);
		});
</script>
</head>
<body>
<div class='container'>
	<div class='row'>		
		<div class='col-md-12 text-right'>
				<p><a href="#" title="<?php echo $LanguageInstance->get('tandem_logo')?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo')?>" /></a></p>
		</div>		
	</div>
	<div class='row'>
	   	<div class="col-md-6">
   			<div class="list_group">
	   			<div class="list-group-item">
	   			<?php
	   			 	echo $LanguageInstance->get('Current active tandems');
	   			 	echo ": <strong>".$currentActiveTandems."</strong>";
	   			?>
	   			</div> 
	   			<div class="list-group-item">
	   			<?php
	   			 	echo $LanguageInstance->get('Users waiting to practise English');
	   			 	echo ": <strong><span id=\"UsersWaitingEn\">".$getUsersWaitingEn."</span></strong>";
	   			?>
	   			</div> 
	   			<div class="list-group-item">
	   			<?php
	   			 	echo $LanguageInstance->get('Usuarios esperando para practicar Español');
	   			 	echo ": <strong><span id=\"UsersWaitingEn\">".$getUsersWaitingEs."</span></strong>";
	   			?>	   				   				
	   			</div> 
  			</div>
  		</div>	   	
  		<div class="col-md-6">
   			<?php /*<div class="list_group">
	   			<div class="list-group-item">
	   			<?php
	   			 	echo $LanguageInstance->get('Number of people that have done a tandem on specific date');
	   			 	echo ": <div class='tandemByDate'>".$tandemByDate."</div>";
	   			?>
	   			</div> 	   		
  			</div> */?>
  		</div>
  	</div>
  	<p></p>
	<div class='row'>
		<div class='col-md-12'>
		<p>
			 <a class='btn btn-success' href='manage_exercises_tandem.php'><?php echo $LanguageInstance->get("mange_exercises_tandem");?></a>
             <a href='statistics_tandem.php' class='btn btn-success' ><?php echo $LanguageInstance->get("Tandem Statistics");?></a> 
             <a href='tandemInfo.php?force=1&lang=en_US<?php echo $select_room?'&select_room=1':''?>' class='btn btn-success' ><?php echo $LanguageInstance->get("Go to tandem to practise English");?></a> 
             <a href='tandemInfo.php?force=1&lang=es_ES<?php echo $select_room?'&select_room=1':''?>' class='btn btn-success' ><?php echo $LanguageInstance->get("Ir al tandem para practicar Español");?></a> 
		</p>
		</div>
	</div>
</div>
</body>
</html>











