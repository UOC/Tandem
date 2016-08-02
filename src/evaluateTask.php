<?php

require_once dirname(__FILE__) . '/classes/gestorBD.php';

if(isset($_REQUEST['id_tandem'])){
    $id_tandem = $_REQUEST['id_tandem'];
}
if(isset($_REQUEST['id_user'])){
    $id_user = $_REQUEST['id_user'];
}
if(isset($_REQUEST['id_user'])){
    $id_user = $_REQUEST['id_user'];
}
if(isset($_REQUEST['task_number'])){
    $task_number = $_REQUEST['task_number'];
}
if(isset($_REQUEST['enjoyed'])){
    $enjoyed = $_REQUEST['enjoyed'];
}
if(isset($_REQUEST['nervous'])){
    $nervous = $_REQUEST['nervous'];
}
if(isset($_REQUEST['comment'])){
    $comment = $_REQUEST['comment'];
}
if(empty($id_tandem) or empty($id_user) or empty($task_number) or empty($enjoyed) or empty($nervous) or empty ($comment)){
	echo json_encode(array("result" => "Missing parameter"));
}

$gestordb = new GestorBD();
$response = $gestordb->setTaskEvaluation($id_tandem, $id_user, $task_number, $enjoyed, $nervous, $comment);

echo json_encode(array("result" => $response));