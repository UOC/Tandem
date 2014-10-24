<?php
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/utils.php';
$tandem_id = $id = isset($_REQUEST['id'])?intval($_REQUEST['id'],10):0;
?>
<!DOCTYPE html>
<html>
<head>
<title>Waiting for videochat session</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script>
<?php 
if ($_SESSION[OPEN_TOOL_ID]>0){ ?>
	top.setExpiredNow(60);
<?php } ?>
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
<script>
<?php 
if ($_SESSION[OPEN_TOOL_ID]>0){
$url_2_return = str_replace("waiting4user.php","sampleConfirm.php",curPageURL());
$id_register_tandem = isset($_SESSION[CURRENT_TANDEM])?$_SESSION[CURRENT_TANDEM]:false;
$separtor = '?';
if (strpos($url_2_return, '?')!==false){
	$separtor = '&';
}
$url_2_return .= $separtor.'id='.$id_register_tandem;
?>
window.open('ltiConsumer.php?id=<?php echo $_SESSION[OPEN_TOOL_ID]?>&return_url=<?php echo $url_2_return?>');		
<?php } ?>  

interval = setInterval(function(){
	$.ajax({
		type: 'POST',
		url: "api/checkSession.php",
		data : {
			   id : '<?php echo $tandem_id;?>',
		},
		dataType: "JSON",
		success: function(json){	
			if(json  &&   json.result !== "undefined" && json.result == "ok"){
			opener.getInitXml();					     			
		}
	}
	});
},2500);
</script>
</head>
<body id="home" style="background-color:#FFFFFF;">
<img id="home" src="images/Tandem-Speakapps_01.gif" alt="" />
<br /><br />
<p style="text-align:center;"></p>
<p style="text-align:center;"><?php echo $LanguageInstance->get('Waiting for video chat session');?><br/>
</body>
</html>