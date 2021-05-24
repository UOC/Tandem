<?php

require_once __DIR__ . '/classes/gestorBD.php';

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
if(isset($_REQUEST['task_valoration'])){
    $task_valoration = $_REQUEST['task_valoration'];
}
if(isset($_REQUEST['comment'])){
    $comment = $_REQUEST['comment'];
}

if (empty($id_tandem) || empty($id_user) || !isset($task_number, $enjoyed, $nervous, $task_valoration, $comment)) {
    echo json_encode(array('result' => 'Missing parameter'));
    exit;
}

$gestordb = new GestorBD();
$response = $gestordb->setTaskEvaluation($id_tandem, $id_user, $task_number, $enjoyed, $nervous, $task_valoration, $comment);

echo json_encode(array('result' => $response));
