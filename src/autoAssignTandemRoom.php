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
	$path = '';
	if (isset($_SESSION[TANDEM_COURSE_FOLDER]))
		$path = $_SESSION[TANDEM_COURSE_FOLDER] . '/';

	$id_resource_lti = $_SESSION[ID_RESOURCE];
	$lti_context = unserialize($_SESSION[LTI_CONTEXT]);
	$user_language = $_SESSION[LANG];//!empty($_REQUEST['locale']) ? $_REQUEST['locale'] : "es_ES";
	$other_language = ($user_language == "es_ES") ? "en_US" : "es_ES";
	$gestorBD = new GestorBD();    

	$currentActiveTandems = $gestorBD->currentActiveTandems($course_id);

	if($currentActiveTandems <= MAX_TANDEM_USERS){
		//Delete previous tandem
		$gestorBD->deleteFromWaitingRoom($user_obj->id,-1);//we delete all waiting room that are older than the defined MAX_WAITING_TIME
		$last_id = $gestorBD->get_lastid_invited_to_join($user_obj->id, $id_resource_lti, $course_id);
		$gestorBD->tandemMaxWaitingTime();//we delete all waiting room that are older than the defined MAX_WAITING_TIME
		$exercisesNotDone = $gestorBD->getExercicesNotDoneWeek($course_id,$user_obj->id); 
		$numPeopleWaitingForTandem = $gestorBD->sameLanguagePeopleWaiting($user_language,$course_id);
		$areThereTandems = $gestorBD->checkIfAvailableTandemForExercise($exercisesNotDone,$course_id,$user_language,$user_obj->id,$other_language);

		$getUsersWaitingEs = $gestorBD->getUsersWaitingByLanguage($course_id,"es_ES");
		$getUsersWaitingEn = $gestorBD->getUsersWaitingByLanguage($course_id,"en_US");
	}
//abertranb not need it mange all using the autoAssingCheckTandem.php
	/*
    //Ok we have the exercises the user has not done this week. Lets find someone waiting to do that exercise if not we offer it.
	
	//lets see first if we have people waiting in the waiting room that matches our exercises. 
	if(!empty($areThereTandems)){
		//ok so we are here cause we have someone waiting for one of our exercices.
		$tId = $gestorBD->createTandemFromWaiting($areThereTandems[0],$user_obj->id,$id_resource_lti);
		header("location: accessTandem.php?id=".$tId);
		die();
	}*/
	?>                    
	<!DOCTYPE html>
	<html>
	<head>
		<title>Tandem</title>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" media="all" href="css/autoAssignTandem.css?id=21" />
		<link rel="stylesheet" type="text/css" media="all" href="css/tandem-waiting-room.css?id=21" />
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
			<?php			
			//if the max number of allowed tanden rooms is reached, then we send a modal. 
			if($currentActiveTandems >= MAX_TANDEM_USERS){ ?>
				$.modal($('#maxTandems')); 	      
			<?php }else{ ?>

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
	        			if(json  &&  typeof json.tandem_id !== "undefined"){
	        			window.location.replace("accessTandem.php?id="+json.tandem_id);
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
                var minutos = 10;
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
	         desconn = function(){	         
	         	<?php 
	         	if(!empty($_SESSION[BasicLTIConstants::LAUNCH_PRESENTATION_RETURN_URL])){ 
	         		echo "window.location.replace('".$_SESSION[BasicLTIConstants::LAUNCH_PRESENTATION_RETURN_URL]."') ";
	         	}else
	         	echo "self.close();"; 
	         	?>
			}
			<?php } ?>


			//Updates the number of people waiting for english and spanish tandems
			var intervalWaiting = setInterval(function(){
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
    <style>
	.refreshPage{padding:5px;color:#FFF;}
    </style>
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
			<div id="main" <?php echo ($currentActiveTandems >= MAX_TANDEM_USERS) ? "style='display:none'" : ''  ?> >
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
						<div class='waitingForButton english '>
							<?php echo $LanguageInstance->get('Users waiting to practice English'); ?>:
							<span id='UsersWaitingEn'><?php echo $getUsersWaitingEn;?></span>
						</div>
						<div class='waitingForButton spanish '>
							<?php echo $LanguageInstance->get('Usuarios esperando para practicar Español');?>:
							<span id='UsersWaitingEs'><?php echo $getUsersWaitingEs;?></span>
						</div>

						<p class="clear"><?php echo $LanguageInstance->get("waiting_for_tandem_assignment");?>.</p>
						<!--span><i><?php echo $LanguageInstance->get("If you do not find partner in 10 minutes we recommend you access later")?>. <?php echo $LanguageInstance->get("Check other participants' availability in the classroom calendar")?></i></span>-->
						<h3><?php echo $LanguageInstance->get("6 STEPS for a successful Tandem experience")?>:</h3>
						
						<ol class="bullets">
							<li class="bullets"><?php echo $LanguageInstance->get("Go online at every hour and half-hour")?>.</li>
							<li class="bullets"><?php echo $LanguageInstance->getTagDouble("ALWAYS take your microphone and headphones (even if you have a laptop) and check that they work well in %s'Try your audio and video settings'%s on the top of the main page of the MOOC", '<a href="http://videoconference.speakapps.org/videochat/testEnvironment.htm" target="blank">', '</a>')?>.</li>
							<li class="bullets"><?php echo $LanguageInstance->get("Enter Tandem (The tandem icon is a green 't'). Then wait until a tandem partner connects with you")?>.</li>
							<li class="bullets"><?php echo $LanguageInstance->get("As soon as a Tandem partner connects with you, Videochat will be automatically activated and will start RECORDING.  Don’t be shy and don’t exit!  A window with the tandem tasks will be also automatically activated and you and your partner will be able to switch from the Videochat window to the Tandem window very easily")?>.</li>
							<li class="bullets"><?php echo $LanguageInstance->get("Do the tandem with your partner and REMEMBER that when the instructions are in Spanish you and your partner speak Spanish. When the instructions are in English, you speak English")?>.</li>
							<li class="bullets"><?php echo $LanguageInstance->get("IMMEDIATELY after the tandem activity, please evaluate your partner by clicking on 'peer review', where you fill in the portfolio")?>.</li>
						</ol>	
					</div>
					<!-- Max number of active tandems reached -->
					<div id='maxTandems' class='modal'>
						<span class='text'><?php echo $LanguageInstance->get("max_num_of_tandem_rooms_reached")?></span>
						<p><button class="btn btn-success refreshPage" type='button' onclick='window.location.reload()' ><?php echo $LanguageInstance->get("refresh_page");?></button></p>
					</div>
					<div class='manageSection'>
 					<?php  					
 					if ($user_obj->instructor==1 || $user_obj->admin==1) { ?>                            
                                <div class="clear">
                                <a href='manage_exercises_tandem.php'><?php echo $LanguageInstance->get("mange_exercises_tandem");?></a>
                                <a href='statistics_tandem.php'><?php echo $LanguageInstance->get("Tandem Statistics");?></a>
                            </div>                                
                     <?php } ?>                            
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
    <div id="modal-end-task" class="modal">
    	<div align="center">
	        <div>
	                <img id="home" src="images/final1.png"alt="" />
	        </div>
	        <div class="text">
	                <p><?php echo $LanguageInstance->get('Time expired');?>
	                <span><i><?php echo $LanguageInstance->get("Check other participants' availability in the classroom calendar")?></i></span>
	                </p>
	        </div>
	        <div class="">
	          <a href="#" onclick="desconn();"><img id="home" src="images/final2.png" alt="" /></a>
	        </div>
	    </div>    
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