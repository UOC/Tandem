<?php

require_once __DIR__ . '/classes/constants.php';
require_once __DIR__ . '/classes/gestorBD.php';
require_once __DIR__ . '/classes/mailingClient.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

if (empty($user_obj) || !isset($user_obj->id)) {
	echo json_encode(array('result' => 'Invalid session'));
	exit();
}

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

if(empty($msg) || empty($current_user_id) || empty($subject) || empty($user_host_id) || empty($user_guest_id)){
	echo json_encode(array('result' => 'Missing parameter'));
}else{
    $partner_id = ($current_user_id == $user_host_id) ? $user_guest_id : $user_host_id;
    $gestordb = new GestorBD();
    $partner_data = $gestordb->getUserData($partner_id);
    $mailing = new MailingClient();
    $response = $mailing->sendEmail($partner_data['email'], $partner_data['fullname'], $subject, $msg);

    echo json_encode(array('result' => $response));
}
