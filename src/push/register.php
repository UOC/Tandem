<?php

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';

$user_register_obj = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$subscriber_id     = isset( $_POST['subscriber_id'] ) ? $_POST['subscriber_id'] : false;
$user_id           = - 1;
if ( $user_register_obj && isset( $user_register_obj->id ) && $user_register_obj->id > 0 ) {
	$user_id = $user_register_obj->id;
}

$return         = new stdClass();
$return->result = 'error';

if ( $user_id > 0 && $subscriber_id ) {
	$gestorBD       = new GestorBD();
	$return->result = $gestorBD->register_push_subscription( $user_id, $subscriber_id );
}

header( 'Content-Type: application/json' );
echo json_encode( $return );
exit();
