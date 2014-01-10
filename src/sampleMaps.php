<?php
//Retrieve data from url params
$room = $_GET["room"];
$data = $_GET["data"];
$user = $_GET["user"];
$ExerFolder = $_GET["nextSample"];
$is_final = false;
include_once(dirname(__FILE__).'/classes/register_action_user.php');
//This is because xml nodes begins counting at zero, but zero is not real :-) 
if($_GET["node"]==1) $node = $_GET["node"];
else $node = $_GET["node"]-1;
//For user A and B only. If more users or login names needed, fetch data from xml :-)
if($user =='a') $Otheruser='b';
else $Otheruser='a';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset=utf-8 />
	<title>Tandem</title>
	<link media="screen" rel="stylesheet" href="css/colorbox.css" />
	<link media="screen" rel="stylesheet" href="css/default.css" />
	<script src="js/jquery-1.7.2.min.js"></script>
	<script src="js/jquery.colorbox-min.js"></script>
	<script src="js/loadUserData.js"></script>
	<?php include_once dirname(__FILE__).'/js/google_analytics.php'?>
	<script>
		$(document).ready(function(){
//colorbox js action
			var originalClose = $.colorbox.close; 
			$.colorbox.close = function(){
				if(numCadenas!=numUsers) $.colorbox({href:"waiting4user.html",escKey:false,overlayClose:false});
				else if(endOfTandem==1) showFinishedAlert();
				else originalClose();
			};
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
			var accionNum=0;
			var posibleDesconn=0;
			var userDesconn = 0;
			var classOf;
			var numExerc;
			var numUsers;
			var nextSample;
			var numCadenas;
			var numBtn;
			var textE="";
			var body = document.getElementsByTagName('body').item(0);
			var script = document.createElement('script');
			var btns="";
			var minis = new Array();
			var minisId=0;
			var numNodes=0;
			var minisOther = new Array();
			var minisOtherId=0;
			var endOfTandem=0;
			if (window.ActiveXObject) xmlReq = new ActiveXObject("Microsoft.XMLHTTP");
			else xmlReq = new XMLHttpRequest();
//get data from dataROOM.xml->initializes exercise values
			getInitXML = function(){
				var url = "<?php echo $path;?>data<?php echo $data;?>.xml";
				xmlReq.onreadystatechange = processInitXml;
				xmlReq.timeout = 100000;
				xmlReq.overrideMimeType("text/xml");
				xmlReq.open("GET", url, true);
				xmlReq.send(null);
			}
			processInitXml = function(){
				if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
					//extract data
					//var lng=xmlReq.responseXML.getElementsByTagName('exe')[0].getAttribute("lang");
					//script.src = "lang/"+lng+".js";
					//script.type = 'text/javascript';
					//body.appendChild(script);
					var cad=xmlReq.responseXML.getElementsByTagName('nextType');
					numNodes = cad.length-1;
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
					setTimeout(function(){$('#showNews').slideDown('fast');$('#showNews').html(txtWaiting4User);},250);
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
//when both connected show alert, change user->side images and central image
					if(check4UsersConex()){
						$('#showNews').slideDown('fast');
						$("#showNews").html(txtOtherUserConn);
						setTimeout(function(){$("#imgR").attr('src','images/before_connecting<?php echo $user;?>.jpg');},1000);
						setTimeout(function(){$("#imgR").attr('src','images/connecting.jpg');},1500);
						$('#buttonsCheck').show('fast');
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
					if(numExerc==1) $.colorbox({href:"home.php?id=<?php echo $data;?>",escKey:true,overlayClose: false});
					posibleDesconn=1;
					return true;
				}else return false;
			}
//Interval (1000ms) checking for both users to write down answer into xml

			check4BothChecked = function(){
				$.ajax({
				  type: 'GET',
				  url: "check.php?room=<?php echo $room; ?>",
				  data: {
					  room: "<?php echo $room; ?>"
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

						var countNodesXML = <?php echo $node-1;?>;
						var cad = $(xml).find('actions');

						if(cad.length>countNodesXML){					
							var numAcc = cad[countNodesXML].getElementsByTagName('action').length;
							var numAccPos=numAcc-1;
							//TODO repassar a partir d'aqu�
							var isFinishedFirst = cad[countNodesXML].getElementsByTagName('action')[numAccPos].getAttribute('firstUser');
							var isFinishedSecond = cad[countNodesXML].getElementsByTagName('action')[numAccPos].getAttribute('secondUser');						
							if(isFinishedFirst!=null && isFinishedSecond!=null){
								var valorBtn = cad[countNodesXML].getElementsByTagName('action')[numAccPos].firstChild.nodeValue;
								if($('#LayerBtn'+valorBtn)){
									$('#LayerBtn'+valorBtn).html('<img src="ejercicios/sample<?php echo $ExerFolder;?>/btn'+valorBtn+'p.gif"/>');
									$('#showNews').slideUp('fast');
								}
	//if true, exercise finished
								if(numAcc==numBtn) showNextQuestion();
							}
							for(var i=0;i<numAcc;i++){
								var isAnswered = cad[countNodesXML].getElementsByTagName('action')[i].firstChild.nodeValue;
	//First answer, notify the other user
								if(inArray(isAnswered,minisOther)==false){
									minisOther[minisOtherId]=isAnswered;
									$('#LayerBtn'+isAnswered).show('slow');
									minisOtherId++;
								}
							}
							if(isAnswered!=null){
								<?php if($user=='b'){?>
								$('#imgPpal').attr('src','ejercicios/sample<?php echo $ExerFolder;?>/a'+minisOther.join('')+'.jpg');
								<?php }?>						
								if(numAcc!=numBtn){
									if(isFinishedFirst!=null && isFinishedSecond==null && isFinishedFirst!='<?php echo $user;?>'){
									 		$('#showNews').slideDown('fast');
											$('#showNews').html(txtTheUser+"<?php echo " ".$Otheruser;?>"+txtReplied);
										  }
									<?php if($user=='a'){ ?>
									var mapChanged= document.getElementById("btn"+isAnswered);
									mapChanged.onclick= function(){
										return false;
									}
									<?php }?>
								}
							}
						}
				},
			})
			},
			check4BothChecked_old = function(){
				var url="check.php?room=<?php echo $room; ?>";
				if (window.ActiveXObject) xmlReq = new ActiveXObject("Microsoft.XMLHTTP");
				else xmlReq = new XMLHttpRequest();
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
//checks that an element is in array
			inArray = function(needle, haystack) {
			    var length = haystack.length;
				var f=false;
			    for(var i = 0; i < length; i++) {
			        if(haystack[i] == needle) f=true;
			    }
			    return f;
			}
///////////////////////////////////////////////////
//main function. Checks user's answers
			processXmlOverChecked = function(){
				if(xmlDontExists("<?php echo $room; ?>.xml") == 404){
					hideText();
					userDesconn=1;
				}else if(xmlReq.readyState == 4 && xmlReq.status == 200){
					var countNodesXML = <?php echo $node-1;?>;
					if(xmlReq.responseXML.getElementsByTagName('actions').length>countNodesXML){					
						var cad=xmlReq.responseXML.getElementsByTagName('actions');
						var numAcc = cad[countNodesXML].getElementsByTagName('action').length;
						var numAccPos=numAcc-1;
						var isFinishedFirst = cad[countNodesXML].getElementsByTagName('action')[numAccPos].getAttribute('firstUser');
						var isFinishedSecond = cad[countNodesXML].getElementsByTagName('action')[numAccPos].getAttribute('secondUser');						
						if(isFinishedFirst!=null && isFinishedSecond!=null){
							var valorBtn = cad[countNodesXML].getElementsByTagName('action')[numAccPos].firstChild.nodeValue;
							if($('#LayerBtn'+valorBtn)){
								$('#LayerBtn'+valorBtn).html('<img src="ejercicios/sample<?php echo $ExerFolder;?>/btn'+valorBtn+'p.gif"/>');
								$('#showNews').slideUp('fast');
							}
//if true, exercise finished
							if(numAcc==numBtn) showNextQuestion();
						}
						for(var i=0;i<numAcc;i++){
							var isAnswered = cad[countNodesXML].getElementsByTagName('action')[i].firstChild.nodeValue;
//First answer, notify the other user
							if(inArray(isAnswered,minisOther)==false){
								minisOther[minisOtherId]=isAnswered;
								$('#LayerBtn'+isAnswered).show('slow');
								minisOtherId++;
							}
						}
						if(isAnswered!=null){
							<?php if($user=='b'){?>
							$('#imgPpal').attr('src','ejercicios/sample<?php echo $ExerFolder;?>/a'+minisOther.join('')+'.jpg');
							<?php }?>						
							if(numAcc!=numBtn){
								if(isFinishedFirst!=null && isFinishedSecond==null && isFinishedFirst!='<?php echo $user;?>'){
								 		$('#showNews').slideDown('fast');
										$('#showNews').html(txtTheUser+"<?php echo " ".$Otheruser;?>"+txtReplied);
									  }
								<?php if($user=='a'){ ?>
								var mapChanged= document.getElementById("btn"+isAnswered);
								mapChanged.onclick= function(){
									return false;
								}
								<?php }?>
							}
						}
					}//if num Actions
				}//if readyState
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
				hideText();
				setTimeout("document.location.href='index.php'",250);
			}
//hide all kind of stuff in page
			hideButtons = function(){
				for(var i=0;i<numBtn;i++) $('#LayerBtn'+i).hide('fast');
				$('#buttonsCheck').hide('fast');
				$('#imgComp2').hide('fast');
			}
			hideText = function(){
				$('#showNews').slideDown('fast');
				$('#showNews').html(txtDesconnected);
				$('#buttonDesconn').hide('slow');
				clearInterval(intervalUpdateAction);
			}
//hide
//hide all kind of stuff in page
			writeButtons = function(){
				btns="<ul class='listaBotones'>";
				for(var i=0;i<numBtn;i++)
				btns+='<li id="LayerBtn'+i+'"><a href="#" onclick="accion(\'btn'+i+'\','+i+');return false;"><img src="ejercicios/sample<?php echo $ExerFolder;?>/btn'+i+'.gif"/></li>';
				btns+="</ul>";
				$('#buttonsCheck').html(btns);				
				$('#buttonDesconn').html('<img src="images/logout.jpg" onclick="desconn();"/>');
				$('#showNews').slideDown('fast');
				$('#textosExerc').html(textE);
				if(numExerc==1) if('<?php echo $user;?>'=='a') $.colorbox({href:"waiting4user.php",escKey:false,overlayClose:false});
			}
//executes action->action.php writes in room.xml data got from user's activity (img pressed), shows next button
			accion = function(id,number){
				//$('#idfrm').attr('src','action.php?room=<?php echo $room;?>&user=<?php echo $user;?>&number='+number+'&nextSample=<?php echo $node;?>&tipo=map');
				$.ajax({
				  type: 'GET',
				  url: "action.php",
				  data: {'room':'<?php echo $room;?>','user':'<?php echo $user;?>','number':number,'nextSample':'<?php echo $node;?>','tipo':'map'},
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
				var mapId=id;
				id = id.split("btn");
				id = parseFloat (id[1]);
				<?php if($user=='a'){ ?>
				var mapChanged= document.getElementById(mapId);
				mapChanged.onclick= function(){
					return false;
				}
				<?php }?>
				minis[minisId] = id;
				minisId++;
				<?php if($user=='b'){?>
				$('#imgPpal').attr('src','ejercicios/sample<?php echo $ExerFolder;?>/a'+minis.join('')+'.jpg');
				<?php }?>
				$('#LayerBtn'+id).html('<img src="ejercicios/sample<?php echo $ExerFolder;?>/btn'+id+'inter.gif"/>');
				$('#LayerBtn'+id).show('fast');
			}
//Exercise finished, stop checking for answers' interval, shows next question
			showNextQuestion = function(){
				salir=1;
				clearInterval(intervalUpdateAction);
				hideButtons();
				$('#imgCompSrc').attr('src','ejercicios/sample<?php echo $ExerFolder;?>/<?php echo $Otheruser;?>_Small.jpg');
				$('#imgComp').hide('fast');
				$('#imgComp2').show('fast');
				if(numNodes!=<?php echo $node;?>){
					$('#showNews').slideUp('fast');
					$('#nextQuestion').html('<a href="'+classOf+'.php?room=<?php echo $room;?>&user=<?php echo $user;?>&nextSample='+nextSample+'&node=<?php echo $node+1;?>&data=<?php echo $data;?>"><img src="images/siguiente.gif"/></a>');
				}else{
					$('#showNews').slideDown('fast');
					$('#showNews').html(txtFin);
					$('#nextQuestion').html('<a href="#" onclick="showFinishedAlert();return false;"><img src="images/final.gif"/></a>');
				}			
			}
			
			showFinishedAlert = function(){
				endOfTandem=1;
				$.colorbox({href:"end.php?room=<?php echo $room;?>",escKey:true});
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
	
<body>
	<div id="container">
    	<div id="head"> <img src="images/Tandem-Speakapps_01.gif"/>
			<div id="buttonDesconn"></div>
			<div id="help"><a href="#" onclick='$.colorbox({href:"home.php?id=<?php echo $data;?>",escKey:true,overlayClose:false});'><img src="images/info.jpg"/></a></div>
        	
		</div>
      	
<div id="content">
			<div id="menu">
				<ul>
					<?php 
					//Show top menu
						$i = $node;
						$task = $i."p";
						if(is_file('images/task'.$i.'p.gif')) echo '<li><img src="images/task'.$i.'p.gif"/></li>';
						else echo '<li><img src="images/task'.$task.'.gif"/></li>';
					?>
      			</ul>
			</div>
    		
			<div id="textosExerc"></div>
			
    		<div class="image">
				<img id="imgPpal" src="ejercicios/sample<?php echo $ExerFolder;?>/<?php echo $user;?>.jpg" alt="Zoom" usemap="#MAP"/>
                <?php if($user=='a'){ ?>
				<map name="Map" id="Map">	
                    	<area id="btn0" shape="rect" coords="57,186,89,240" href="#" onClick="accion('btn0',0);return false;"/>
						<area id="btn1" shape="rect" coords="354,230,382,260" href="#" onClick="accion('btn1',1);return false;"/>
						<area id="btn2" shape="rect" coords="405,174,489,329" href="#" onClick="accion('btn2',2);return false;"/>
  				</map>
				<?php }?>
				
				<div id="zoom">
            		<a rel="example1" title="Zoom" href="ejercicios/sample<?php echo $ExerFolder;?>/<?php echo $user;?>_Big.jpg">
            		<img id="zoom" src="images/zoom.jpg" width="50" height="21" alt="" /></a>
            	</div>
				
			</div>
			<div id="imgComp">
				<img src="ejercicios/sample<?php echo $ExerFolder;?>/ex.gif"/>
			</div>
			<div id="imgComp2">
				<a rel="example1" title="Zoom" href="ejercicios/sample<?php echo $ExerFolder;?>/<?php echo $Otheruser;?>_Big.jpg"><img src="ejercicios/sample<?php echo $ExerFolder;?>/<?php echo $Otheruser;?>_Small.jpg" alt="Zoom"/></a>
				<div id="zoom2"><a rel="example1" title="Zoom" href="ejercicios/sample<?php echo $ExerFolder;?>/<?php echo $Otheruser;?>_Big.jpg"><img id="zoom2" src="images/zoom.jpg" width="50" height="21" alt="" /></a></div>
			</div>
		
			<div id="buttonsCheck"><p></p></div>
            <div id="showNews"></div>
		</div>

	<div id="columR">
			<div class="titol_modul">
		    	The tandem of&hellip;
		    </div>
		    <div id="contenidor_usuaris" class="contenidor_connexio contenidor_esperant">
		    	<div id="contenidor_connector_a" class="contenidor_connector">
		        	<div class="contenidor_foto_a" id="image_person_a">
		            	&nbsp;
		            </div>
		            <div class="contenidor_dades_person_a">
		            	<span id="name_person_a" class="name_person">&nbsp;</span><br>
		            	<span id="points_person_a" class="punts">&nbsp;</span><br>
		            	<span id="chat_person_a" class="punts">&nbsp;</span>
		            </div>
		        </div>
		        <div class="clear"></div>
		    	<div id="contenidor_connector_b" class="contenidor_connector">
		            <div class="contenidor_dades_person_b">
		            	<span id="name_person_b" class="name_person">&nbsp;</span><br>
		            	<span id="points_person_b" class="punts">&nbsp;</span><br>
		            	<span id="chat_person_b" class="punts">&nbsp;</span>
		            </div>
		        	<div class="contenidor_foto_b"  id="image_person_b">
		            	&nbsp;
		            </div>
		        </div>
		    </div>
		    <img id="img_footer_connecting" src="images/footer_connecting.jpg"/>
		</div>		
		<div id="nextQuestion"></div>
		<div id="footer"> <img src="images/Tandem-Speakapps_35.jpg"/> </div>
	
	</div>

	<!-- iframe src="" width="0" frameborder="0" height="0" id="idfrm" name="idfrm"></iframe>-->
</body>

</html>