<?php

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../classes/mailingClient.php';
require_once __DIR__ . '/../classes/IntegrationTandemBLTI.php';

$id                             = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'], 10 ) : 0;
$user_obj                       = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$force_select_room              = isset( $_SESSION[ FORCE_SELECT_ROOM ] ) ? $_SESSION[ FORCE_SELECT_ROOM ] : false;
$open_tool_id                   = isset( $_SESSION[ OPEN_TOOL_ID ] ) ? $_SESSION[ OPEN_TOOL_ID ] : false;
$sent_url                       = isset( $_REQUEST['sent_url'] ) ? base64_decode( $_REQUEST['sent_url'] ) : '';
$userab                         = isset( $_REQUEST['userab'] ) ? $_REQUEST['userab'] : '';
$check_if_i_accepted_connection = isset( $_REQUEST['check_if_i_accepted_connection'] ) ? $_REQUEST['check_if_i_accepted_connection'] == 1 : false;
$check_onlyaudio_video          = isset( $_REQUEST['check_onlyaudio_video'] ) ? $_REQUEST['check_onlyaudio_video'] == 1 : false;
$tandem                         = false;
$gestorBD                       = new GestorBD();
$return                         = new stdclass();
$mailing                        = new MailingClient( $gestorBD, $LanguageInstance );
$return->result                 = 'error';
$timePassed                     = 0;

if ( $id > 0 ) {
	$tandem = $gestorBD->obteTandem( $id );
}
if ( $tandem ) {

	if ( defined( 'BBB_SECRET' ) ) {
		$bbb_path = __DIR__ . '/../bbb/BBBIntegration.php';
		if ( file_exists( $bbb_path ) ) {
			require_once $bbb_path;
			BBBIntegration::checkSessionInfo( $id, $gestorBD, 2, BBB_REQUIRES_WEBCAM_TO_START );
			if ($check_onlyaudio_video) {
				$feedback = $gestorBD->getFeedbackByIdTandemIdUser( $id, $user_obj->id );
				$return->accepted_audio = false;
				$return->accepted_video = false;
				if ($feedback) {
					$return->accepted_audio = !empty( $feedback['accepted_audio_datetime'] );
					$return->accepted_video = !empty( $feedback['accepted_video_datetime'] );
					echo json_encode( $return );
					exit();
				}
			}
		}
	}
	//lets update the session_user
	if ( ! empty( $user_obj->id ) && ! empty( $force_select_room ) && ! empty( $open_tool_id ) && $open_tool_id > 0 && ! empty( $sent_url ) ) {
		$timePassed = $gestorBD->updateSessionUser( $id, $user_obj->id, $force_select_room, $open_tool_id, $sent_url );
	}

	if ( $gestorBD->checkTandemSession( $id ) ) {
		if ( isset( $_SESSION[ ID_FEEDBACK ] ) && $_SESSION[ ID_FEEDBACK ] > 0 ) {
			$_SESSION[ ID_EXTERNAL ] = $gestorBD->getFeedbackExternalIdTool( $id );
		}
		$return->result = "ok";
		$return->accepted_audio = true;
		$return->accepted_video = true;

	} elseif ( $user_obj && $gestorBD->myPartnerAcceptedConnectionAndMeNot( $id, $user_obj->id ) ) {
		$return->other_partner_connected_and_me_not = 1;
	} elseif ( $user_obj && $check_if_i_accepted_connection && $gestorBD->meAcceptedConnectionAndMyPartnerNot( $id,
			$user_obj->id ) ) {
		$return->i_connected_and_my_partner_not = 1;
	} elseif ( $user_obj && $timePassed > MAX_TANDEM_WAITING ) {

		//time has reach the limit, lets send a notification to the partner to come do the tandem
		if ( $mailing->tandemTimeOutNotificationEmail( $id, $user_obj->id, $force_select_room, $open_tool_id, $sent_url,
			$userab ) ) {
			$return->emailsent = 1;
		}
	}
	if (isset($return->result) && $return->result != 'ok') {
		$feedback = $gestorBD->getFeedbackByIdTandemIdUser( $id, $user_obj->id );
		$return->accepted_audio = false;
		$return->accepted_video = false;
		if ($feedback) {
			$return->accepted_audio = !empty( $feedback['accepted_audio_datetime'] );
			$return->accepted_video = !empty( $feedback['accepted_video_datetime'] );
		}
	}
}

echo json_encode( $return );
