<?php

$colorLink = $_GET["colorLink"];
$colorP = $_GET["colorP"];
$colorTitulos = $_GET["colorTitulos"];
$colorBotones = $_GET["colorBotones"];
$font = $_GET["font"];

if(!is_dir('../../skins')) mkdir('../../skins', 0777, true);
if(!is_dir('../../skins/css')) mkdir('../../skins/css', 0777, true);

$targetFolder = $_SERVER['DOCUMENT_ROOT'] . '/skins/css'; // Relative to the root
$sourceFolder = $_SERVER['DOCUMENT_ROOT'] . '/js/colorPicker'; // Relative to the root

if(!copy($sourceFolder."/styleSkin.css",$targetFolder."/styleSkin.css")) error_log("error copying");

$str=file_get_contents($targetFolder."/styleSkin.css");

if($colorLink!="") $str=str_replace("#111111", $colorLink ,$str);
if($colorP!="") $str=str_replace("#222222", $colorP ,$str);
if($colorTitulos!="") $str=str_replace("#333333", $colorTitulos ,$str);
if($colorBotones!="") $str=str_replace("#444444", $colorBotones ,$str);

if($font!="") {
	$str=str_replace("font0", $font ,$str);
	$str=str_replace("font1", $font ,$str);
	$str=str_replace("font2", $font ,$str);
	$str=str_replace("font3", $font ,$str);
	$str=str_replace("font4", $font ,$str);
}

file_put_contents($targetFolder."/styleSkin.css", $str);

?>