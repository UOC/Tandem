<?php

use tandemPush\PushAlert;

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/PushNotication.php';
require_once __DIR__ . '/PushAlert.php';


$user_register_obj = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$user_id           = - 1;
$course_register_id = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : - 1;
if ( $user_register_obj && isset( $user_register_obj->id ) && $user_register_obj->id > 0 ) {
	$user_id = $user_register_obj->id;
}

$return         = new stdClass();
$return->result = 'error';

if ( $user_id > 0 && $course_register_id > 0) {
	$gestorBD       = new GestorBD();
	$return->result = 'ok';

	$destination_url = 'http://mooc.speakapps.org/roulette-tandem/';

	$icon = FULL_URL_TO_SITE . '/img/tandemNotification.png';
	$push = new PushAlert();
	$user_language      = $_SESSION[ LANG ];

	$title = substr($user_language, 0, 2) === 'es' ? 'There are users waiting for Tandem' : 'Hay usuarios esperando para hacer tandem';
	$msg = substr($user_language, 0, 2) === 'es' ? 'Click here to access' : 'Haz click aquí para acceder';

	$push->sendNotificationToLang( $gestorBD, $user_id, $user_language, $course_register_id,
		$title,
		$msg,
		$destination_url, $icon );
}

header( 'Content-Type: application/json' );
echo json_encode( $return );
exit();
