<?php

require_once dirname(__FILE__) . '/classes/gestorBD.php';

if(isset($_REQUEST['msg'])){
    $msg = $_REQUEST['msg'];
}

if(isset($_REQUEST['subject'])){
    $subject = $_REQUEST['subject'];
}

if(isset($_REQUEST['partner_id'])){
    $partner_id = $_REQUEST['partner_id'];
}

if(empty($msg) or empty($partner_id) or empty($subject)){
	echo json_encode(array("result" => "Missing parameter"));
}

$gestordb = new GestorBD();
$response = $gestordb->send_email($msg, $subject, $partner_id);

echo json_encode(array("result" => $response));