<?php
//Retrieve data from url params
$room = $_GET["room"];
$data = $_GET["data"];
$user = $_GET["user"];
$is_final = false;



include_once(dirname(__FILE__).'/classes/register_action_user.php');
include_once(dirname(__FILE__).'/classes/gestorBD.php');
require_once dirname(__FILE__).'/classes/lang.php';


$id_current_tandem = $_SESSION[CURRENT_TANDEM];
$gestorBDSample = new GestorBD();
$tandem = $gestorBDSample->obteTandem($id_current_tandem);

$title_exercise = $tandem['name_exercise'];

if(isset($_GET['userb']) && $_GET['userb']!="" && $_GET['userb']!=null){
	$userBid = $_GET['userb'];
	$nameb = $gestorBDSample->getUserB($userBid);
}

$iexploiter11=false;
if (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false) $iexploiter11=true;

$ExerFolder = $_GET["nextSample"];
//This is because xml nodes begins counting at zero, but zero is not real :-) 
if($_GET["node"]==1) $node = $_GET["node"];
else $node = $_GET["node"]-1;
//For user A and B only. If more users or login names needed, fetch data from xml :-)
if($user =='a') $Otheruser='b';
else $Otheruser='a';
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="ie ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="ie ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="ie ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="ie ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
<head>
	<meta charset=utf-8 />
	<title>Tandem</title>
	<link media="screen" rel="stylesheet" href="css/colorbox.css" />
	<link media="screen" rel="stylesheet" href="css/default.css" />
	<link rel="stylesheet" type="text/css" href="css/tandem.css" media="all" />
	<script src="js/jquery-1.7.2.min.js"></script>
	<script src="js/jquery.colorbox-min.js"></script>
	<script src="js/jquery.ui.widget.js"></script>
	<script src="js/jquery.ui.core.js"></script>
	<script src="js/jquery.ui.progressbar.js"></script>
	<script src="js/loadUserData.js"></script>
	<script type="text/javascript" src="js/jquery.animate-colors.min.js"></script>
	<script type="text/javascript" src="js/jquery.iframe-auto-height.plugin.1.7.1.min.js"></script>
	<script type="text/javascript" src="js/jquery.infotip.min.js"></script>
	<script type="text/javascript" src="js/jquery.timeline-clock.min.js"></script>
        <script type="text/javascript" src="js/jquery.barrating.min.js"></script>
	<?php include_once dirname(__FILE__).'/js/google_analytics.php';?>
	<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
	<link type="text/css" href="js/window/css/jquery.window.css" rel="stylesheet" />
        <link type="text/css" href="css/bars-square.css" rel="stylesheet" />
	<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
	<?php }?>
	<script type="text/javascript" src="js/jquery.simplemodal.1.4.2.min.js"></script>

	
	<script type="text/javascript">
var isIE11 = !!navigator.userAgent.match(/Trident\/7\./); //check compatibility with iE11 (user agent has changed within this version)
var isie8PlusF = (function(){var undef,v = 3,div = document.createElement('div'),all = div.getElementsByTagName('i');while(div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',all[0]);return v > 4 ? v : undef;}());if(isie8PlusF>=8) isie8Plus=true;else isie8Plus=false;
if(isIE11 || isie8Plus) isIEOk=true; else isIEOk=false;
//timer
var intTimerNow;
var limitTimer = 500;
var limitTimerConn = 1000;
var node = <?php echo $node;?>;
var numOfChecksSameNode = 0; //only applies when the cad is bigger than current node
var ExerFolder = '<?php echo $ExerFolder?>';
sessionStorage.checkModal = "0";

function setExpiredNow(itNow){
	intTimerNow = setTimeout("getTimeNow("+itNow+");", 1000);
}
function getTimeNow(itNow){
	var tNow;
	itNow--;
	if(itNow<10) tNow ="0"+itNow;
	else tNow = itNow;
	$("#startNowBtn").html("00:"+tNow);
	if(itNow<=1){ 
		clearInterval(intTimerNow);
		desconn();
	}
	else setExpiredNow(itNow);
}
//timer
var totalUser = 0;
$(document).ready(function(){
    createRatings = function(){
        $('#comment').val('')
        $('#rating-square-enjoyed-div').html('');
        $('#rating-square-enjoyed-div').html('<select id="rating-square-enjoyed" class="rating-square"><option value=""></option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option></select>');
        $('#rating-square-enjoyed').barrating({
            theme: 'bars-square',
            showValues: true,
            showSelectedRating: false
        });
        $('#rating-square-nervous-div').html('');
        $('#rating-square-nervous-div').html('<select id="rating-square-nervous" class="rating-square"><option value=""></option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option></select>');
        $('#rating-square-nervous').barrating({
            theme: 'bars-square',
            showValues: true,
            showSelectedRating: false
        });
        $('#rating-square-task-valoration-div').html('');
        $('#rating-square-task-valoration-div').html('<select id="rating-square-task-valoration" class="rating-square"><option value=""></option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option></select>');
        $('#rating-square-task-valoration').barrating({
            theme: 'bars-square',
            showValues: true,
            showSelectedRating: false
        });
        $('#rating-square-enjoyed').on('change', function() {
            $('#enjoyed').val(this.value);
        });
        $('#rating-square-nervous').on('change', function() {
            $('#nervous').val(this.value);
        });
        $('#rating-square-task-valoration').on('change', function() {
            $('#task-valoration').val(this.value);
        });
    }
    <?php if ($_SESSION[SHOW_USER_STATUS]){ ?>
        $('#moodModal').modal('show');
        createRatings();
    <?php } ?>
    showEvaluateTaskModal = function(last){
        <?php if ($_SESSION[ENABLE_TASK_EVALUATION]){ ?>
                $('#simplemodal-container').css('display', 'block');
                $('#simplemodal-overlay').css('display', 'block');
                if (last == 0){
                    $("#moodBtn").attr("onclick","evaluateTask(0)");
                    $('#evaluateTaskModal').modal('show');
                }else{
                    $("#moodBtn").attr("onclick","evaluateTask(1);showVideoChatAndGoodbyeMessage();");
                    $("#closeModalBtn").attr("onclick","closeModal(1)");
                    $('#evaluateTaskModal').modal('show');
                }
        <?php } ?>    
    }
    emojiSelected = function(selected){
        $.ajax({
                type: 'POST',
                url: "updateUserMood.php",
                data : {
                           id_tandem : '<?php echo $_SESSION['current_tandem'];?>',
                           id_user : '<?php echo $_SESSION['current_user']->id;?>',
                           mood : selected
                },
                success: function(data){
					$('#moodModal').modal('hide');
                }
        });
    }
    
    closeModal = function(last){
        $('#simplemodal-container').css('display', 'none');
        $('#simplemodal-overlay').css('display', 'none');
        createRatings();
        if (last == 1){
            showVideoChatAndGoodbyeMessage();
        }
    }
    
    
    allowClick = function(){
        if ($('#allow').is(":checked")){
            $('#send-contact-email-btn').prop('disabled', false);
            $('#send-contact-email-btn').removeClass('disabled');
        }else{
            $('#send-contact-email-btn').prop('disabled', true);
            $('#send-contact-email-btn').addClass('disabled');
        }
    }
    
    sendContactEmail = function(){
        if (($('#contact-email-subject').val().trim() !== '') && ($('#contact-email-comment').val().trim() !== '')){
            var msg = $('#contact-email-comment').val();
            var subject = $('#contact-email-subject').val();
            $('#contact-email-modal-warning').css('display', 'none');
            $('#send-contact-email-cancel-btn').click();
            $.ajax({
                    type: 'POST',
                    url: "send-contact-email.php",
                    data : {
                        msg : msg,
                        subject: subject,
                        current_user_id: '<?php echo $_SESSION['current_user']->id;?>',
                        user_host_id: '<?php echo $tandem['id_user_host'];?>',
                        user_guest_id: '<?php echo $tandem['id_user_guest'];?>'
                    },
                    success: function(data){	 
                        // 
                    }
            });
        }else{
            $('#contact-email-modal-warning').css('display', 'block');
        }
    }
    
    checkIfExternalToolClosed = function(){
        if (sessionStorage.getItem("checkModal") == "0"){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var xmlDoc = this.responseXML;
                    if( xmlDoc.getElementsByTagName("externalToolClosed").length != 0 ){
                        if ($('#contact-user-modal').length > 0) {
                            $('#contact-user-modal').modal('show');
                            sessionStorage.setItem("checkModal","1");
                        }
                    }
                }
            };
            xhttp.open("GET", 'xml/' + '<?php echo $_GET['room']; ?>' + ".xml", true);
            xhttp.send();
        }
    }
    
    evaluateTask = function (last){
        $('#simplemodal-container').css('display', 'none');
        $('#simplemodal-overlay').css('display', 'none');
        var task_number = node
        if (last == 0){
            task_number = node - 1;
        }
        $.ajax({
                type: 'POST',
                url: "evaluateTask.php",
                data : {
                           id_tandem : '<?php echo $_SESSION['current_tandem'];?>',
                           id_user : '<?php echo $_SESSION['current_user']->id;?>',
                           task_number : task_number,
                           enjoyed : $('#enjoyed').val(),
                           nervous : $('#nervous').val(),
                           task_valoration : $('#task-valoration').val(),
                           comment : $('#comment').val()
                },
                success: function(data){
                    createRatings();
                }
        });
    }
//colorbox js actionexample3
notifyTimerDown = function(id){
	if($.trim(txtNews)!=$.trim(id)){
		$('#showNews').html(id);
		$('#showNews').fadeIn(1000).slideDown("fast");
		$("#showNews").delay(8000).fadeOut(1000).slideUp("fast");
		txtNews=id;
	}
}
//colorbox
$("a[rel='example1']").click(function(event){
	event.preventDefault();
	$('a[rel="example1"]').colorbox({
		maxWidth: '90%',
		initialWidth: '200px',
		initialHeight: '200px',
		speed: 300,            
		overlayClose: false
	});
	$.colorbox({href: $(this).attr('href')});
});

//global vars
			//20121004
			var see_solution = true;
			//END
			var txtNews="";
			var accionNum=0;
			var posibleDesconn=0;
			var userDesconn = 0;
			var classOf;
			var numExerc;
			var numUsers;
			var nextSample;
			var numBtn;
			var numNodes=0;
			var numCadenas;
			var textE="";
			var salir=0;
			var minutos;
			var segundos;
			var barraLoadTimer;
			var initHTML;
			var initHTMLB;
			var body = document.getElementsByTagName('body').item(0);
			var script = document.createElement('script');	
			var endOfTandem=0;
			var intervalTimerAction;
			var intervalIfNextQuestion;
			var intervalIfNextQuestionAnswered;
			var intervalUpdateAction;
			var intervalUpdateLogin;
//xml request for iexploiter/others 		
if (window.ActiveXObject) xmlReq = new ActiveXObject("Microsoft.XMLHTTP");
else xmlReq = new XMLHttpRequest();
//get data from dataROOM.xml->initializes exercise values


<?php
require_once(dirname(__FILE__).'/classes/constants.php');
if(!isset($_SESSION)) {
	session_start();
}

$path = '';
$extra = '';
if (isset($tandem['relative_path']) && strlen($tandem['relative_path'])>0){
	$extra = $tandem['relative_path'];
}

if (isset($_SESSION[TANDEM_COURSE_FOLDER])) $path = $_SESSION[TANDEM_COURSE_FOLDER].$extra.'/';
?>

 function getInitXML(){
	$.ajax({
		type: 'GET',
		url: "<?php echo $path;?>data<?php echo $data;?>.xml",
		data: {
		},
		dataType: "xml",
		success: function(xml){
					    //var clientid = $(xml).find('client_id').eq(1).text();
					  //extract data
						//var lng=$(xml).find('exe').attr('lang');
						//script.src = "lang/"+lng+".js";
						//script.type = 'text/javascript';
						//body.appendChild(script);
						var cad=$(xml).find('nextType');
						numNodes=cad.length-1;
						
						for(var i=1;i<=numNodes;i++){
							var txtInfoTask = cad[i].getElementsByTagName("textE")[0].childNodes[0].data;
							<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
							$("#infoT"+i+"t").html(i);
						<?php } else { ?>
							$("#infoT"+i+"t").html("Task "+i);
							<?php } ?>
							$("#infoT"+i+"txt").html(txtInfoTask);
						}

						if((node+1)<=numNodes){
							classOf=cad[node+1].getAttribute("classOf");
							nextSample=cad[node+1].getAttribute("currSample");
						}
						numExerc=node;
						numUsers=cad[node].getAttribute("numUsers");
						numBtn=cad[node].getAttribute("numBtns");
						
						//timer
						isTimerOn = cad[node].getAttribute("timer");
						if(isTimerOn!=null){
							minutos = isTimerOn.split(":")[0];
							segundos = isTimerOn.split(":")[1];
							$("#timeline").show("fast");
							// ventana modal al inicio de tarea con timer
							if ($("#modal-start-task").length > 0){
								$.modal( $('#modal-start-task') , {
									onClose: function (d){
										var s = this;d.container.fadeOut(300,function(){d.overlay.fadeOut(300,function(){s.close();});});}});
							}
							timerOn(minutos,segundos);
						}
						initHTML = cad[node].getAttribute("initHTML");
						initHTMLB = cad[node].getAttribute("initHTMLB");
						endHTML = cad[node].getAttribute("endHTML");
						textE=cad[node].getElementsByTagName("textE")[0].childNodes[0].data;
						getXML("<?php echo $user;?>","<?php echo $room;?>");
						if(intervalUpdateLogin){
							clearInterval(intervalUpdateLogin);
						}
						intervalUpdateLogin = setInterval('getXMLDone("<?php echo $user;?>","<?php echo $room;?>")',limitTimer);
	//thread is so quick...
	writeButtons(endHTML == null ? true : false);
	setTimeout(function(){notifyTimerDown('<?php echo $LanguageInstance->get('txtWaiting4User')?>');},250);
}   
});
}
	
	//timer
	StartTandemTimer = function(){
		$("#lnk-start-task").addClass("btnOff");
		$("#lnk-start-task").html("Waiting...");
		$("#lnk-start-task").removeAttr("href");
		$("#lnk-start-task").removeAttr("onclick");
		accionPreTimer();
		if(intervalTimerAction){
			clearInterval(intervalTimerAction);
		}
		intervalTimerAction = setInterval(timerChecker,1000);
	}
	timerChecker = function(){
		$.ajax({
			type: 'GET',
			url: "check.php?room=<?php echo $room; ?>&t=1",
			data: {
			},
			dataType: "xml",
			statusCode: {
				404: function() {
					hideText();
					hideButtons();
					userDesconn=1;
				}
			},
			success: function(xml){

				var cad = $(xml).find('actions');				
				var isFinishedFirst = cad[node-1].getAttribute('firstUser');
				var isFinishedSecond = cad[node-1].getAttribute('secondUser');
				if(isFinishedFirst!=null && isFinishedSecond!=null){ 
					clearInterval(intervalTimerAction);
					partnerTimerTaskReady();
				}
			}
		})
	}
	accionPreTimer = function(){
		$.ajax({
			type: 'GET',
			url: "action.php",
			data: {'room':'<?php echo $room;?>','user':'<?php echo $user?>','nextSample':node,'tipo':'confirmPreTimer'},
			dataType: "xml"
		});
	}		
			//acabaTiempo!			
			accionTimer = function(){
				$.ajax({
					type: 'GET',
					url: "action.php",
					data: {'room':'<?php echo $room;?>','numBtn':numBtn,'user':'<?php echo $user?>','nextSample':node,'tipo':'confirmTimer'},
					dataType: "xml"
				});
				showSolutionAndShowNextTask();
				//$('#ifrmHTML').attr("src","<?php echo $path; ?>ejercicios/<?php echo $ExerFolder;?>/"+endHTML);
			}
//timer


// processInitXml = function(){
// 	if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
// 		var cad=xmlReq.responseXML.getElementsByTagName('nextType');
// 		numNodes=cad.length-1;
// 		if(node+1<=numNodes){
// 			classOf=cad[node+1].getAttribute("classOf");
// 			nextSample=cad[node+1].getAttribute("currSample");
// 		}
// 		numExerc=node;
// 		numUsers=cad[node].getAttribute("numUsers");
// 		numBtn=cad[node].getAttribute("numBtns");
// 		textE=cad[node].getElementsByTagName("textE")[0].childNodes[0].data;
// 		getXML("<?php echo $user;?>","<?php echo $room;?>");
// 		intervalUpdateLogin = setInterval('getXMLDone("<?php echo $user;?>","<?php echo $room;?>")',limitTimer);
// //thread is so quick...
// writeButtons();
// setTimeout(function(){notifyTimerDown('<?php echo $LanguageInstance->get('txtWaiting4User')?>');},150);
// hideButtons();
// }
// }

//Initializes & creates users node in room's xml			
getXML = function(user,room){
	var url="createUser.php";
	var params="user="+user+"&room="+room;
	xmlReq.onreadystatechange = processXml;
	xmlReq.open("GET", url+"?"+params, true);
	if(!isIEOk){
		xmlReq.timeout = 10000;
		xmlReq.overrideMimeType("text/xml");
	}


	xmlReq.send(null);
}
//nothing to do
processXml = function(){}
//Interval (500ms) checking xml and waiting for both users to be connected
getXMLDone = function(user,room){
	var url="check.php?room=<?php echo $room; ?>&t=2";
	xmlReq.onreadystatechange = processXmlOverDone;
	xmlReq.open("GET", url, true);
	if(!isIEOk){
		xmlReq.timeout = 10000;
		xmlReq.overrideMimeType("text/xml");
	}
	xmlReq.onerror = onError;	
	xmlReq.send(null);
	
}

onError = function(){
	clearInterval(intervalUpdateLogin);
	limitTimer+=500;
	if (intervalUpdateLogin) {
		clearInterval(intervalUpdateLogin);
	}
	intervalUpdateLogin = setInterval('getXMLDone("<?php echo $user;?>","<?php echo $room;?>")',limitTimer);
	notifyTimerDown('<?php echo $LanguageInstance->get('SlowConn')?>');
}

processXmlOverDone = function(){
	if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
		if(check4UsersConex()){
			//when both connected show alert, change user->side images and central image
			notifyTimerDown('<?php echo $LanguageInstance->get('txtOtherUserConn')?>');
			setTimeout(function(){$("#imgR").attr('src','images/before_connecting<?php echo $user;?>.jpg');},1000);
			setTimeout(function(){$("#imgR").attr('src','images/connecting.jpg');},1500);
			$('#buttonsCheck').show('fast');
			$('#LayerBtn0').show('slow');
			$('#image').fadeIn('slow');
			showImage('<?php echo $user;?>');
}
}
}

var UserGotDisconnectedMessage = 0;
//here if isDisconnected is true, then we call the drop down popup to alert about this
userGotDisconnected = function(UserName){	
		if(UserName.length > 0 && UserGotDisconnectedMessage == 0){			
                    notifyTimerDown("<?php echo $LanguageInstance->get('The user %1 has been disconnected or closed the video chat session')?>".replace("%1",UserName));
                    UserGotDisconnectedMessage = 1;
                }
}
//check for both connected
check4UsersConex = function(){
	var cad=xmlReq.responseXML.getElementsByTagName('usuario');
	numCadenas=cad.length;
//are both users written into xml?
if(numCadenas==numUsers){
			getUsersDataXml('<?php echo $user?>','<?php echo $room?>');
			//when both connected stop checking for connex, starts interval for checking answers, show intro page, ready for desconnex
			clearInterval(intervalUpdateLogin);

			if(intervalUpdateAction){
				clearInterval(intervalUpdateAction);
			}
			intervalUpdateAction = setInterval(check4BothChecked,<?php echo !empty($_REQUEST['elparam']) ? $_REQUEST['elparam'] : 1500 ?>);
			//if(numExerc==1) $.colorbox({href:"home.php?id=<?php echo $data;?>",escKey:true,overlayClose: false});
			if(numExerc==1){
				clearInterval(intTimerNow); 
				$.colorbox.close();
			}
			posibleDesconn=1;
			return true;
	}else 
	return false;
}
			check4BothChecked = function(){	
                                checkIfExternalToolClosed();
				$.ajax({
					type: 'GET',
					url: "check.php?room=<?php echo $room; ?>&t=3",
					dataType: "xml",
					statusCode: {
						404: function() {							
							hideText();
							hideButtons();
							userDesconn=1;
						},
						408: function(){							
							clearInterval(intervalUpdateAction);
							limitTimerConn+=500;
							if (intervalUpdateAction) {
								clearInterval(intervalUpdateAction);
							}
							intervalUpdateAction = setInterval(check4BothChecked,<?php echo !empty($_REQUEST['elparam']) ? $_REQUEST['elparam'] : 1500 ?>);
							notifyTimerDown('<?php echo $LanguageInstance->get('SlowConn')?>');
						}
					},
					success: function(xml){

<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
						//lets see if the other user got disconnected from the external tool
						var externalToolClosed = xml.getElementsByTagName('externalToolClosed');
						if(externalToolClosed.length > 0 && externalToolClosed[0].childNodes.length>0){												
							userGotDisconnected(externalToolClosed[0].childNodes[0].nodeValue);
						}
						<?php } else {?>
						// This code is not used anymore because we only have solution buttons right now 20141022 .
						users = xml.getElementsByTagName('usuarios');
						total = users.length;
						if (total>0) {
							users = users[0].childNodes;
							total = users.length;
							if (total>totalUser) {
							}
							totalUser = total;
						}
						var countNodesXML = <?php echo $node-1;?>;
						var cad = $(xml).find('actions');
						if(cad.length>countNodesXML){
							if(cad[countNodesXML]!=null && cad[countNodesXML].getElementsByTagName('action').length>accionNum){	
								var isFinishedFirst = cad[countNodesXML].getElementsByTagName('action')[accionNum].getAttribute('firstUser');
								var isFinishedSecond = cad[countNodesXML].getElementsByTagName('action')[accionNum].getAttribute('secondUser');						
								if(isFinishedFirst!=null && isFinishedSecond!=null){
									accionNumPrev = parseInt(accionNum);
									accionNum = parseInt(accionNum)+1;
									EndwaitStep(accionNum);
									//if true, exercise finished	
									if(accionNum==numBtn) {
											// 20121004 - abertranb - change to show sholution instead of go to next question
											//showNextQuestion();
											//MODIFIED
											enableSolution();
											//END
									}
									//First answer, notify the other user
								}else if(isFinishedFirst!=null && isFinishedSecond==null && isFinishedFirst!='<?php echo $user;?>'){
									notifyTimerDown("<?php echo $LanguageInstance->get("txtTheUser");?>"+isFinishedFirst+"<?php echo $LanguageInstance->get("txtReplied");?>");
								}					
						}
					}
					<?php }?>
},error: function (xhr, ajaxOptions, thrownError) {
        // console.log(xhr.status);
        // console.log(thrownError);
}

})

}


//Interval (1000ms) checking for both users to write down answer into xml
check4BothChecked_old = function(){
	var url="<?php echo $room; ?>.xml";
	xmlReq.onreadystatechange = processXmlOverChecked;
	if(userDesconn==0){
		if(!isIEOk){
			xmlReqUser.timeout = 10000;
			xmlReqUser.overrideMimeType("text/xml");
		}		
		xmlReq.open("GET", url, false);
		xmlReq.send(null);
	}
}
//checks that room's xml exists
xmlDontExists = function(url){
	if(userDesconn==0){
		if (window.ActiveXObject) http = new ActiveXObject("Microsoft.XMLHTTP");
		else http = new XMLHttpRequest();
		http.open('HEAD', url, false);
		http.send();
		return http.status;
	}else return 200;
}
///////////////////////////////////////////////////
//main function. Checks user's answers
processXmlOverChecked = function(){
	//checks that room's xml exists
	if(xmlDontExists("<?php echo $room; ?>.xml") == 404){
		hideText();
		hideButtons();
		userDesconn=1;
	}else if(xmlReq.readyState == 4 && xmlReq.status == 200){
		var users=xmlReqUser.responseXML.getElementsByTagName('usuarios');	
		total = users.length;
		if (total>0) {
			users = users[0].childNodes;
			total = users.length;
			if (total>totalUser) {
			}
			totalUser = total;
		}
		var countNodesXML = node-1;
		if(xmlReq.responseXML.getElementsByTagName('actions').length>countNodesXML){
			var cad=xmlReq.responseXML.getElementsByTagName('actions');										
			if(cad[countNodesXML]!=null && cad[countNodesXML].getElementsByTagName('action').length>accionNum){	
				var isFinishedFirst = cad[countNodesXML].getElementsByTagName('action')[accionNum].getAttribute('firstUser');
				var isFinishedSecond = cad[countNodesXML].getElementsByTagName('action')[accionNum].getAttribute('secondUser');						
				if(isFinishedFirst!=null && isFinishedSecond!=null){
					accionNumPrev = parseInt(accionNum);
					accionNum = parseInt(accionNum)+1;
					txtNews="";
					if(accionNum==numBtn) {
						enableSolution();
					}
				//First answer, notify the other user
				}else if(isFinishedFirst!=null && isFinishedSecond==null && isFinishedFirst!='<?php echo $user;?>'){
					notifyTimerDown("<?php echo $LanguageInstance->get("txtTheUser");?>"+isFinishedFirst+"<?php echo $LanguageInstance->get("txtReplied");?>");
				}
			}
		}
	}
}
///////////////////////////////////////////////////
//desconnex, stop check for answers
desconn = function(){
				//$('#idfrm').attr('src','desconn.php?room=<?php echo $room;?>');
				$.ajax({
					type: 'GET',
					url: "desconn.php",
					data: {'room':'<?php echo $room;?>'},
					success: function(){
						  //
						}
					});
				if(posibleDesconn==1) clearInterval(intervalUpdateAction);
				hideButtons();
				hideText();
				//20121005 - abertranb - Go back to the selectUserAndRomm and disble onbeforeunload message
				salir = 1;

				<?php 
				if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) { 
					/*if(!empty($_SESSION[BasicLTIConstants::LAUNCH_PRESENTATION_RETURN_URL])){ 
						echo "window.location.replace('".$_SESSION[BasicLTIConstants::LAUNCH_PRESENTATION_RETURN_URL]."') ";
					}else*/
					echo "setTimeout(\"document.location.href='feedback.php'\",250);";                            
				} else { ?>
					setTimeout("document.location.href='selectUserAndRoom.php'",250);
					<?php  } ?>

				//END
			}
//hide all kind of stuff in page
hideButtons = function(){
	$('#steps').hide('fast');
	$('#tasks').hide('fast');
}
hideText = function(){
	notifyTimerDown('<?php echo $LanguageInstance->get("txtDesconnected")?>');
	$('#buttonDesconn').hide('slow');
}
			//20121004
			enableSolution = function() {
				//alert("Enabling solution!!!");
<?php if (!isset($_SESSION[USE_WAITING_ROOM]) || $_SESSION[USE_WAITING_ROOM]!=1) {?>
				salir=1;
<?php } ?>				
				clearInterval(intervalUpdateAction);
				if(intervalTimerAction!=null) clearInterval(intervalTimerAction);
				$('#next_task').attr('onclick',"showSolutionAndShowNextTask();return false;");	
				$('#next_task').addClass('active');
			}
			//END
//hide
//hide all kind of stuff in page
writeButtons = function(hideSeeSolution){
	$("#steps").addClass("steps_"+numBtn);
	var botones="";
	for(var i=0;i<numBtn;i++){
		j=i+1;
		if(numBtn==1){
                        if (!hideSeeSolution){
//                            botones+='<li id="sol1Item" class="solution" style="display:none;"><span class="lbl"><?php // echo $LanguageInstance->get("Solution");?> <img src="img/ok.png" alt="<?php // echo $LanguageInstance->get('Solution');?>" /></span></li><li id="next1Item" style="display:none;"><a href="#" class="next" id="next_task" title="<?php // echo $LanguageInstance->get('Next Task');?>"><span class="lbl"><?php // echo $LanguageInstance->get("See Solution");?></span></a></li><li class="step"><a href="#" class="active" id="step_'+i+'" title="step '+j+'" onclick="accion(\'btn'+i+'\','+i+');waitStep('+i+');showSolutionAndShowNextTask();document.getElementById(\'sol1Item\').style.display=\'inline\';document.getElementById(\'next1Item\').style.display=\'inline\';return false;"><span class="lbl"><?php // echo $LanguageInstance->get('See Solution');?></span></a></li>';
                            botones+='<li id="sol1Item" class="solution" style="display:none;"><span class="lbl"><?php echo $LanguageInstance->get("Solution");?> <img src="img/ok.png" alt="<?php echo $LanguageInstance->get('Solution');?>" /></span></li><li id="next1Item" style="display:none;"><a href="#" class="next" id="next_task" title="<?php echo $LanguageInstance->get('Next Task');?>"><span class="lbl"><?php echo $LanguageInstance->get("See Solution");?></span></a></li><li class="step"><a href="#" class="active" id="step_'+i+'" title="step '+j+'" onclick="if (timeline){try {	timeline.stop();} catch (e) {}}accion(\'btn'+i+'\','+i+');waitStep('+i+');showSolutionAndShowNextTask();document.getElementById(\'sol1Item\').style.display=\'inline\';document.getElementById(\'next1Item\').style.display=\'inline\';return false;"><span class="lbl"><?php echo $LanguageInstance->get('See Solution');?></span></a></li>';
                        }else{
                            botones+='<li class="step"><a href="#" class="active" id="step_'+i+'" title="step '+j+'" onclick="accion(\'btn'+i+'\','+i+');waitStep('+i+');showNextTask();document.getElementById(\'sol1Item\').style.display=\'inline\';document.getElementById(\'next1Item\').style.display=\'inline\';return false;"><span class="lbl"><?php echo $LanguageInstance->get('Next');?></span></a></li>';
                        }
		}else{
			if(i==0) botones+='<li class="step"><a href="#" class="active" id="step_'+i+'" title="step '+j+'" onclick="accion(\'btn'+i+'\','+i+');waitStep('+i+');return false;"><span class="lbl">'+j+'</span></a></li>';
			else botones+='<li class="step"><a href="#" id="step_'+i+'" title="step '+j+'" onclick="accion(\'btn'+i+'\','+i+');waitStep('+i+');return false;"><span class="lbl">'+j+'</span></a></li>';
		}
	}
	 if(numBtn>1) botones+='<li class="solution"><span class="lbl"><?php echo $LanguageInstance->get("Solution");?> <img src="img/ok.png" alt="<?php echo $LanguageInstance->get('Solution');?>" /></span></li><li><a href="#" class="next" id="next_task" title="<?php echo $LanguageInstance->get('Next Task');?>"><span class="lbl"><?php echo $LanguageInstance->get('See Solution');?></span></a></li>';
	$("#steps").html(botones);

	var tasksIt="<ul>";
	for(var i=1;i<=numNodes;i++){
		
		if(i<numExerc) tasksIt+='<li class="completed"><span class="lbl"><?php echo isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1?'':$LanguageInstance->get('Task');?> '+i+' <img src="img/ok.png" alt="completed" /></span></li>';
					if(i==numExerc) tasksIt+='<li class="active"><span class="lbl"><?php echo  isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1?'':$LanguageInstance->get('Task');?> '+i+'</span></li>';//<li class="arrow"></li>';
					if(i>numExerc) tasksIt+='<li><span class="lbl"><?php echo isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1?'':$LanguageInstance->get('Task');?> '+i+'</span></li>';
					if (i<numNodes) tasksIt+='<li class="arrow"></li>';
				}
				tasksIt+="</ul>";
				$('#tasks').html(tasksIt);
				
				//monta el iframe de inicio
				if(initHTMLB==null)
					$('#ifrmHTML').attr("src","<?php echo $path; ?>ejercicios/"+ExerFolder+"/"+initHTML+"?user=<?php echo $user;?>");
				else{
					if("<?php echo $user;?>"=="a")
					$('#ifrmHTML').attr("src","<?php echo $path; ?>ejercicios/"+ExerFolder+"/"+initHTML);
					else
						$('#ifrmHTML').attr("src","<?php echo $path; ?>ejercicios/"+ExerFolder+"/"+initHTMLB);
				}

				<?php 
				if (!isset($_SESSION[USE_WAITING_ROOM]) || $_SESSION[USE_WAITING_ROOM]==0) { ?>

				if(numExerc==1) 
					if('<?php echo $user;?>'=='a') {	
						<?php
						$fn = '';
						$sn = '';
						if( isset($_GET["userb"]) ){
							if($nameb!=null || $nameb!=""){
								$fnB = $nameb->fullname;
								$fnB = explode(" ",$fnB);
								$fn = $fnB[0];
								$sn = $fnB[1];
							}
						}
						?>
						$.colorbox({href:"waiting4user.php?fn=<?php echo $fn;?>&sn=<?php echo $sn; ?>",escKey:false,overlayClose:false,width:380,height:280,onLoad:function(){$('#cboxClose').hide();}});
					}
				<?php } ?>
				}  
//executes action->action.php writes in room.xml data got from user's activity (button pressed), shows next button
accion = function(id,number){
				//abertranb - 20120925 - If is not active you can't press
				if (!$('#step_'+number).hasClass('active')){
					return;
				}
				$.ajax({
					type: 'GET',
					url: "action.php",
					//OTOD 
					data: {'room':'<?php echo $room;?>','user':'<?php echo $user;?>','number':number,'nextSample':node,'tipo':'confirm'},
					dataType: "xml",
					statusCode: {
						404: function() {
							hideText();
							hideButtons();
							userDesconn=1;
						}
					},
					success: function(){
					  //
					}
				});
				$('#'+id).attr("disabled", "true");
				id = id.split("btn");
				id = parseFloat (id[1]);
				accionNum = id;
				id++;
			}
//Exercise finished, stop checking for answers' interval, shows next question

			//20121004 - Add - @abertranb 
			showSolutionAndShowNextTask = function() {
				showSolution();
				$('#next_task').attr('onclick',"");
				if(numNodes!=node){
					$('#next_task .lbl').html("<?php echo $LanguageInstance->get('Next Task');?>");
				}
				$('#ifrmHTML').attr("src","<?php echo $path; ?>ejercicios/"+ExerFolder+"/"+endHTML);
				showNextQuestion();
			}
			// END
                        
                        showNextTask = function(){
                            salir = 1;
                            pass2NextQuestion();
                        }
			
			showNextQuestion = function(){
				salir=1;
				clearInterval(intervalUpdateAction);
				if(intervalTimerAction!=null) clearInterval(intervalTimerAction);
				
				//muestra el iframe de la solución
				if(numNodes!=node){
					$('#next_task').attr('onclick',"showEvaluateTaskModal(0);pass2NextQuestion();return false;");
					if(document.getElementById('next1Item')) document.getElementById('next1Item').style.display='inline';
				}else{
					<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) { ?>
//					showGoodbyeMessage();
					<?php } ?>
					$('#next_task .lbl').html("<?php echo $LanguageInstance->get('Click to finish');?>");
					<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) { ?>
					$('#next_task').attr('onclick',"showEvaluateTaskModal(1);return false;");
					<?php } else {?>
					$('#next_task').attr('onclick',"showFinishedAlert();return false;");
					<?php } ?>

				}
				if (intervalIfNextQuestionAnswered) {
					clearInterval(intervalIfNextQuestionAnswered);
				}
				intervalIfNextQuestionAnswered = setInterval('checkIfPass2NextQuestion("<?php echo $user;?>","<?php echo $room;?>")',750);
				
			}


			checkIfPass2NextQuestion = function(){
                                checkIfExternalToolClosed();
				$.ajax({
					type: 'GET',
					url: "check.php?room=<?php echo $room; ?>&t=4",
					data: {
					},
					dataType: "xml",
					statusCode: {
						404: function() {
							hideText();
							hideButtons();
							userDesconn=1;
						}
					},
					success: function(xml){
						var cad = $(xml).find('actions');
						/*var isFirstUserEnd = cad[cad.length-1].getAttribute('firstUserEnd');
						var isSecondUserEnd = cad[cad.length-1].getAttribute('secondUserEnd');*/
						if (cad.length>=node) {
							var isFirstUserEnd = cad[node-1].getAttribute('firstUserEnd');
							var isSecondUserEnd = cad[node-1].getAttribute('secondUserEnd');
							if(isFirstUserEnd!=null && isSecondUserEnd==null && isFirstUserEnd!='<?php echo $user;?>'){
								notifyTimerDown("<?php echo $LanguageInstance->get("txtTheUser");?>"+isFirstUserEnd+"<?php echo $LanguageInstance->get("txtEndTask");?>");
								clearInterval(intervalIfNextQuestionAnswered);
							}
						}
					}
				})
			}

			pass2NextQuestion = function(){
                                checkIfExternalToolClosed();
				$.ajax({
					type: 'GET',
					url: "action.php",
					data: {'room':'<?php echo $room;?>','user':'<?php echo $user;?>','nextSample':node,'tipo':'SetNextQuestion'},
					dataType: "xml",
					statusCode: {
						404: function() {
							hideText();
							hideButtons();
							userDesconn=1;
						}
					},
					success: function(){
						notifyTimerDown("<?php echo $LanguageInstance->get("txtWaiting4UserEndTask");?>");
						if(intervalIfNextQuestion){ clearInterval(intervalIfNextQuestion);}
						intervalIfNextQuestion = setInterval('checkIfPass2NextQuestionToJump("<?php echo $user;?>","<?php echo $room;?>")',500);
						$('#next_task').removeClass("active");
					}
				});
			}

			registerActionNextTask = function(){
				$.ajax({
					type: 'GET',
					url: "action.php",
					data: {'room':'<?php echo $room;?>','user':'<?php echo $user;?>','node':node,'tipo':'register_action_user_next_task'},
					dataType: "xml",
					success: function(){
					}
				});
			}
			checkIfPass2NextQuestionToJump = function(){
				$.ajax({
					type: 'GET',
					url: "check.php?room=<?php echo $room; ?>&t=5",
					data: {
					},
					dataType: "xml",
					statusCode: {
						404: function() {
							hideText();
							hideButtons();
							userDesconn=1;
						}
					},
					success: function(xml){

						var cad = $(xml).find('actions');
						/*var isFirstUserEnd = cad[cad.length-1].getAttribute('firstUserEnd');
						var isSecondUserEnd = cad[cad.length-1].getAttribute('secondUserEnd');*/
						var isFirstUserEnd = cad[node-1].getAttribute('firstUserEnd');
						var isSecondUserEnd = cad[node-1].getAttribute('secondUserEnd');
						if(isFirstUserEnd!=null && isSecondUserEnd!=null){ 
							clearInterval(intervalIfNextQuestion);
							<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) { ?>
								ExerFolder = nextSample;
								node = node+1;
								numOfChecksSameNode = 0;
								clearInterval(intervalUpdateLogin);
								registerActionNextTask();
								getInitXML();
								//Register action
								
							<?php } else {?>
							location.href=classOf+'.php?room=<?php echo $room;?>&user=<?php echo $user;?>&nextSample='+nextSample+'&node=<?php echo $node+2;?>&data=<?php echo $data;?>';
							<?php } ?>
						} 
						<?php /*if (false && isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) { ?>
						if (node<cad.length-1) {
							if(isFirstUserEnd==null || isSecondUserEnd==null){ 
							//If in 5 tryes then 
								numOfChecksSameNode++;
								if (numOfChecksSameNode>5) {
									if (isFirstUserEnd==null) {
										isFirstUserEnd = "a";
									}
									else{
										isSecondUserEnd = "b";
									} 

								}
							}
						}
						<?php }*/ ?>
					}
				})
			}


showVideoChatAndGoodbyeMessage = function(){
    parent.$.fn.showVideochatEvent();
    showGoodbyeMessage();
}

showFinishedAlert = function(){
	endOfTandem=1;
	$.colorbox({href:"end.php?room=<?php echo $room;?>",escKey:true,overlayClose:false,onLoad:function(){$('#cboxClose').hide();}});
	try {
		
		if (intervalIfNextQuestionAnswered) {
			clearInterval(intervalIfNextQuestionAnswered);
		}
		if (intervalUpdateLogin) {
			clearInterval(intervalUpdateLogin);
		}
		if (intervalTimerAction) {
			clearInterval(intervalTimerAction);
		}
		if (intervalIfNextQuestion) {
			clearInterval(intervalIfNextQuestion);
		}
		if (intervalIfNextQuestionAnswered) {
			clearInterval(intervalIfNextQuestionAnswered);
		}
		if (intervalUpdateAction) {
			clearInterval(intervalUpdateAction);
		}
	} catch (e) {

	}

}
//shows central image
showImage = function(id){
	$('#image').show('slow');
}

<?php if (!isset($_SESSION[USE_WAITING_ROOM]) || $_SESSION[USE_WAITING_ROOM]==0) {?>
	getInitXML();
<?php } else { 
//Store in database
	//Lets go to insert the current tandem data
	$user_language = $_SESSION[LANG];
	$user_obj = $_SESSION[CURRENT_USER];
	$other_language = ($user_language == "es_ES") ? "en_US" : "es_ES";
	$id_partner = $tandem['id_user_guest']==$user_obj->id?$tandem['id_user_host']:$tandem['id_user_guest'];
	$id_feedback = $gestorBDSample->createFeedbackTandem($tandem['id'], 0, $user_obj->id, $user_language, $id_partner, $other_language);
	if (!$id_feedback) {
		die ($LanguageInstance->get('There are a problem storing data, try it again'));		
	}
	$_SESSION[ID_FEEDBACK] = $id_feedback;
	//Put check sesssion to false
	$gestorBDSample->updateTandemSessionNotAvailable($tandem['id']);
	

	?>
		//$.colorbox({href:"waitingForVideoChatSession.php?id=<?php echo $_SESSION[CURRENT_TANDEM];?>",escKey:false,overlayClose:false,width:380,height:280});
		var windowVideochat = false;
		var windowStartTandem = false;
		var windowNotificationTandem = false;
		var windowSayGoodbye = false;
		var windowMessage = false;
		var intervalVideochat = false;
		var widthWindowVideochat = $( window ).width()*0.98;
		var heightWindowVideochat = $( window ).height()*0.98;
		$(document).ready(function(){
			$('#btnMessageShowVideochat').click(function(event) {
				showVideochatAction();
			});
			var myButtons = [
			   {
			   id: "btn_minimize_videochat_close",           // required, it must be unique in this array data
			   title: "<?php echo $LanguageInstance->get('Hide Videochat')?>",   // optional, it will popup a tooltip by browser while mouse cursor over it
			   //clazz: "",           // optional, don't set border, padding, margin or any style which will change element position or size
			   //style: "",                    // optional, don't set border, padding, margin or any style which will change element position or size
			   image: "js/window/img/close.png",    // required, the url of button icon(16x16 pixels)
			   callback:                     // required, the callback function while click it
			      function(btn, wnd) {
			         hideVideochat(wnd, true);
			      }
			   },
			   {
			   id: "btn_minimize_videochat",           // required, it must be unique in this array data
			   title: "<?php echo $LanguageInstance->get('Hide Videochat')?>",   // optional, it will popup a tooltip by browser while mouse cursor over it
			   clazz: "window_icon_button_88_13",           // optional, don't set border, padding, margin or any style which will change element position or size
			   //style: "",                    // optional, don't set border, padding, margin or any style which will change element position or size
			   image: "js/window/img/<?php echo $user_language=='es_ES'?'ver':'view'?>_tandem.gif",    // required, the url of button icon(16x16 pixels)
			   callback:                     // required, the callback function while click it
			      function(btn, wnd) {
			         hideVideochat(wnd, true);
			      }
			   }
			];
			<?php
			 $urlForVideoChat = "ltiConsumer.php?id=".$_SESSION[OPEN_TOOL_ID];
			 if (file_exists(dirname(__FILE__).'/external_integration.php')) {
			 	include_once(dirname(__FILE__).'/external_integration.php');
			 }
			?>
			windowVideochat = $.window({
			   title: "",
			   url: "<?php echo $urlForVideoChat?>",
			   width: widthWindowVideochat,
			   height: heightWindowVideochat,
			   maxWidth: $( document ).width(),
			   maxHeight: $( document ).height(),
			   closable: false,
			   draggable: true,
			   resizable: true,
			   animationSpeed: 200,
			   maximizable: false,
			   minimizable: false,
			   showFooter: false,
			   showRoundCorner: true,
   			   custBtns: myButtons
			});

			<?php if (!$_SESSION[FORCE_SELECT_ROOM]) {?>
			windowNotificationTandem = $.window({
			   title: "",
			   url: "notificationStartTandem.php",
			   width: 310,
			   height: 130,
			   maxWidth: 400,
			   maxHeight: 400,							  
			   draggable: false,
			   closable: true,							   
			   maximizable: false,
			   minimizable: false,
			   showFooter: true,
			   modal: true,
			   showRoundCorner: true				   			   
			});
			<?php } ?>

			intervalVideochat = setInterval(function() {checkVideochat(windowVideochat)},2500);
			createVideochatButtons(windowVideochat, widthWindowVideochat, heightWindowVideochat);
			$(".window_function_bar").width("120px");

			//tmp patch
			$("#window_0").css({top:'1px'});
			/*$('#show_videochat').hover(function() {
			    $('#alertShowVideoXat').toggle();
		    });*/



		});
		function showGoodbyeMessage() {
			windowSayGoodbye = $.window({
				title: "",
				url: "notificationSayGoodbye.php",
				width:310,
				height: 250,
				maxWidth: 800,
				maxHeight: 400,
                                y: $( window ).height()-500,
                                x: $( window ).width()-320,
				draggable: true,
				closable: true,
				maximizable: false,
				minimizable: false,
				showFooter: true,
				modal: true,
				showRoundCorner: true
			});
			//Stop timer
			if (timeline) {
				try {	timeline.stop();} catch (e) {}
			}
		}

		function messageWindow(urlShow, is_videochat) {
			if (windowMessage) {
				windowMessage.close();
			}
			var myButtons = [
			   {
			   id: "btn_close_start_tandem",           // required, it must be unique in this array data
			   title: "<?php echo $LanguageInstance->get('Maximize')?>",   // optional, it will popup a tooltip by browser while mouse cursor over it
			   image: "js/window/img/maximize.png",    // required, the url of button icon(16x16 pixels)
			   callback:                     // required, the callback function while click it
			      function(btn, wnd) {
			         if (is_videochat) {
			         	showVideochat(windowVideochat, widthWindowVideochat, heightWindowVideochat);
			         }else {
			         	hideVideochat(windowVideochat, true);
			         }
			      }
			   }
			];
                        
			windowMessage = $.window({
							   title: "",
							   url: urlShow,
							   width: is_videochat?210:180,
							   y: $( window ).height()-235,
							   x: $( window ).width()-(is_videochat?220:190),
							   height: 230,
							   maxWidth: 500,
							   maxHeight: 400,
							   closable: false,
							   draggable: true,
							   resizable: true,
							   maximizable: false,
							   minimizable: false,
							   showFooter: false,
							   modal: true,
							   showRoundCorner: true,
   							   modalOpacity: 0.5,
   							   custBtns: myButtons
							});

		}

		function hideVideochat(winVideochat, changeButtons) {
			/*var styleObj = {};
					styleObj.width = 1;
					styleObj.height = 1;
				winVideochat.animate(styleObj, 200, 'swing', function() {
					adjustHeaderTextPanelWidth();
				});*/
			winVideochat.resize(1,1);
			messageWindow('showVideochat.php?is_videochat=1', true);
			
			if (changeButtons) {
				$('#hide_videochat').hide();
				$('#show_videochat').show();
				//$('#alertShowVideoXat').show();
			}
		}

		function showVideochat(winVideochat, widthWinVideochat, heightWinVideochat) {
			messageWindow('showVideochat.php?is_videochat=0', false);
			winVideochat.resize(widthWindowVideochat, heightWindowVideochat);
		}

		function createVideochatButtons(winVideochat, widthWinVideochat, heightWinVideochat) {
			$('#videochatButtons').html('<input type="button" id="show_videochat" class="tandem-btn" value="<?php echo $LanguageInstance->get('Show Videochat')?>"/><!--div id="alertShowVideoXat" style="cursor: pointer"><img src="img/videoXat.gif"> </div-->'+
				'<input type="button" id="hide_videochat" class="tandem-btn" value="<?php echo $LanguageInstance->get('Hide Videochat')?>"/>');
			$('#hide_videochat').hide();
			$('#hide_videochat').click({winVideochat: winVideochat}, function(event) {
				hideVideochat(event.data.winVideochat, true);
			});
			$('#show_videochat').click({winVideochat: winVideochat, widthWinVideochat: widthWinVideochat, heightWinVideochat: heightWinVideochat}, function(event) {
				showVideochat(event.data.winVideochat, event.data.widthWinVideochat, event.data.heightWinVideochat);
				$('#show_videochat').hide();
				$('#hide_videochat').show();
				$('#alertShowVideoXat').hide();
			});
			$('#alertShowVideoXat').click({winVideochat: winVideochat, widthWinVideochat: widthWinVideochat, heightWinVideochat: heightWinVideochat}, function(event) {
				showVideochat(event.data.winVideochat, event.data.widthWinVideochat, event.data.heightWinVideochat);
				$('#show_videochat').hide();
				$('#hide_videochat').show();
				$('#alertShowVideoXat').hide();
			});
		}

		function showVideochatAction() {
			showVideochat(windowVideochat, widthWindowVideochat, heightWindowVideochat);
		}

		
	        /*jQuery("#modal-content-video").modal(
	            {
	                escClose: true,
	                opacity: 100,
	                minHeight:jQuery( document ).height()<400?(jQuery( document ).height()*0.80):400,
	                minWidth: jQuery( document ).width()<700?(jQuery( document ).width()*0.80):600,
	                onShow: function (dialog) {
	                },
	                onClose: function (dialog) {
	                    jQuery("#iframe-modal-video").attr("src","about:blank");
	                    jQuery.modal.close();
	                }
	            });*/
	    var connection_success = false; 
	    <?php 
	    /*if($_GET['user']=="a") $userR = "user=b"; else $userR = "user=a";
	    $request_uri = str_replace("user=".$_GET['user'],$userR,$_SERVER['REQUEST_URI']);*/
	    $request_uri = $_SERVER['REQUEST_URI'];
	    
	    ?>
		function checkVideochat( winV){
			$.ajax({
				type: 'POST',
				url: "api/checkSession.php",
				data : {
					   id : '<?php echo $_SESSION[CURRENT_TANDEM];?>',
					   sent_url : '<?php echo base64_encode("http://".$_SERVER["SERVER_NAME"].$request_uri);?>',
					   userab : '<?php echo $_GET["user"]; ?>'
				},
				dataType: "JSON",
				success: function(json){	
					if(json  &&   json.result !== "undefined" && json.result == "ok"){
						if (intervalVideochat){
							clearInterval(intervalVideochat);	
						}
						var myButtons = [
						   {
						   id: "btn_close_start_tandem",           // required, it must be unique in this array data
						   title: "<?php echo $LanguageInstance->get('Hide Videochat')?>",   // optional, it will popup a tooltip by browser while mouse cursor over it
						   //clazz: "",           // optional, don't set border, padding, margin or any style which will change element position or size
						   //style: "",                    // optional, don't set border, padding, margin or any style which will change element position or size
						   image: "js/window/img/maximize.png",    // required, the url of button icon(16x16 pixels)
						   callback:                     // required, the callback function while click it
						      function(btn, wnd) {
						         startTandemVC();
						      }
						   }
						];
						/*$.colorbox({iframe: true,width:380,height:280, href: 'connectedPartnerStartTandem.php'});
						$(document).bind('cbox_closed', function(){
						  startTandemVC();
						});*/
						windowStartTandem = $.window({
							   title: "",
							   url: "connectedPartnerStartTandem.php",
							   width: 400,
							   //y: $( document ).height()*0.1,
							   height: 400,
							   maxWidth: 500,
							   maxHeight: 400,
							   closable: true,
							   draggable: false,
							   resizable: true,
							   maximizable: false,
							   minimizable: false,
							   showFooter: true,
							   modal: true,
							   showRoundCorner: true,
   			   				   custBtns: myButtons
				   			   
							});
					}

					if(json  &&   json.emailsent !== "undefined" && json.emailsent == 1){
					//if 30 seconds have passed since we are waiting for the partner, then we show this alert
						sendEmailNotification = $.window({
						   title: "",
						   content: '<p style="padding:15px"><?php echo $LanguageInstance->get('thirty_second_notification_message');?></p>',
						   width: 400,
						   //y: $( document ).height()*0.1,
						   height: 200,
						   maxWidth: 500,
						   maxHeight: 400,
						   closable: true,
						   draggable: false,
						   resizable: true,
						   maximizable: false,
						   minimizable: false,
						   showFooter: true,
						   modal: true,
						   showRoundCorner: true,
			   				   custBtns: myButtons			   			   
						});	
					}



				}
			});
		}
		function startTandemVC() {
			connection_success = true;
			$(document).unbind('cbox_closed');
			//$.colorbox.close();
			windowStartTandem.close();
//			hideVideochat(windowVideochat, false);
                        messageWindow('showVideochat.php?is_videochat=0', false);
			getInitXML();
		}
		jQuery.fn.extend({
			startTandemVCEvent: function () {
				$('#showMessageInit').hide();
				startTandemVC();
			}
		});

		jQuery.fn.extend({
			hideVideochatEvent: function () {
				hideVideochat(windowVideochat, true);
			}
		});
		jQuery.fn.extend({
			showVideochatEvent: function () {				
				showVideochat(windowVideochat, widthWindowVideochat, heightWindowVideochat);
			}
		});		
		
		jQuery.fn.extend({
			hideSoundNotification: function () {				
				windowNotificationTandem.close();			
			}
		});


		jQuery.fn.extend({
			hideSayGoodbye: function () {
				windowSayGoodbye.close();
			}
		});

		jQuery.fn.extend({
			hideSayGoodbyeAndRedirect: function () {
				windowSayGoodbye.close();
                                window.location ='feedback.php';
			}
		});

<?php
} 
?>
//prevents from closing
window.onbeforeunload = function() {
<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>	
	if(salir==0) {
registerActionNextTask();
		return "<?php echo $LanguageInstance->get('Do you want to leave Tandem?. To send feedback to your tandem partner click on Review form (in tandem window)');?>";
}
<?php  } else {?>	
	if(salir==0) return "<?php echo $LanguageInstance->get('Do you want to leave Tandem?. You will disconnect from your tandem partner');?>";
<?php } ?>	
}
getUsersDataXml('<?php echo $user?>','<?php echo $room?>');

});

</script>
</head>

<body class="page">

	<!-- accessibility -->
	<div id="accessibility">
		<a href="#content" accesskey="s" title="Acceso directo al contenido">Acceso directo al contenido</a>
	</div>
	<!-- /accessibility -->

	<div id="wrapper">

		<noscript>
			<div class="alertjs-container">
				<div class="alertjs">
					<h5>JavaScript no está habilitado en tu navegador</h5>
					<p>Para usar Tandem, activa JavaScript o actualiza tu navegador con una versión que acepte JavaScript.</p>
				</div>
			</div>
		</noscript>

		<div id="head-container">
			<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
			<div id="videochatButtons"></div>
			<?php } ?>
			<!-- header -->
			<div id="header">
				<div id="logo">
					<div id="showNews" class="modal"></div>
					<a href="#" title="Inicio Tandem"><img src="img/logo_tandem_top.png" alt="logo Tandem" /></a>
				</div>
				<div id="title">
					<div class="title_wrap">
						<h1><?php echo $title_exercise?></h1>
						<span class="lnk_wrap"><a href="#content_info" id="lnk_info" title="info" class="infotip"><span class="hidden">info</span></a></span>
					</div>
					<div id="content_info">
						<div class="col_1" id="textosExerc"><p><strong>Welcome to SpeakApps - Tandem</strong></p><p>This is a description of the tasks to be performed.</p><p>Tandem exercises require you to be connected in a <strong>common space</strong> to be <strong>performed simultaneously</strong>. To advance in the different parts of the exercise one of you must make a request through the buttons of each task which must be confirmed by your partner.</p></div>
						<div class="col_2">
							<h3 id="infoT1t"></h3>
							<p id="infoT1txt"></p>
							<h3 id="infoT2t"></h3>
							<p id="infoT2txt"></p>
							<h3 id="infoT3t"></h3>
							<p id="infoT3txt"></p>
						</div>
						<div class="col_2">
							<h3 id="infoT4t"></h3>
							<p id="infoT4txt"></p>
							<h3 id="infoT5t"></h3>
							<p id="infoT5txt"></p>
							<h3 id="infoT6t"></h3>
							<p id="infoT6txt"></p>
						</div>
					</div>
				</div>

				<div id="users">
					<div class="user">
						<div class="details">
							<span class="name" id="name_person_a"></span>
							<?php if (!isset($_SESSION[USE_WAITING_ROOM]) || $_SESSION[USE_WAITING_ROOM]!=1) {?>
							<a href="#info_user_1" id="lnk_user_1" class="infotip" data-rel="<?php echo $LanguageInstance->get('hide_profile')?>"><span><?php echo $LanguageInstance->get('show_profile')?></span></a>
							<?php } ?>
						</div>
						<div id="image_person_a" class="photo" alt="user 1 photo"></div>
						<div class="user_info" id="info_user_1">
						<?php if (!isset($_SESSION[USE_WAITING_ROOM]) || $_SESSION[USE_WAITING_ROOM]!=1) {?>
							<span class="social" title="skype" id="chat_person_a">SkypeUser <span class="icon skype"></span></span>
						<?php } ?>
						</div>
						<a href="#" id="lnk_quit" <?php echo (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1)?'style="right:-90px"':''?> onclick="desconn();"><?php echo $LanguageInstance->get((isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1)?'Review form':'quit')?></a>
					</div>
					<div class="user">
						<div class="details">
							<span class="name" id="name_person_b"></span>
							<?php if (!isset($_SESSION[USE_WAITING_ROOM]) || $_SESSION[USE_WAITING_ROOM]!=1) {?>
							<a href="#info_user_2" id="lnk_user_2" class="infotip" data-rel="<?php echo $LanguageInstance->get('hide_profile')?>"><span><?php echo $LanguageInstance->get('show_profile')?></span></a>
							<?php } ?>
						</div>
						<div id="image_person_b" class="photo" alt="user 2 photo"></div>
						<div class="user_info" id="info_user_2">
						<?php if (!isset($_SESSION[USE_WAITING_ROOM]) || $_SESSION[USE_WAITING_ROOM]!=1) {?>
							<span class="social" title="skype" id="chat_person_b">SkypeUser <span class="icon skype"></span></span>
						<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<!-- /header -->
		</div>

		<div id="task-container">
			<div id="tasks"></div>
		</div>

		<!-- main-container -->
		<div id="main-container">
			<!-- main -->
			<div id="main">
				<!-- tarea de X pasos -->
				<ul id="steps"></ul>

				<div id="timeline" style="display:none;">
					<div class="lbl"><?php echo $LanguageInstance->get('task_remaining_time')?></div>
					<div class="clock" id="clock"><span class="mm">00</span>:<span class="ss">00</span></div>
					<div class="linewrap"><div class="line"></div></div>
				</div>
				<div id="content">
					<?php 
				if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) { ?>
						<div  id="showMessageInit" class="message">
							<?php echo $LanguageInstance->get('You should to be connected with your partner using videochat')?>. <input type="button" id="btnMessageShowVideochat" class="tandem-btn" value="<?php echo $LanguageInstance->get('Show Videochat')?>"/></div>
					<?php  } ?>
					<iframe name='ifrmHTML' id="ifrmHTML" class="iframe" src="" frameborder="0" border="0"></iframe>
				</div>
			</div>
			<!-- /main -->
		</div>
		<!-- /main-container -->    	
	</div>

	<!-- footer -->
	<div id="footer-container-exercise">
		<div id="footer">
			<div class="footer-logos">
				<div style="float: left; margin-top: 0pt; text-align: justify; width: 600px;"><span style="font-size:9px;">This project has been funded with support from the Lifelong Learning Programme of the European Commission.  <br />
This site reflects only the views of the authors, and the European Commission cannot be held responsible for any use which may be made of the information contained therein.</span>
</div>
		 &nbsp;	<img src="css/images/EU_flag.jpg" alt="" />
				<!--img src="img/logo_LLP.png" alt="Lifelong Learning Programme" />
				<img src="img/logo_EAC.png" alt="Education, Audiovisual &amp; Culture" /-->
				<img src="img/logo_speakapps.png" alt="Speakapps" />
			</div>
		</div>
	</div>

	<!-- modals -->
	<div id="modal-start-task" class="modal">
		<p class="msg">This is a timer based task, please confirm to start: It will begin when both you and your partner confirm by clicking the “Start task” button.</p>
		<p><a href='#' onclick="StartTandemTimer();return false;" id="lnk-start-task" class="btn">Start Task</a></p>
	</div>

	<div id="modal-end-task" class="modal">
		<p class="msg">Time up!</p>
                <p><a href='#' onclick="$('#next1Item').css('display', 'block')" id="lnk-end-task" class="btn simplemodal-close">Close</a></p>
	</div>
        <!--Mood Modal--> 
        <div id="moodModal" class="modal">
<!--                <div class="modal-header">
                    <button type="button" class="simplemodal-close mood-img" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>-->
                <p class="msg"><?php echo $LanguageInstance->get('Hi!')?></p>
                <p class="msg"><?php echo $LanguageInstance->get('How do you feel today?')?></p>
                <p class="msg" style="min-height: 150px;">
                    <img class="simplemodal-close mood-img" src="images/smile.png" onclick="emojiSelected(1)" />
                    <img class="simplemodal-close mood-img" src="images/neutral.png" onclick="emojiSelected(2)" />
                    <img class="simplemodal-close mood-img" src="images/sad.png" onclick="emojiSelected(3)" />
                </p>
	</div>
        <!--Evaluate Task Modal--> 
        <div id="evaluateTaskModal" class="modal">
<!--                <div class="modal-header">
                    <button type="button" id="closeModalBtn" class="mood-img" data-dismiss="modal" aria-label="Close" onclick="closeModal(0)"><span aria-hidden="true">&times;</span></button>
                </div>-->
                <p class="msg"><?php echo $LanguageInstance->get('I enjoyed doing this task (1=not at all 6=a lot)')?></p>
                <div id="rating-square-enjoyed-div">
                    <select id="rating-square-enjoyed" class="rating-square">
                        <option value=""></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                </div>
                <p class="msg"><?php echo $LanguageInstance->get('I felt nervous doing the task (1=not at all 6=a lot)')?></p>
                <div id="rating-square-nervous-div">
                    <select id="rating-square-nervous" class="rating-square">
                        <option value=""></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                </div>
                <p class="msg"><?php echo $LanguageInstance->get('This task was (1 Very bad - 6 excellent)')?></p>
                <div id="rating-square-task-valoration-div">
                    <select id="rating-square-task-valoration" class="rating-square">
                        <option value=""></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                </div>
                <p class="msg"><?php echo $LanguageInstance->get('The thing that has challenged me the most is:')?></p>
                <form role="form">
                    <div class="form-group">
                        <textarea class="form-control" rows="10" id="comment"></textarea>
                        <input id="enjoyed" type="hidden" value="0"> 
                        <input id="nervous" type="hidden" value="0"> 
                        <input id="task-valoration" type="hidden" value="0"> 
                        <input id="moodBtn" type="button" value="Enviar" onclick=""/>
                    </div>
                </form>
	</div>
        <!-- Contact Modal -->
        <div id="contact-user-modal" class="modal">
                <div class="modal-header">
                    <button type="button" class="simplemodal-close mood-img" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="msg"><?php echo $LanguageInstance->get('Your partner has been disconnected, to continue this tandem you can contact with him here')?></p>
                    <form role="form">
                        <div>
                            <label for="subject" class="contact-user-modal-label"><?php echo $LanguageInstance->get('Subject') ?>:</label>
                            <input type="text" id="contact-email-subject" value="<?php echo $LanguageInstance->get('Tandem disconnected. Partner contact details to continue the task.'); ?>">
                        </div>
                        <br/>
                        <div>
                            <label for="comment" class="contact-user-modal-label"><?php echo $LanguageInstance->get('Comment') ?>:</label>
                            <textarea rows="5" id="contact-email-comment"><?php echo $LanguageInstance->get('You have been disconnected from tandem. You can contact to your partner at') ?> <?php echo $gestorBDSample->getUserEmail($_SESSION['current_user']->id); ?> <?php echo $LanguageInstance->get('to continue the task.') ?></textarea>
                        </div>
                        <br/>
                        <div>
                            <input type="checkbox" id="allow" onclick="allowClick()">
                            <label for="allow"><?php echo $LanguageInstance->get('Check to allow sharing your contact info with your partner') ?>:</label>
                        </div>
                        <div id="contact-email-modal-warning" class="alert alert-warning" style="display:none;">
                            <?php echo $LanguageInstance->get('Missing parameters') ?>
                        </div>
                    </form>
                </div>
                <br/>
                <div class="modal-footer">
                    <button type="button" id="send-contact-email-btn" class="btn btn-primary disabled" disabled onclick="sendContactEmail()"><?php echo $LanguageInstance->get('Send') ?></button>
                    <button type="button" id="send-contact-email-cancel-btn" class="simplemodal-close mood-img" data-dismiss="modal" aria-label="Close"><?php echo $LanguageInstance->get('Cancel') ?></button>
                </div>
	</div>
        <!-- /Contact Modal -->
        <!-- /modals -->
	<!-- /footer -->
	<script type="text/javascript" src="js/tandem.js?version=1"></script>
	<?php if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1) {?>
	<!--link media="screen" rel="stylesheet" href="css/jquery_modal.css" /-->
	<script type="text/javascript" src="js/window/jquery.window.min.js"></script>
	<?php }?>
</body>

</html>
