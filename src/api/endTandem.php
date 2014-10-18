<?php
require_once dirname(__FILE__).'/../classes/lang.php';
require_once dirname(__FILE__).'/../classes/utils.php';
require_once dirname(__FILE__).'/../classes/constants.php';
require_once dirname(__FILE__).'/../classes/gestorBD.php';
require_once dirname(__FILE__).'/../classes/IntegrationTandemBLTI.php';

$id = intval($_REQUEST['id'],10);
$user_id = intval($_REQUEST['user_id'],10);
$tandem = false;
$gestorBD = new GestorBD();
$return = new stdClass();
$return->ok = false;
$return->error = false;
if ($id>0) {
	$tandem = $gestorBD->obteTandem($id); 
}
if ($tandem && $user_id>0) {
	
	$exercise = $tandem['name_xml_file'];
	$room = sanitise_string($exercise.getTandemIdentifier($id, $id_resource));
	$tandemBLTI = new IntegrationTandemBLTI();
	//we need to identify the exercise
	//Now we try to get data course
	$relative_path = isset($tandem['relative_path']) && strlen($tandem['relative_path'])>0 ? $tandem['relative_path'].DIRECTORY_SEPARATOR:'';
	$data_exercise = $tandemBLTI->getDataExercise($exercise, true, $relative_path);

	$tandemBLTI->endSessionExternalToolXMLUser($user_id, $tandem);
	$return->ok = true;
}
else {
	$return->error = 'Missing parameters';
}
 echo $_GET['callback'] . '(' . json_encode($return) . ')';