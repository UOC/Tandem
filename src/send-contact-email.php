<?php

require_once dirname(__FILE__) . '/classes/gestorBD.php';

if(isset($_REQUEST['msg'])){
    $msg = $_REQUEST['msg'];
}

if(isset($_REQUEST['subject'])){
    $subject = $_REQUEST['subject'];
}

if(isset($_REQUEST['current_user_id'])){
    $current_user_id = $_REQUEST['current_user_id'];
}

if(isset($_REQUEST['user_host_id'])){
    $user_host_id = $_REQUEST['user_host_id'];
}

if(isset($_REQUEST['user_guest_id'])){
    $user_guest_id = $_REQUEST['user_guest_id'];
}

if(empty($msg) or empty($current_user_id) or empty($subject) or empty($user_host_id) or empty($user_guest_id)){
	echo json_encode(array("result" => "Missing parameter"));
}else{
    $partner_id = ($current_user_id == $user_host_id) ? $user_guest_id : $user_host_id;
    $gestordb = new GestorBD();
    $response = $gestordb->send_email($msg, $subject, $partner_id);

    echo json_encode(array("result" => $response));
}