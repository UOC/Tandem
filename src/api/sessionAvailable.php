<?php
require_once dirname(__FILE__).'/../classes/lang.php';
require_once dirname(__FILE__).'/../classes/utils.php';
require_once dirname(__FILE__).'/../classes/constants.php';
require_once dirname(__FILE__).'/../classes/gestorBD.php';
require_once dirname(__FILE__).'/../classes/IntegrationTandemBLTI.php';

$id = isset($_REQUEST['id'])?intval($_REQUEST['id'],10):0;
$download_url = isset($_REQUEST['download_url'])?$_REQUEST['download_url']:'';

$tandem = false;
$gestorBD = new GestorBD();
$return = new stdClass();
$return->result = 'error';
$return->sessionid = $id;

if ($id>0) {
	$tandem = $gestorBD->obteTandem($id); 
}

if ($tandem) {	

	$gestorBD->updateTandemSessionAvailable($id);
	if (strlen($download_url)>0) {
		$gestorBD->updateDownloadVideoUrlFeedbackTandemByTandemId($id, $download_url);
	}
	$return->result = 'ok';	

	
}
else {
	$return->error = 'Unknown tandem id';
}
echo json_encode($return);