 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link media="screen" rel="stylesheet" href="css/init.css" />
<link media="screen" rel="stylesheet" href="css/colorbox.css" />
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery.colorbox-min.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		
		function detectBrowserVersion(){
			var userAgent = navigator.userAgent.toLowerCase();
			$.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());
			var version = 0;

		// Is this a version of IE?
		if($.browser.msie){
			userAgent = $.browser.version;
			userAgent = userAgent.substring(0,userAgent.indexOf('.'));	
			version = userAgent;
		}

		// Is this a version of Chrome?
		if($.browser.chrome){
			userAgent = userAgent.substring(userAgent.indexOf('chrome/') +7);
			userAgent = userAgent.substring(0,userAgent.indexOf('.'));	
			version = userAgent;
			// If it is chrome then jQuery thinks it's safari so we have to tell it it isn't
			$.browser.safari = false;
		}

		// Is this a version of Safari?
		if($.browser.safari){
			userAgent = userAgent.substring(userAgent.indexOf('safari/') +7);	
			userAgent = userAgent.substring(0,userAgent.indexOf('.'));
			version = userAgent;	
		}

		// Is this a version of Mozilla?
		if($.browser.mozilla){
		//Is it Firefox?
		if(navigator.userAgent.toLowerCase().indexOf('firefox') != -1){
			userAgent = userAgent.substring(userAgent.indexOf('firefox/') +8);
			userAgent = userAgent.substring(0,userAgent.indexOf('.'));
			version = userAgent;
		}
		// If not then it must be another Mozilla
		else{
		}
		}

		// Is this a version of Opera?
		if($.browser.opera){
			userAgent = userAgent.substring(userAgent.indexOf('version/') +8);
			userAgent = userAgent.substring(0,userAgent.indexOf('.'));
			version = userAgent;
		}
		return version;
		}
		
		//In case of Mozilla <=3
		if(detectBrowserVersion()<='3' && jQuery.uaMatch(navigator.userAgent).browser=='mozilla'){
			
			document.getElementById('room').disabled = true;
			$.colorbox({html:"<br /><br /><p id='user_agent_msg'>You are using the browser Mozilla Firefox version "+detectBrowserVersion()+" which is not   currently compatible with the Tandem tool. Please use Google Chrome,   Firefox version 4 or higher, or Safari. <strong><br />  </strong>Please note that<strong> the Tandem Tool</strong><strong> does</strong><strong> NOT work with Internet Explorer</strong>. We are working on solving this problem.</p>",width:600,height:170,escKey:true,overlayClose: false});
		}
		//In case of Microsoft Internet Explorer
		if(jQuery.uaMatch(navigator.userAgent).browser=='msie'){
			document.getElementById('room').disabled = true;			
			$.colorbox({html:"<br /><br /><p id='user_agent_msg'>You are using the browser Internet Explorer version "+detectBrowserVersion()+" which is not   currently compatible with the Tandem tool. Please use Google Chrome,   Firefox version 4 or higher, or Safari. <strong><br />  </strong>Please note that<strong> the Tandem Tool</strong><strong> does</strong><strong> NOT work with Internet Explorer</strong>. We are working on solving this problem.</p>",width:600,height:170,escKey:true,overlayClose: false});
		}
			

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
//checks checkRoom.php for room availability
		putLink = function(value){
			getXML();
		}
//opens next exercise	
		openLink = function(value,nextExer){
			top.document.location.href=classOf+".php?room="+value+"&user=a&nextSample="+nextSample+'&node='+node+'&data='+data;
		}
//get dataROOM.xml params
		getXML = function(){
			room = document.getElementById('room').value.split("_");
			data = room[0].split("speakApps");
			data = data[0].split("-");
			data = data[0].toUpperCase();
			room = document.getElementById('room').value.split("-");
			room = room[1];
			var url="data"+data+".xml";
			xmlReq.onreadystatechange = processXml;
			xmlReq.timeout = 100000;
			xmlReq.overrideMimeType("text/xml");
			xmlReq.open("GET", url, true);
			xmlReq.send(null);
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
				$('#idfrm').attr('src','checkRoom.php?room='+room+'&nextSample='+nextSample+'&node='+node+'&classOf='+classOf+'&data='+data);
			}
		}
	});
</script>
</head>
<body class="todo">
<div id="intro">
  <div id="intro03_"></div>
	<div id="intro05_"></div>
	<div id="intro06_">	
		<input type="text" id="room" value="" size="10" onchange="putLink(this.value);"/>	
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