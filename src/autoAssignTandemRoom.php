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
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set("display_errors",1);

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
	$path = '';
	if (isset($_SESSION[TANDEM_COURSE_FOLDER]))
		$path = $_SESSION[TANDEM_COURSE_FOLDER] . '/';

	$id_resource_lti = $_SESSION[ID_RESOURCE];
	$lti_context = unserialize($_SESSION[LTI_CONTEXT]);
	$user_language = $_SESSION[LANG];//!empty($_REQUEST['locale']) ? $_REQUEST['locale'] : "es_ES";
	$other_language = ($user_language == "es_ES") ? "en_US" : "es_ES";
	$gestorBD = new GestorBD();    
	$last_id = $gestorBD->get_lastid_invited_to_join($user_obj->id, $id_resource_lti, $course_id);
	$exercisesNotDone = $gestorBD->getExercicesNotDoneWeek($course_id,$user_obj->id); 
	
	//abertranb not need it mange all using the autoAssingCheckTandem.php
	/*
    //Ok we have the exercises the user has not done this week. Lets find someone waiting to do that exercise if not we offer it.
	$areThereTandems = $gestorBD->checkIfAvailableTandemForExercise($exercisesNotDone,$course_id,$user_language,$user_obj->id,$other_language);

	//lets see first if we have people waiting in the waiting room that matches our exercises. 
	if(!empty($areThereTandems)){
		//ok so we are here cause we have someone waiting for one of our exercices.
	die("22".serialize($areThereTandems));
		$tId = $gestorBD->createTandemFromWaiting($areThereTandems[0],$user_obj->id,$id_resource_lti);
		header("location: accessTandem.php?id=".$tId."&not_init=1");
		die();
	}*/
	

	?>                    
	<!DOCTYPE html>
	<html>
	<head>
		<title>Tandem</title>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" media="all" href="css/autoAssignTandem.css" />
		<link rel="stylesheet" type="text/css" media="all" href="css/tandem-waiting-room.css" />
		<link rel="stylesheet" type="text/css" media="all" href="css/defaultInit.css" />
		<link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui.css" />
		<!-- 10082012: nfinney> ADDED COLORBOX CSS LINK -->
		<link rel="stylesheet" type="text/css" media="all" href="css/colorbox.css" />
		<!-- END -->
		<!-- Timer End -->
		<script src="js/jquery-1.7.2.min.js"></script>
		<script src="js/jquery.ui.core.js"></script>
		<script src="js/jquery.ui.widget.js"></script>
		<script src="js/jquery.ui.button.js"></script>
		<script src="js/jquery.ui.position.js"></script>
		<script src="js/jquery.ui.autocomplete.js"></script>
		<script src="js/jquery.colorbox-min.js"></script>
		<script src="js/common.js"></script>
		<!-- Timer Start!! -->
		<script src="js/loadUserData.js"></script>
		<script type="text/javascript" src="js/jquery.animate-colors.min.js"></script>
		<script type="text/javascript" src="js/jquery.simplemodal.1.4.2.min.js"></script>
		<script type="text/javascript" src="js/jquery.iframe-auto-height.plugin.1.7.1.min.js"></script>
		<script type="text/javascript" src="js/jquery.infotip.min.js"></script>
		<script type="text/javascript" src="js/jquery.timeline-clock.min.js"></script>
		<script src="js/jquery.ui.progressbar.js"></script> 
		<script>

		$(document).ready(function(){
	        interval = setInterval(function(){
	        	$.ajax({
	        		type: 'POST',
	        		url: "autoAssignCheckTandems.php",
	        		data : {
	        			   exercisesID : '<?php echo implode(",",$exercisesNotDone);?>',
	        			   otherlanguage    : '<?php echo $other_language;?>',
	        			   courseID   : '<?php echo $course_id;?>',
	        			   user_id    : '<?php echo $user_obj->id;?>',
	        			   id_resource_lti : "<?php echo $id_resource_lti;?>"
	        		},
	        		dataType: "JSON",
	        		success: function(json){	        			
	        			if(json !== null){
	        			window.location.replace("accessTandem.php?id="+json.tandem_id+"&not_init=1");                             
						clearInterval(interval);
	        			}
	        		}
	        	});
	        },2500);
	        <?php 
	        /****** MANAGING TIME BAR ****/ 
	       ?>
            StartTandemTimer = function(){
                $("#timeline").show("fast");
                var minutos = 30;
                var segundos = 0;
                timerOn(minutos,segundos);
                timeline.start();
	        }
			var lwidth = $('#timeline').outerWidth() - ($('#timeline .lbl').outerWidth() + $('#timeline .clock').outerWidth()) + 5;
			var lmargin = $('#timeline .lbl').outerWidth() - 5;
			$('#timeline .linewrap').css({'width': lwidth + 'px', 'margin-left' : lmargin + 'px'});
			var timeline;
			timerOn = function(minutos,segundos){
				// Configuración timeline
		               
				timeline = $('#timeline').timeLineClock({
					time: {hh:0,mm:parseInt(minutos),ss:parseInt(segundos)},
					onEnd: theEnd
				}); 
			}    
			function theEnd(){
				if ($("#modal-end-task").length > 0){
					//TODO put modal
					$.modal($('#modal-end-task'));
					//accionTimer();
				}
			}        
	         StartTandemTimer();	
	        <?php 
	        /****** END MANAGING TIME BAR ****/ 
	       ?>

		});
    </script>   	
</head>
<body>
	<!-- accessibility -->
	<div id="accessibility">
		<a href="#content" accesskey="s" title="Acceso directo al contenido"><?php echo $LanguageInstance->get('direct_access_to_content') ?></a> 
	</div>
	<!-- /accessibility -->
	<!-- /wrapper -->
	<div id="wrapper">
		<!-- main-container -->
		<div id="main-container">
			<!-- main -->
			<div id="main">
				<!-- content -->
				<div id="content">
					<span class="welcome"><?php echo $LanguageInstance->get('welcome') ?> <?php echo $user_obj->fullname; ?>!</span>                             
					<!-- *********************************** -->   
					<!-- ****WAITING-TANDEM-ROOM-dynamic**** -->
					<!-- *********************************** -->                            
					<div id="timeline">
						<div class="lbl"><?php echo $LanguageInstance->get('waiting_remaining_time')?></div>
						<div class="clock" id="clock"><span class="mm">00</span>:<span class="ss">00</span></div>
						<div class="linewrap"><div class="line"></div></div>
					</div>                            
					<!-- WAITING MODAL -->
					<!-- TANDEM MODAL -->
					<div class='waitingForTandem'>
						<img class='loaderImg' src="css/images/loading_2.gif" width="128" height="128" alt="" />
						<span class='text'><?php echo $LanguageInstance->get("waiting_for_tandem_assignment");?></span>
					</div>
					<div class="clear">
						<p id="roomStatus"></p>
					</div>
					<div class="cleaner"></div>  
					<div id="logo">
						<a href="#" title="<?php echo $LanguageInstance->get('tandem_logo') ?>"><img src="css/images/logo_Tandem.png" alt="<?php echo $LanguageInstance->get('tandem_logo') ?>" /></a>
					</div>
				</div>
				<!-- /content -->
			</div>
			<!-- /main -->
		</div>
		<!-- /main-container -->
	</div>
	<!-- /wrapper -->
	<!-- footer -->
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
	<!-- /footer -->
	<?php include_once dirname(__FILE__) . '/js/google_analytics.php' ?>
</body>
</html>
<?php } ?>