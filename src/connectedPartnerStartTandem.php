<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/utils.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link media="screen" rel="stylesheet" href="css/default.css" />
<link rel="stylesheet" type="text/css" href="css/tandem.css" media="all" />
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>


<script type="text/javascript">
	var intTimerNow;
	startTandemConnectedTandem = function(){
		if (intTimerNow) {
			clearInterval(intTimerNow);
		}
		parent.$.fn.startTandemVCEvent();
		//parent.$.fn.colorbox.close();
	}

	//setTimeout("startTandemConnectedTandem", 30);
	var isNowOn=0;
	function setTime(itNow){
		isNowOn=1;
		intTimerNow = setTimeout("getTimeNow("+itNow+");", 1000);
	}
	function getTimeNow(itNow){
		var tNow;
		itNow--;
		if(itNow<10) tNow ="0"+itNow;
		else tNow = itNow;
			$("#startNowTandem").html(tNow);
			if(itNow<=1){ 
				startTandemConnectedTandem();
			}
			else setTime(itNow);
	}
	setTime(30);
	$( window ).unload(function() {
	  parent.$.fn.startTandemVCEvent();
	});
</script>
<!-- End Save for Web Styles -->
</head>
<body id="home">
<!--div>
	<img id="home" src="images/final1.png" width="310" height="85" alt="" />
</div-->
<div class='container'>
<div class='row'>
<div class='col-md-12'>
<p></p>
	<div class="text">
		<p><?php echo $LanguageInstance->get('You are in a videochat with your partner')?>.</p>
		<p><?php echo $LanguageInstance->get('Your session is now being recorded. Click on the green button to see your partner.')?></p>
		<p><?php echo $LanguageInstance->get('You will redirect to tandem activity in');?> <span id="startNowTandem"></span> <?php echo $LanguageInstance->get('seconds');?>.</p>
		<p><?php echo $LanguageInstance->get('During the session, click on Show task/Show video (on the bottom right of the screen) to switch between the conversation with your partner and the task.');?>.</p>
	</div>
<p></p>
<div class='btn_review'>
	<button onclick="startTandemConnectedTandem();" class="btn btn-success"><?php echo $LanguageInstance->get('Start tandem now!');?></button>
</div>
</div></div></div>
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</body>
</html>