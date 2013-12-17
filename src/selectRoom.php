<?php 

require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';

$user_obj = $_SESSION[CURRENT_USER];
$course_id = $_SESSION[COURSE_ID];

require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';


function getAvailableExercises($directory) {
	$results = array();
    $handler = opendir($directory);
    while ($filepath = readdir($handler)) {
    	preg_match('/[^?]*/', $filepath, $matches); 
        $string = $matches[0]; 
      
        $pattern = preg_split('/\./', $string, -1, PREG_SPLIT_OFFSET_CAPTURE); 

        # check if there is any extension 
        if(count($pattern) > 1) 
        { 
            $filenamepart = $pattern[count($pattern)-1][0]; 
            preg_match('/[^?]*/', $filenamepart, $matches); 
            $pos=strpos($pattern[0][0],'data');
            if ($matches[0]=='xml' && $pos!==FALSE) {
            	 $results[] = substr($pattern[0][0], $pos+4);
            }
        } 
    	
	}
    closedir($handler);
    return $results;
}

if (!isset($user_obj)) {
	//Tornem a l'index
	header ('Location: index.php');
} else {

	//Permetem que seleccini l'exercici 20111110
	$is_host = $user_obj->is_host;
	
	$custom_room = isset($user_obj->custom_room)?$user_obj->custom_room:'';
	$tandemBLTI = new IntegrationTandemBLTI();
	$selected_exercise = $tandemBLTI->checkXML2GetExercise($user_obj);

	$array_exercises = array();
	if (strlen($custom_room)>0){
		$gestorBD	= new GestorBD();
			$array_exercises = $gestorBD->get_tandem_exercises($course_id);
		//$array_exercises = getAvailableExercises("./");
	}
	
	//Agafem les dades de l'usuari
	$name = mb_convert_encoding($user_obj->name, 'ISO-8859-1', 'UTF-8');
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link media="screen" rel="stylesheet" href="css/init.css" />
	<link media="screen" rel="stylesheet" href="css/colorbox.css" />
	<link media="screen" rel="stylesheet" href="css/default.css" />
<script src="js/jquery-1.7.2.min.js"></script>
	<?php include_once dirname(__FILE__).'/js/google_analytics.php'?>
	<script src="js/jquery.colorbox-min.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
//xml request for iexploiter/others 		
		if (window.ActiveXObject) xmlReq = new ActiveXObject("Microsoft.XMLHTTP");
		else xmlReq = new XMLHttpRequest();
//global vars - will be extracted from dataROOM.xml
		var classOf="";
		var numBtns="";
		var numUsers="";
		var nextSample="";
		var node="";
		var room="";
		var data="";
		putLink = function(value){
			getXML();
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
			data = data[0].toUpperCase();
			room = room_2[1];
			var url="data"+data+".xml";
			xmlReq.onreadystatechange = processXml;
			xmlReq.timeout = 100000;
			xmlReq.overrideMimeType("text/xml");
			xmlReq.open("GET", url, true);
			xmlReq.send(null);
		}
//get dataROOM.xml params
		getXML = function(){
			room_temp = document.getElementById('room').value;
			getXMLRoom(room_temp);
		}
		processXml = function(){
			if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
				//extract data
				var cad=xmlReq.responseXML.getElementsByTagName('nextType');
				classOf=cad[0].getAttribute("classOf");
				numBtns=cad[0].getAttribute("numBtns");
				numUsers=cad[0].getAttribute("numUsers");
				nextSample=cad[0].getAttribute("currSample");
				node=parseInt(cad[0].getAttribute("node"))+1;
				document.getElementById('roomStatus').innerHTML="";
//				document.location.href='checkRoomUser.php?exercise='+data+'&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&room='+room;
				$('#idfrm').attr('src','checkRoomUser.php?room='+room+'-<?php echo $custom_room;?>&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
				
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
				document.getElementById('roomStatus').innerHTML="";
//				document.location.href='checkRoomUser.php?exercise='+data+'&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&room='+room;
				$('#idfrm').attr('src','checkRoomUser.php?create_room=1&room='+room+'&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
				
		}
		printError = function (error) {
			alert(error);
		}
		<?php if (!$is_host) {?>
		$.colorbox.close = function(){}; 
		$.colorbox({href:"waiting4user.php",escKey:false,overlayClose:false,width:300,height:180});
		var interval = null;
		checkExercise = function() {
			if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
				//extract data
				var cad=xmlReq.responseXML.getElementsByTagName('usuarios');
				var exercise=cad[0].getAttribute("exercise")+"-<?php echo $user_obj->custom_room?>";
				if (exercise.length>0) {
					$.colorbox.close();
					if (interval!=null)
						clearInterval(interval);
					getXMLRoom(exercise);
			}
		 } 
		}

		interval = setInterval(function(){
				var url="check.php?room=<?php echo $user_obj->custom_room?>";
				xmlReq.onreadystatechange = checkExercise;
				xmlReq.timeout = 100000;
				xmlReq.overrideMimeType("text/xml");
				xmlReq.open("GET", url, true);
				xmlReq.send(null);
			 },1000);
		//interval2 = setInterval("fes",1500);
		//checkExercise_interval();
		//interval = setInterval("checkExercise_interval",3000);
		<?php }?>
		
		<?php if ($selected_exercise && strlen($selected_exercise)>0){ echo 'getXML();'; }?>
	});
</script>
</head>
<body class="todo">
<div id="intro">
  <div id="intro03_">Welcome <?php echo $name?></div>
	<div id="intro05_"></div>
	<div id="intro06_">	
		<?php if (count($array_exercises)>0 && strlen($custom_room)>0) {?>
			<select id="room" onchange="putLink(this.value);">
					<option value="">Select exercise</option>
				<?php foreach ($array_exercises as $exercise) {?>
					<option value="<?php echo $exercise['name'].'_'.$custom_room?>" <?php echo $selected_exercise==$exercise['name']?'selected':''?>><?php echo $exercise['name']?></option>
				<?php }?>
			</select>	
		<?php } else {?>
			<input type="text" id="room" value="" size="10" onchange="putLink(this.value);"/>	
		<?php }?>	
	</div>
	<div id="intro07_"></div>
	<div id="intro08_"></div>
	<div id="intro09_"></div>
	<div id="intro10_">
		<img src="images/login.png" width="62" height="19" />
	</div>
	<div id="intro11_"></div>
	<div id="intro12_"></div>
  	<div id="intro13_">
    	<p id="roomStatus"></p>
    </div>
  <div id="intro14_"></div>
  <div id="peu"> <p><strong>Welcome to the Tandem Pilot.</strong>This tool works with Google Chrome, Firefox 4 onwards, Safari 5. <strong>The Tandem Tool does NOT work with Internet Explorer.</strong> We are working on solving this problem.<br><br>

Activities are performed simultaneously by two users. To start a session one of the participants should enter IM1CA4 followed by a dash and a number (e.g. IM1CA4-55) and tell the other participant which number to use to log into the same session, in this example IM1CA4-55. If a session with the same number has already been used by another pair of participants you will not be able to create a session. If this happens, try again with a different number.<br />
    <br />

  </p>
  </div>
</div>	

<iframe src="" width="0" frameborder="0" height="0" id="idfrm" name="idfrm" />
</body>
</html>
<?php } ?>