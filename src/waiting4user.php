<?php
require_once dirname(__FILE__).'/classes/lang.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Tandem Pantalla Inici_ParaExportar</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script>
	top.setExpiredNow(60);
</script>
<style type="text/css">
<!--

#home_ {
	position:absolute;
	left:0px;
	top:0px;
	width:695px;
	height:626px;  
}

-->
</style>
</head>
<body id="home" style="background-color:#FFFFFF;">
<img id="home" src="images/Tandem-Speakapps_01.gif" alt="" />
<br /><br />
<p style="text-align:center;"><?php echo Language::get('waitingUserX');?><?php echo " <b>".$_GET["fn"]." ".$_GET["sn"]."</b>";?></p>
<p style="text-align:center;"><?php echo Language::get('acceptance');?><br/>
<h4 style="text-align:center;"><span id="startNowBtn"></span></h4></p>

</body>
</html>