<?php

require_once __DIR__ . '/classes/constants.php';
require_once __DIR__ . '/classes/gestorBD.php';
require_once __DIR__ . '/classes/lang.php';
require_once __DIR__ . '/classes/mailingClient.php';

$user_obj  = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$course_id = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : false;

if ( empty( $user_obj ) || ! isset( $user_obj->id ) ) {
	echo json_encode( array( 'result' => 'Invalid session' ) );
	exit();
}

if ( isset( $_REQUEST['msg'] ) ) {
	$msg = $_REQUEST['msg'];
}

if ( isset( $_REQUEST['subject'] ) ) {
	$subject = $_REQUEST['subject'];
}

if ( isset( $_REQUEST['partner_id'] ) ) {
	$partner_id = $_REQUEST['partner_id'];
}

if ( empty( $msg ) or empty( $partner_id ) or empty( $subject ) ) {
	echo json_encode( array( 'result' => 'Missing parameter' ) );
} else {
	$gestordb     = new GestorBD();
	$partner_data = $gestordb->getUserData( $partner_id );
	$mailing      = new MailingClient();
	$subject      = $LanguageInstance->getTag( '%s says', $user_obj->fullname ) . ': "' . $subject . '"';
	$msg          .= '<br> <hr>' . $LanguageInstance->get( 'This message has been sent via the tandem feedback form on tandemMOOC.' );
	$response     = $mailing->sendEmail( $partner_data['email'], $partner_data['fullname'], $subject, $msg );

	echo json_encode( array( 'result' => $response ) );
}
