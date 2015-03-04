<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/

// Define a destination

$imgToSave = $_GET["img"];

if(!is_dir('../../skins')) mkdir('../../skins', 0777, true);
if(!is_dir('../../skins/img')) mkdir('../../skins/img', 0777, true);
$targetFolder = '/skins/img'; // Relative to the root

$verifyToken = md5('unique_salt' . $_POST['timestamp']);




if (isset($_FILES['Filedata']) && $_POST['token'] == $verifyToken) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);

	if (in_array($fileParts['extension'],$fileTypes)) {
		if(!move_uploaded_file($tempFile,$targetFile)) error_log("Failed".$_FILES['userfile']['error']);

		if($fileParts['extension']!='png') imagepng(imagecreatefromstring(file_get_contents($targetFile)), $targetPath."/".$imgToSave.".png");
		else rename($targetFile,$targetPath."/".$imgToSave.".png");

	} else {
		error_log('Invalid file type.');
	}
}

if(!is_dir('../../skins')) mkdir('../../skins', 0777, true);
if(!is_dir('../../skins/css')) mkdir('../../skins/css', 0777, true);

$targetFolder = $_SERVER['DOCUMENT_ROOT'] . '/skins/css'; // Relative to the root
$sourceFolder = $_SERVER['DOCUMENT_ROOT'] . '/js/colorPicker'; // Relative to the root

if(!is_file($targetFolder."/styleSkin.css")) copy($sourceFolder."/styleSkinImg.css",$targetFolder."/styleSkin.css");



?>