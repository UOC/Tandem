<?php

if (!defined(PROTECTED_FOLDER)) {
	require_once('config.inc.php');
}

function makeXML($user,$room){
	$doc = new DOMDocument();
	$doc->formatOutput = true;
	$ini = $doc->createElement( "tandem" );
	$doc->appendChild( $ini );
	$u = $doc->createElement( "usuarios" );
	$roomN = $doc->createAttribute( "room" );
	$roomNumber = $doc->createTextNode($room);
	$roomN->appendChild($roomNumber);
	$u->appendChild($roomN);
	$doc->appendChild( $u );
		$ini->appendChild( $u );
	echo $doc->saveXML();
	$doc->save(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");
	editXML($user,$room);
}

function editXML($user,$room){  
	$xml = simplexml_load_file(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");
	if(count($xml->usuarios[0]->usuario)==0) $usuario = $xml->usuarios[0]->addChild('usuario',$user);
	else if(count($xml->usuarios[0]->usuario)==1 && $xml->usuarios[0]->usuario[0]!=$user) $usuario = $xml->usuarios[0]->addChild('usuario',$user);
  	$xml->asXML(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");
}

if($_GET["user"]!="" && $_GET["room"]!=""){
	if(!is_file(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$_GET["room"].".xml")) makeXML($_GET["user"],$_GET["room"]);
	else editXML($_GET["user"],$_GET["room"]);
}
?>