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
	$user_language = !empty($_REQUEST['locale']) ? $_REQUEST['locale'] : "es_ES";
	$other_language = ($user_language == "es_ES") ? "en_US" : "es_ES";
	$gestorBD = new GestorBD();    
	 $last_id = $gestorBD->get_lastid_invited_to_join($user_obj->id, $id_resource_lti, $course_id);
	$exercisesNotDone = $gestorBD->getExercicesNotDoneWeek($course_id,$user_obj->id);   
    //Ok we have the exercises the user has not done this week. Lets find someone waiting to do that exercise if not we offer it.
	$gestorBD->checkIfAvailableTandemForExercise($exercisesNotDone,$course_id,$user_language,$user_obj->id);

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
		});

		//IE DETECTION - ERROR MSG TO USER
		var isIE11 = !!navigator.userAgent.match(/Trident\/7\./); //check compatibility with iE11 (user agent has changed within this version)
		var isie8PlusF = (function(){var undef,v = 3,div = document.createElement('div'),all = div.getElementsByTagName('i');while(div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',all[0]);return v > 4 ? v : undef;}());if(isie8PlusF>=8) isie8Plus=true;else isie8Plus=false;
		if(isIE11 || isie8Plus) isIEOk=true; else isIEOk=false;
		if (isie8PlusF<8) $.colorbox({href:"warningIE.html",escKey:true,overlayClose:false, width:400, height:350,onLoad:function(){$('#cboxClose').hide();}});
		// END
		//xml request for iexploiter11+/others 		
		if (isIEOk || window.ActiveXObject) xmlReq = new ActiveXObject("Microsoft.XMLHTTP");
		else xmlReq = new XMLHttpRequest();
		//global vars - will be extracted from dataROOM.xml
		var classOf="";
		var numBtns="";
		var numUsers="";
		var nextSample="";
		var node="";
		var room="";
		var data="";
		var TimerSUAR = 2000;
		var txtNews="";

		enable_exercise= function(value){
			value=parseInt(value, 10);
			if (value!='' && value>0) {
				$('#room').removeAttr('disabled');
				$("#room").combobox();
			}
			else { 
				$("#room").destroy();
				$('#room').attr('disabled', 'disabled');
			}
		}
		enable_button= function(value){
			if (value!='')
				$('#start').removeAttr('disabled');
			else 
				$('#start').hide();
		}
				
		putLink = function(){
			getXML();
			return false;
		}
		//opens next exercise	
		openLink = function(value,nextExer){
			top.document.location.href=classOf+".php?room="+value+"&user=a&nextSample="+nextSample+'&node='+node+'&data='+data;
		}

getXMLRoom = function(room){
	var room_2 = room.split("-");
	room = room.split("_");
	data = room[0].split("speakApps");
	data = data[0].split("-");

			data = data[0];//.toUpperCase();
			var extra_path = '';
			if (data.indexOf("/")>=0) {
				data = data.split("/");
				data.indexOf("/");
				extra_path = data[0]+"/";
				data = data[1];
			}

			room = room_2[1];
			var url= "<?php echo $path; ?>"+extra_path+"data"+data+".xml";
			xmlReq.onreadystatechange = processXml;
			if(!isIEOk){
				xmlReq.timeout = 100000;
				xmlReq.overrideMimeType("text/xml");
			}
                        //alert(url);
                        xmlReq.open("GET", url, true);
                        xmlReq.send(null);
}
printError  = function(error){
	$('#roomStatus').val(error);
}

//get dataROOM.xml params
getXML = function(){
	alert('dins de getXML');
	user_selected = $('#user_selected').val();
	room_temp = $('#room').val();
	if (user_selected=="" || user_selected=="-1") {
				//alert("<?php echo $LanguageInstance->get('select_user')?>");
			} else {
				if (room_temp=="" || room_temp=="-1") {
					//alert("<?php echo $LanguageInstance->get('select_exercise')?>");
				} else {
					enable_button('');
					getXMLRoom(room_temp);
				}
			}
}
processXml = function(){
	if((xmlReq.readyState == 4) && (xmlReq.status == 200)){
		//extract data
		if (xmlReq.responseXML!=null) {
			var cad=xmlReq.responseXML.getElementsByTagName('nextType');
			classOf=cad[0].getAttribute("classOf");
			numBtns=cad[0].getAttribute("numBtns");
			numUsers=cad[0].getAttribute("numUsers");
			nextSample=cad[0].getAttribute("currSample");
			node=parseInt(cad[0].getAttribute("node"))+1;
			user_selected=$('#user_selected').val();
			document.getElementById('roomStatus').innerHTML="";
			$('#idfrm').attr('src','checkRoomUserTandem.php?id_user_guest=-1&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
		} else {
			$('#roomStatus').html('Error loading exercise '+data+' contact with the administrators');
		}
	} else if ((xmlReq.readyState	==	4) && (xmlReq.status == 404)) {
		$('#roomStatus').html('Exercise '+data+' does not exist');
	}
}
createRoom = function(room, data, nextSample, node, classOf){
		//extract data
		var cad=xmlReq.responseXML.getElementsByTagName('nextType');
		classOf=cad[0].getAttribute("classOf");
		numBtns=cad[0].getAttribute("numBtns");
		numUsers=cad[0].getAttribute("numUsers");
		nextSample=cad[0].getAttribute("currSample");
		node=parseInt(cad[0].getAttribute("node"))+1;
		user_selected=$('#user_selected').val();
		document.getElementById('roomStatus').innerHTML="";
		$('#idfrm').attr('src','checkRoomUserTandem.php?id_user_guest=-1&create_room=1&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
		//TODO in Ajax
}
printError = function (error) {
	alert(error);
}
enable_button();
 /*intervalCheck = setInterval(function(){
	$.ajax({
		type: 'GET',
		url: "new_tandems.php",
		data: {
			id: <?php echo $last_id; ?>
		},
		dataType: "xml",
		success: function(xml){
			var id_txt = $(xml).find('id').text();
			if (id_txt &&  id_txt.length>0) {
				var created_txt = $(xml).find('created').text();
				var nameuser_txt = $(xml).find('nameuser').text();
				var exercise_txt = $(xml).find('exercise').text();
				$("#info-block").show();                                                
				window.location.replace("accessTandem.php?id="+id_txt);                             
				clearInterval(intervalCheck);
			}
		},
		error: function(){
			clearInterval(intervalCheck);
			TimerSUAR+=500;
			intervalCheck = setInterval(intervalCheck,TimerSUAR);
			
		}
	});
},TimerSUAR); */	

<?php 
    $tandemBLTI = new IntegrationTandemBLTI();
    
    $selected_exercise = $tandemBLTI->checkXML2GetExercise($user_obj);

if ($selected_exercise && strlen($selected_exercise)>0){ echo 'getXML();'; }?>
			


var intTimerNow;
var isNowOn=0;
function setExpiredNow(itNow){
	isNowOn=1;
	intTimerNow = setTimeout("getTimeNow("+itNow+");", 1000);
}
function getTimeNow(itNow){
	var tNow;
	itNow--;
	if(itNow<10) tNow ="0"+itNow;
	else tNow = itNow;
	$("#startNowBtn").html("<?php echo $LanguageInstance->get('accept')?> 00:"+tNow);
	if(itNow<=1){ 
		$("#startNowBtn").removeClass("tandem-btn").addClass("tandem-btnout");
		$("#startNowBtn").html("<?php echo $LanguageInstance->get('caducado')?>");
		$("#startNowBtn").attr("href", "#");
		window.location.href=window.location.href;
		clearInterval(intTimerNow);
	}
	else setExpiredNow(itNow);
}
</script>
<?php 
//Permetem que seleccini l'exercici 20111110
$is_host = $user_obj->is_host; 
$array_exercises = $gestorBD->get_tandem_exercises($course_id);
$tandemBLTI = new IntegrationTandemBLTI();
$selected_exercise = $tandemBLTI->checkXML2GetExercise($user_obj);
$selected_exercise_select = isset($_POST['room']) ? $_POST['room'] : '';
    //IMPORTANT ***************************************************************
    //
    //Gets the object of tandem if exists, if not this user is the host
$pending_invitations = $gestorBD->get_invited_to_join($user_obj->id, $id_resource_lti, $course_id, true);

$last_id = $gestorBD->get_lastid_invited_to_join($user_obj->id, $id_resource_lti, $course_id);
$name = mb_convert_encoding($user_obj->name, 'UTF-8', 'UTF-8');
?>                                
<script>
	$(document).ready(function() {
		StartTandemTimer();
	});
</script>

<?php if ($selected_exercise && strlen($selected_exercise) > 0) {
	echo 'getXML();';
} ?>         
<script>
	function restFromWaitingRoomAndTandem(paramString){
		$.ajax({
			data:{ 
				'language': "<?php echo $_SESSION[LANG]; ?>", 
				'courseID': "<?php echo $course_id; ?>",
				'userID':  "<?php echo $user_obj->id; ?>",
				'typeClose': paramString
			},
			url: "differentRequests.php",
			type: "POST",
			dataType: "html",
		}).done(function(data){
			console.log(data);
		}); 
	}

	function getWaitingTandemRoom(exercise_id,only_exercise_id,is_tandem){
        $.ajax({
        	data:{ 
        		'language': "<?php echo $_SESSION[LANG]; ?>", 
        		'courseID': "<?php echo $course_id; ?>", 
                    //'exerciseID': encodeURIComponent(exercise_id),
                    'exerciseID': exercise_id,
                    'userID':  "<?php echo $user_obj->id; ?>",
                    'ltiID': "<?php echo $_SESSION[ID_RESOURCE]; ?>",
                    'onlyExID' : only_exercise_id,
                    'is_tandem' : is_tandem?1:0
                },
                url: "getRequestsWaitingTandemRoom.php",
                type: "POST",
                dataType: "json",

            }).done(function(data){
            	if (is_tandem==true && data =="room_taken"){

            		modalRoomTaken();                      

            	}else if(is_tandem==true){                                  
            		tandemStandBy();
                    //$("#simplemodal-container").show( "fast");
                    top.document.getElementById('roomStatus').innerHTML="<?php echo $LanguageInstance->get('Connecting...')?>";
                    setTimeout("alert",30000);
                }                	
            });                 
        }
            </script>
            <script>

            	var intTimerNow;
            	var limitTimer = 500;
            	var limitTimerConn = 1000;
            	function setExpiredNow(itNow){
            		intTimerNow = setTimeout("getTimeNow("+itNow+");", 10000);
            	}
            	function getTimeNow(itNow){
            		var tNow;
            		itNow--;
            		if(itNow<10) tNow ="0"+itNow;
            		else tNow = itNow;
            		$("#startNowBtn").html("00:"+tNow);
            		if(itNow<=1){ 
            			clearInterval(intTimerNow);
				//desconn();
			}
			else setExpiredNow(itNow);
		}                
		StartTandemTimer = function(){
			$("#timeline").show("fast");
			var minutos = 30;
			var segundos = 0;
			timerOn(minutos,segundos);
			timeline.start();
		}

var totalUser = 0;
$(document).ready(function(){
//colorbox js actionexample3
notifyTimerDown = function(id){
	if($.trim(txtNews)!=$.trim(id)){
		$('#showNews').html(id);
		$('#showNews').fadeIn(1000).slideDown("fast");
		$("#showNews").delay(3000).fadeOut(1000).slideUp("fast");
		txtNews=id;
	}
}
});
		timeline = $('#timeline').timeLineClock({
			time: {hh:0,mm:parseInt(minutos),ss:parseInt(segundos)},
			onEnd: theEnd
		}); 
	   

        // fin espera partner tarea con timer
        function partnerTimerTaskReady(){
        	$.modal.close();
        	stepReady($('#steps li .waiting'));
        	timeline.start();
        }
        
	// ventana modal tiempo agotado
	function theEnd(){
		if ($("#modal-end-task").length > 0){
			$.modal($('#modal-end-task'));
			//accionTimer();
		}
	}
        //conectando tandem
        function tandemStandBy(){
        	if ($("#waitingUser").length > 0){
        		$.modal($('#waitingUser'));
        		modalTimer2();
        	}
        }  
	 //modal with the message of room already in a tandem.
	 function modalRoomTaken(){
	 	if($("#modalRoomTaken").length > 0){
	 		$.modal($('#modalRoomTaken'));            
	 	}
	 }

	 var show_message_giveup = true;
	 function showWaitingMessage(urlToRedirect, tandem_id){
	 	if ($("#modal-start-task").length > 0){
	 		show_message_giveup = true;
               // alert(urlToRedirect);
               // alert(tandem_id);
                //modalTimer();
                //clearInterval(intervalCheck);
                var TimerSUAR = 1000;
                intervalCheckHavePartner = setInterval(function(){
                	$.ajax({
                		type: 'GET',
                		url: "have_partner.php",
                		data: {
                			id: tandem_id
                		},
                		dataType: "xml",
                		success: function(xml){
                			var id_txt = $(xml).find('id').text();
                			if (id_txt &&  id_txt.length>0) {
                				document.location.href = urlToRedirect;						
                				clearInterval(intervalCheckHavePartner);                				
                			}
                		},
                		error: function(){
                			clearInterval(intervalCheckHavePartner);
                			TimerSUAR+=500;
                			intervalCheckHavePartner = setInterval(intervalCheckHavePartner,TimerSUAR);
                			notifyTimerDown('<?php echo $LanguageInstance->get('SlowConn')?>');
                		}
                	});
                },TimerSUAR);	
               // startModalQuickly();
           }
       }

       function startModalQuickly(){

       	$('#modal-start-task').modal( {onClose: function () {
       		if (show_message_giveup) {
                      //alert('give_up');
                      closeModalAndCountAgain('give_up');
                  } else {

                  	restFromWaitingRoomAndTandem('lapsed'); 
                      //closeModalAndCountAgain('lapsed');
                      $.modal.close(); // must call this!
                  }
              }})
       }

       function closeModalAndCountAgain(paramString) {
       	if (counterW){
       		clearInterval(counterW);   
       	}
       	beginningOneMoreTime();
            $.modal.close(); // must call this!
            if (show_message_giveup){
                restFromWaitingRoomAndTandem(paramString); //we rest from the waiting room and the tandem room
            }
        }
        
        function closeModalAndCountAgainTandem() {
        	if (counterT){
        		clearInterval(counterT);   
        	}
        	beginningOneMoreTime();
            $.modal.close(); // must call this!

        }

        var counterW = false;
        var countW = <?php echo WAITING_TIME; ?>;
        var countCurrentW = countW;

        function modalTimer(){
        	countCurrentW = countW;
            counterW = setInterval(timerW, 1000); //1000 will  run it every 1 second
        }
        
        function timerW(){

        	countCurrentW=countCurrentW-1;
        	if (countCurrentW <= 0)
        	{
                //alert('lapsed');
                show_message_giveup=false;
                closeModalAndCountAgain('lapsed');
                return;
            }

            document.getElementById("timerWaiting").innerHTML=countCurrentW + " secs"; // watch for spelling
        }
        

        var counterT = false;
        var countT = <?php echo TANDEM_TIME; ?>;
        var countCurrentT = countT;
        function modalTimer2(){
        	countCurrentT = countT;
            counterT = setInterval(timerT, 1000); //1000 will  run it every 1 second
        }
        
        function timerT(){
        	countCurrentT=countCurrentT-1;
        	if (countCurrentT <= 0)
        	{
                //clearInterval(counterT);
                //$.modal.close(); 
                closeModalAndCountAgainTandem();
                return;
            }
            document.getElementById("timerTandem").innerHTML=countCurrentT + " secs"; // watch for spelling            
        }        
        function timeStop(){
        	timeline.stop();
        }        
        function beginningOneMoreTime(){
        	timeline.start();
        }        
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
	<iframe src="" width="0" frameborder="0" height="0" id="idfrm" name="idfrm"></iframe>

	<?php include_once dirname(__FILE__) . '/js/google_analytics.php' ?>
</body>
</html>
<?php } ?>