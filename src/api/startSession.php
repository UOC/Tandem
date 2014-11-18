<?php
require_once dirname(__FILE__).'/../classes/lang.php';
require_once dirname(__FILE__).'/../classes/utils.php';
require_once dirname(__FILE__).'/../classes/constants.php';
require_once dirname(__FILE__).'/../classes/gestorBD.php';
require_once dirname(__FILE__).'/../classes/IntegrationTandemBLTI.php';

$id = isset($_REQUEST['id'])?intval($_REQUEST['id'],10):0;
$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;

$tandem = false;
$gestorBD = new GestorBD();
$return = new stdClass();
$return->result = 'error';
$return->sessionid = $id;

if ($id>0) {
	$tandem = $gestorBD->obteTandem($id); 
}

if ($tandem) {	
	//Save the return id if it is set, if not get as id tandem
	$id_external_tool = isset($_GET['return_id'])?$_GET['return_id']:$id;
	$end_external_service = isset($_GET['end_external_service'])?$_GET['end_external_service']:'';
	
	$gestorBD->updateExternalToolFeedbackTandemByTandemId($id, $id_external_tool, $end_external_service);
	
	//start Tandem
	//we will only update the id_external-tool if its 0 or null
	$ccc = $gestorBD->checkExternalToolField($id);
	if(empty($ccc)){
		$gestorBD->setCreatedTandemToNow($id);
	}	
	$gestorBD->startTandemSession($id);
	$return->result = 'ok';	
}

echo json_encode($return);