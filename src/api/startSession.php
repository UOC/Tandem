<?php
require_once dirname(__FILE__).'/../classes/lang.php';
require_once dirname(__FILE__).'/../classes/utils.php';
require_once dirname(__FILE__).'/../classes/constants.php';
require_once dirname(__FILE__).'/../classes/gestorBD.php';
require_once dirname(__FILE__).'/../classes/IntegrationTandemBLTI.php';

$id = isset($_REQUEST['id'])?intval($_REQUEST['id'],10):0;

$tandem = false;
$gestorBD = new GestorBD();
$return = new stdClass();
$return->result = 'error';
$return->sessionid = $id;

if ($id>0) {
	$tandem = $gestorBD->obteTandem($id); 
}

if ($tandem) {	
	$gestorBD->startTandemSession($id);
	$return->result = 'ok';	
}

echo json_encode($return);