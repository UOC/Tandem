<?php

use tandemPush\PushAlert;

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/PushNotication.php';
require_once __DIR__ . '/PushAlert.php';


$user_register_obj = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$tandem_id         = isset( $_POST['id'] ) ? $_POST['id'] : false;
$sent_url          = isset( $_POST['sent_url'] ) ? $_POST['sent_url'] : false;
$userab            = isset( $_REQUEST['userab'] ) ? $_REQUEST['userab'] : '';
$user_id           = - 1;
if ( $user_register_obj && isset( $user_register_obj->id ) && $user_register_obj->id > 0 ) {
	$user_id = $user_register_obj->id;
}

$return         = new stdClass();
$return->result = 'error';

if ( $user_id > 0 && $tandem_id ) {
	$sent_url       = base64_decode( $sent_url );
	$gestorBD       = new GestorBD();
	$return->result = 'ok';
	if ( ! $gestorBD->partner_has_accessed_to_tandem( $tandem_id, $user_id ) ) {
		$tandem = $gestorBD->obteTandem( $tandem_id );
		if ( $tandem ) {

			if ( $tandem['id_user_host'] == $user_id ) {
				$partner_user_id = $tandem['id_user_guest'];
			} else {
				$partner_user_id = $tandem['id_user_host'];
			}

			$partner_data         = $gestorBD->getUserData( $partner_user_id );
			$partner_session_data = $gestorBD->getSessionUserData( $partner_user_id, $tandem_id );
			$user_session_data    = $gestorBD->getSessionUserData( $user_id, $tandem_id );

			if ( empty( $partner_session_data ) ) {
				// If the partener doesn't have a session data, then we create one for him.
				if ( $userab === 'a' ) {
					$userR = 'user=b';
				} else {
					$userR = 'user=a';
				}
				$sent_url             = str_replace( 'user=' . $userab, $userR, $sent_url );
				$open_tool_id                   = isset( $_SESSION[ OPEN_TOOL_ID ] ) ? $_SESSION[ OPEN_TOOL_ID ] : false;
				$force_select_room              = isset( $_SESSION[ FORCE_SELECT_ROOM ] ) ? $_SESSION[ FORCE_SELECT_ROOM ] : false;
				$partner_session_data = $gestorBD->createSessionUser( $tandem_id, $partner_user_id, $force_select_room,
					$open_tool_id, $sent_url );
			}

			$destination_url = FULL_URL_TO_SITE . '/goToTandem.php?tandem_id=' . $tandem_id . '&user_id=' . $partner_user_id . '&token=' . $partner_session_data['token'] . '';
			$user = $gestorBD->getUserB($user_id);
			$icon = FULL_URL_TO_SITE . '/img/tandemNotification.png';
			$push = new PushAlert();
			$push->sendNotificationToSubscriber( $gestorBD, $partner_user_id, $tandem_id,
				$LanguageInstance->getTag('Your partner %s is waiting for you to do a tandem', $user_register_obj->fullname),
				$LanguageInstance->getTag( 'Your partner %s is waiting for you', $user_register_obj->fullname ),
				$destination_url, $icon );
		} else {
			$return->result = 'error';
		}
	}
}

header( 'Content-Type: application/json' );
echo json_encode( $return );
exit();
