<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Tandem</title>
<?php
function getDirectoryList ($directory){
    $results = array();
    $handler = opendir($directory);
    while ($file = readdir($handler)) {
    	if ($file != "." && $file != ".." && $file != ".DS_STORE") $results[] = $file;
	}
    closedir($handler);
    return $results;
}

foreach(getDirectoryList("./") as $value){
	$extension = explode(".", $value);
	$isSys = explode("data", $value);
	if($extension[1]=="xml" && $isSys[1]=="") 
		if (filemtime($value) < time()-(24*60*60)) unlink($value);
}
$room = $_GET["room"];
$nextSample = $_GET["nextSample"];
$node = $_GET["node"];
$classOf = $_GET["classOf"];
$data = $_GET["data"];

if(is_file($data.$room.".xml")){?>
	<script type="text/javascript">
		top.document.getElementById('roomStatus').innerHTML="";
		if (window.ActiveXObject) xmlReq = new ActiveXObject("Microsoft.XMLHTTP");
		else xmlReq = new XMLHttpRequest();
		function openLink(){
			
			top.document.location.href="<?php echo $classOf;?>.php?room=<?php echo $data.$room;?>&user=b&nextSample=<?php echo $nextSample;?>&node=<?php echo $node;?>&data=<?php echo $data;?>";
			}
		function checkXML(){
			var url="<?php echo $data.$room; ?>.xml";
			xmlReq.onreadystatechange = processXmlOverDone;
			xmlReq.timeout = 10000;
			xmlReq.overrideMimeType("text/xml");
			xmlReq.open("GET", url, true);
			xmlReq.send(null);
		}
		function processXmlOverDone(){
			if((xmlReq.readyState	==	4) && (xmlReq.status == 200)){
				if(xmlReq.responseXML.getElementsByTagName('usuarios')[0].childNodes.length==2){ 	
					top.document.getElementById('roomStatus').innerHTML='Please choose another room, this one is currently in use.';
				}else{ 
					top.document.getElementById('roomStatus').innerHTML="Connecting...";
					setTimeout(openLink,3000);
				}
			}
		}
		 
	</script>
	</head><body onload="checkXML();"></body></html>
<?php }else{ ?>
	<script type="text/javascript">
		top.document.getElementById('roomStatus').innerHTML="<input id='createRoom' type='submit' value=' create room ' onclick='openLink(\"<?php echo $data.$room;?>\",\"<?php echo $nextSample;?>\",\"<?php echo $node;?>\",\"<?php echo $data;?>\");'/>";
	</script>
<?php }?>