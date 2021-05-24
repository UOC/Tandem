<?php

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../classes/mailingClient.php';
require_once __DIR__ . '/../classes/IntegrationTandemBLTI.php';

$user_obj       = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$course_id      = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : false;
$user_selected  = isset( $_REQUEST['user_selected'] ) ? $_REQUEST['user_selected'] : false;
$gestorBD       = new GestorBD();
$return         = new stdclass();
$return->result = 'error';

if ( $user_obj && $user_obj->id && $course_id && $user_selected) {
	$res            = $gestorBD->get_last_action_time( $user_selected, $course_id );
	$return->result = 'ok';
	$return->data   = $res;
	$gestorBD->update_last_action_time($user_obj->id, $course_id);
}

echo json_encode( $return );
