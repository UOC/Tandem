<?php
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/utils.php';
require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';

$id = isset($_REQUEST['id'])?intval($_REQUEST['id']):0;
$is_user_host = isset($_REQUEST['is_user_host'])?intval($_REQUEST['is_user_host']):-1;
$user_obj = $_SESSION[CURRENT_USER];
$course_id = $_SESSION[COURSE_ID];
if (!isset($user_obj) || !isset($course_id) || !$user_obj->instructor || $id<=0 || $is_user_host<0) {
	//Tornem a l'index
	header ('Location: index.php');
} else {
	$tandemBLTI = new IntegrationTandemBLTI();
	$gestorBD	= new GestorBD();
	$_SESSION[CURRENT_TANDEM] = $id;
	$tandem = $gestorBD->obteTandem($id);
	$room = $tandem['name_xml_file'].$tandem['id_resource_lti'].'_'.$tandem['id'];
	$user = $is_user_host==1?'a':'b';
	$exercise = $tandem['name_xml_file'];
	$xml = $tandem['xml'];
	$data_exercise = $tandemBLTI->getDataExercise($exercise);
	$file = $data_exercise->classOf;
	$node = $data_exercise->node;
	$nextSample = $data_exercise->nextSample;
	if (!file_exists(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml")) {
		
		$handle = fopen(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml", 'w');
		if ($handle) {
			fwrite($handle, $xml);
			fclose($handle);
		}
		
	}
	header ('Location: '.$file.'.php?room='.$room.'&user='.$user.'&nextSample='.$nextSample.'&node='.$node.'&data='.$exercise);
}