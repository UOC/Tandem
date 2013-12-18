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
	<script type="text/javascript" src="js/jquery.simplemodal.1.4.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.iframe-auto-height.plugin.1.7.1.min.js"></script>
	<script type="text/javascript" src="js/jquery.infotip.min.js"></script>
	<script type="text/javascript" src="js/jquery.timeline-clock.min.js"></script>
	<?php include_once dirname(__FILE__).'/js/google_analytics.php'?>
	
	<script>
//timer
		var intTimerNow;
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
//colorbox js actionexample3
			notifyTimerDown = function(id){
				if($.trim(txtNews)!=$.trim(id)){
					$('#showNews').html(id);
					$('#showNews').fadeIn(1000).slideDown("fast");
					$("#showNews").delay(3000).fadeOut(1000).slideUp("fast");
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
//xml request for iexploiter/others 		
			if (window.ActiveXObject) xmlReq = new ActiveXObject("Microsoft.XMLHTTP");
			else xmlReq = new XMLHttpRequest();
//get data from dataROOM.xml->initializes exercise values


<?php
require_once(dirname(__FILE__).'/classes/constants.php');
//MODIFIED - 20120927 - abertran to avoid error  A session had already been started - ignoring session_start()
if(!isset($_SESSION)) {
	session_start();
}
// ORIGINAL
// session_start();
// END


$path = '';
if (isset($_SESSION[TANDEM_COURSE_FOLDER])) $path = $_SESSION[TANDEM_COURSE_FOLDER].'/';
?>

			getInitXML = function(){
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
							$("#infoT"+i+"t").html("Task "+i);
							$("#infoT"+i+"txt").html(txtInfoTask);
						}

						if(<?php echo $node+1;?><=numNodes){
							classOf=cad[<?php echo $node+1;?>].getAttribute("classOf");
							nextSample=cad[<?php echo $node+1;?>].getAttribute("currSample");
						}
						numExerc=<?php echo $node;?>;
						numUsers=cad[<?php echo $node;?>].getAttribute("numUsers");
						numBtn=cad[<?php echo $node;?>].getAttribute("numBtns");
						
						//timer
						isTimerOn = cad[<?php echo $node;?>].getAttribute("timer");
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
						initHTML = cad[<?php echo $node;?>].getAttribute("initHTML");
						initHTMLB = cad[<?php echo $node;?>].getAttribute("initHTMLB");
						endHTML = cad[<?php echo $node;?>].getAttribute("endHTML");
						textE=cad[<?php echo $node;?>].getElementsByTagName("textE")[0].childNodes[0].data;
						getXML("<?php echo $user;?>","<?php echo $room;?>");
						intervalUpdateLogin = setInterval('getXMLDone("<?php echo $user;?>","<?php echo $room;?>")',500);
	//thread is so quick...
						writeButtons();
						setTimeout(function(){notifyTimerDown('<?php echo Language::get('txtWaiting4User')?>');},250);
						// why is this line here?
							//hideButtons();
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
				intervalTimerAction = setInterval(timerChecker,1000);
			}
			timerChecker = function(){
					$.ajax({
					  type: 'GET',
					  url: "<?php echo $room; ?>.xml",
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
						var isFinishedFirst = cad[<?php echo $node-1;?>].getAttribute('firstUser');
						var isFinishedSecond = cad[<?php echo $node-1;?>].getAttribute('secondUser');
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
				  data: {'room':'<?php echo $room;?>','user':'<?php echo $user?>','nextSample':'<?php echo $node;?>','tipo':'confirmPreTimer'},
				  dataType: "xml"
				});
			}		
			//acabaTiempo!			
			accionTimer = function(){
				$.ajax({
				  type: 'GET',
				  url: "action.php",
				  data: {'room':'<?php echo $room;?>','numBtn':numBtn,'user':'<?php echo $user?>','nextSample':'<?php echo $node;?>','tipo':'confirmTimer'},
				  dataType: "xml"
				});
				showSolutionAndShowNextTask();
				//$('#ifrmHTML').attr("src","<?echo $path; ?>ejercicios/<?php echo $ExerFolder;?>/"+endHTML);
			}
//timer

			
			processInitXml = function(){
				if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
					//extract data
					//var lng=xmlReq.responseXML.getElementsByTagName('exe')[0].getAttribute("lang");
					//script.src = "lang/"+lng+".js";
					//script.type = 'text/javascript';
					//body.appendChild(script);
					var cad=xmlReq.responseXML.getElementsByTagName('nextType');
					numNodes=cad.length-1;
					if(<?php echo $node+1;?><=numNodes){
						classOf=cad[<?php echo $node+1;?>].getAttribute("classOf");
						nextSample=cad[<?php echo $node+1;?>].getAttribute("currSample");
					}
					numExerc=<?php echo $node;?>;
					numUsers=cad[<?php echo $node;?>].getAttribute("numUsers");
					numBtn=cad[<?php echo $node;?>].getAttribute("numBtns");
					textE=cad[<?php echo $node;?>].getElementsByTagName("textE")[0].childNodes[0].data;
					getXML("<?php echo $user;?>","<?php echo $room;?>");
					intervalUpdateLogin = setInterval('getXMLDone("<?php echo $user;?>","<?php echo $room;?>")',500);
//thread is so quick...
					writeButtons();
					setTimeout(function(){notifyTimerDown('<?php echo Language::get('txtWaiting4User')?>');},150);
					hideButtons();
				}
			}
//Initializes & creates users node in room's xml			
			getXML = function(user,room){
				var url="createUser.php";
				var params="user="+user+"&room="+room;
				xmlReq.onreadystatechange = processXml;
				xmlReq.timeout = 100000;
				xmlReq.overrideMimeType("text/xml");
				xmlReq.open("GET", url+"?"+params, true);
				xmlReq.send(null);
			}
//nothing to do
			processXml = function(){}
//Interval (500ms) checking xml and waiting for both users to be connected
			getXMLDone = function(user,room){
				var url="check.php?room=<?php echo $room; ?>";
				xmlReq.onreadystatechange = processXmlOverDone;
				xmlReq.timeout = 100000;
				xmlReq.overrideMimeType("text/xml");
				xmlReq.open("GET", url, true);
				xmlReq.send(null);
			}
			processXmlOverDone = function(){
				if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
					if(check4UsersConex()){
//when both connected show alert, change user->side images and central image
						notifyTimerDown('<?php echo Language::get('txtOtherUserConn')?>');
						setTimeout(function(){$("#imgR").attr('src','images/before_connecting<?php echo $user;?>.jpg');},1000);
						setTimeout(function(){$("#imgR").attr('src','images/connecting.jpg');},1500);
						$('#buttonsCheck').show('fast');
						$('#LayerBtn0').show('slow');
						$('#image').fadeIn('slow');
						showImage('<?php echo $user;?>');
					}
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
					intervalUpdateAction = setInterval(check4BothChecked,1000);
					//if(numExerc==1) $.colorbox({href:"home.php?id=<?php echo $data;?>",escKey:true,overlayClose: false});
					if(numExerc==1){
						clearInterval(intTimerNow); 
						$.colorbox.close();
					}
					posibleDesconn=1;
					return true;
				}else return false;
			}
			check4BothChecked = function(){
				$.ajax({
				  type: 'GET',
				  url: "check.php?room=<?php echo $room; ?>",
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
										notifyTimerDown(txtTheUser+isFinishedFirst+txtReplied);
									  }
							}
						}
				},
			})
			},
//Interval (1000ms) checking for both users to write down answer into xml
			check4BothChecked_old = function(){
				var url="<?php echo $room; ?>.xml";
				xmlReq.onreadystatechange = processXmlOverChecked;
				if(userDesconn==0){
					xmlReq.timeout = 100000;
					xmlReq.overrideMimeType("text/xml");
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
					var countNodesXML = <?php echo $node-1;?>;
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
									//showNextQuestion();
									enableSolution();
								}
	//First answer, notify the other user
							}else if(isFinishedFirst!=null && isFinishedSecond==null && isFinishedFirst!='<?php echo $user;?>'){
							 		notifyTimerDown(txtTheUser+isFinishedFirst+txtReplied);
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
				setTimeout("document.location.href='selectUserAndRoom.php'",250);
				//END
			}
//hide all kind of stuff in page
			hideButtons = function(){
				$('#steps').hide('fast');
				$('#tasks').hide('fast');
			}
			hideText = function(){
				notifyTimerDown(txtDesconnected);
				$('#buttonDesconn').hide('slow');
			}
			//20121004
			enableSolution = function() {
				//alert("Enabling solution!!!");
				//$('#next_task .lbl').html("See Solution");
				salir=1;
				clearInterval(intervalUpdateAction);
				if(intervalTimerAction!=null) clearInterval(intervalTimerAction);
				$('#next_task').attr('onclick',"showSolutionAndShowNextTask();return false;");	
				$('#next_task').addClass('active');
			}
			//END
//hide
//hide all kind of stuff in page
			writeButtons = function(){
				$("#steps").addClass("steps_"+numBtn);
	  				var botones="";
					for(var i=0;i<numBtn;i++){
						j=i+1;
						if(numBtn==1){
							botones+='<li id="sol1Item" class="solution" style="display:none;"><span class="lbl">Solution <img src="img/ok.png" alt="solution" /></span></li><li id="next1Item" style="display:none;"><a href="#" class="next" id="next_task" title="Next Task"><span class="lbl">See Solution</span></a></li><li class="step"><a href="#" class="active" id="step_'+i+'" title="step '+j+'" onclick="accion(\'btn'+i+'\','+i+');waitStep('+i+');showSolutionAndShowNextTask();document.getElementById(\'sol1Item\').style.display=\'inline\';document.getElementById(\'next1Item\').style.display=\'inline\';return false;"><span class="lbl">See Solution</span></a></li>';
						}else{
							if(i==0) botones+='<li class="step"><a href="#" class="active" id="step_'+i+'" title="step '+j+'" onclick="accion(\'btn'+i+'\','+i+');waitStep('+i+');return false;"><span class="lbl">'+j+'</span></a></li>';
							else botones+='<li class="step"><a href="#" id="step_'+j+'" title="step '+j+'" onclick="accion(\'btn'+i+'\','+i+');waitStep('+i+');return false;"><span class="lbl">'+j+'</span></a></li>';
						}
					}
					if(numBtn>1) botones+='<li class="solution"><span class="lbl">Solution <img src="img/ok.png" alt="solution" /></span></li><li><a href="#" class="next" id="next_task" title="Next Task"><span class="lbl">See Solution</span></a></li>';
					$("#steps").html(botones);
									
				var tasksIt="<ul>";
				for(var i=1;i<=numNodes;i++){
					if(i<numExerc) tasksIt+='<li class="completed"><span class="lbl">Task '+i+' <img src="img/ok.png" alt="completed" /></span></li>';
					if(i==numExerc) tasksIt+='<li class="active"><span class="lbl">Task '+i+'</span></li>';//<li class="arrow"></li>';
					if(i>numExerc) tasksIt+='<li><span class="lbl">Task '+i+'</span></li>';
					if (i<numNodes) tasksIt+='<li class="arrow"></li>';
				}
				tasksIt+="</ul>";
				$('#tasks').html(tasksIt);
				//$('#textosExerc').html(textE);
				
				//monta el iframe de inicio
				if(initHTMLB==null)
				$('#ifrmHTML').attr("src","<?echo $path; ?>ejercicios/<?php echo $ExerFolder;?>/"+initHTML+"?user=<?php echo $user;?>");
				else{
					if("<?php echo $user;?>"=="a")
						$('#ifrmHTML').attr("src","<?echo $path; ?>ejercicios/<?php echo $ExerFolder;?>/"+initHTML);
					else
						$('#ifrmHTML').attr("src","<?echo $path; ?>ejercicios/<?php echo $ExerFolder;?>/"+initHTMLB);
				}
						
				if(numExerc==1) 
					if('<?php echo $user;?>'=='a') {	
					<?php
						$fn = '';
						$sn = '';
						if($nameb!=null){
							$fnB = $nameb->fullname;
							$fnB = explode(" ",$fnB);
							$fn = $fnB[0];
							$sn = $fnB[1];
						}
					?>
						$.colorbox({href:"waiting4user.php?fn=<?php echo $fn;?>&sn=<?php echo $sn; ?>",escKey:false,overlayClose:false,width:380,height:280,onLoad:function(){$('#cboxClose').hide();}});
					}
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
				  data: {'room':'<?php echo $room;?>','user':'<?php echo $user;?>','number':number,'nextSample':'<?php echo $node;?>','tipo':'confirm'},
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
				if(numNodes!=<?php echo $node;?>){
					$('#next_task .lbl').html("Next Task");
				}
				$('#ifrmHTML').attr("src","<?echo $path; ?>ejercicios/<?php echo $ExerFolder;?>/"+endHTML);
				showNextQuestion();
				
			}
			// END
			
			showNextQuestion = function(){
				//20121004 - DELETE - @abertranb - No show 
				//showSolution();
				//end
				salir=1;
				clearInterval(intervalUpdateAction);
				if(intervalTimerAction!=null) clearInterval(intervalTimerAction);
				
				//muestra el iframe de la solución
				//$('#ifrmHTML').attr("src","<?echo $path; ?>ejercicios/<?php echo $ExerFolder;?>/"+endHTML);
				if(numNodes!=<?php echo $node;?>){
					$('#next_task').attr('href', classOf+'.php?room=<?php echo $room;?>&user=<?php echo $user;?>&nextSample='+nextSample+'&node=<?php echo $node+2;?>&data=<?php echo $data;?>');
					document.getElementById('next1Item').style.display='inline';
				}else{
					$('#next_task .lbl').html("Click to finish");
					$('#next_task').attr('onclick',"showFinishedAlert();return false;");					
				}
				
			}
			showFinishedAlert = function(){
				endOfTandem=1;
				$.colorbox({href:"end.php?room=<?php echo $room;?>",escKey:true,overlayClose:false,onLoad:function(){$('#cboxClose').hide();}});
				}
//shows central image
			showImage = function(id){
				$('#image').show('slow');
			}

//init
			getInitXML();
//prevents from closing
			window.onbeforeunload = function() {
			    if(salir==0) return "Do you want to leave this page?. Please logout before exit tandem.";
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
				    			<a href="#info_user_1" id="lnk_user_1" class="infotip" data-rel="<?php echo Language::get('hide_profile')?>"><span><?php echo Language::get('show_profile')?></span></a>
				    		</div>
				    		<div id="image_person_a" class="photo" alt="user 1 photo"></div>
				    		
				    		<div class="user_info" id="info_user_1">
					    		<span class="social" title="skype" id="chat_person_a">SkypeUser <span class="icon skype"></span></span>
				    		</div>
				    		<a href="#" id="lnk_quit" onclick="desconn();"><?php echo Language::get('quit')?></a>
				    	</div>
				    	<div class="user">
				    		<div class="details">
				    			<span class="name" id="name_person_b"></span>
				    			<a href="#info_user_2" id="lnk_user_2" class="infotip" data-rel="<?php echo Language::get('hide_profile')?>"><span><?php echo Language::get('show_profile')?></span></a>
				    		</div>
				    		<div id="image_person_b" class="photo" alt="user 2 photo"></div>
				    		<div class="user_info" id="info_user_2">
				    			<span class="social" title="skype" id="chat_person_b">SkypeUser <span class="icon skype"></span></span>
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
 			<div class="lbl"><?php echo Language::get('task_remaining_time')?></div>
	 		<div class="clock" id="clock"><span class="mm">00</span>:<span class="ss">00</span></div>
 			<div class="linewrap"><div class="line"></div></div>
	  	</div>
        

      	<div id="content">
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
				<img src="img/logo_LLP.png" alt="Lifelong Learning Programme" />
				<img src="img/logo_EAC.png" alt="Education, Audiovisual &amp; Culture" />
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
		<p><a href='#' id="lnk-end-task" class="btn simplemodal-close">Close</a></p>
	</div>
	<!-- /modals -->
    
	<!-- /footer -->
	<script type="text/javascript" src="js/tandem.js"></script>
	</div>
</body>

</html>
