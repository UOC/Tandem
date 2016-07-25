<?php

require_once dirname(__FILE__) . '/classes/gestorBD.php';

if(isset($_REQUEST['id_tandem'])){
    $id_tandem = $_REQUEST['id_tandem'];
}
if(isset($_REQUEST['id_user'])){
    $id_user = $_REQUEST['id_user'];
}
if(isset($_REQUEST['mood'])){
    $mood = $_REQUEST['mood'];
}
if(empty($id_tandem) or empty($id_user) or empty($mood)){
	echo json_encode(array("result" => "Missing parameter"));
}

$gestordb = new GestorBD();
$response = $gestordb->setMoodToUser($id_tandem, $id_user, $mood);

echo json_encode(array("result" => $response));
