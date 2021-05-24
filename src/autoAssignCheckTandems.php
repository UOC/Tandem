<?php
//Here we will be checking if there is there is on the waiting room we can do a tandem with.

require_once dirname( __FILE__ ) . '/classes/gestorBD.php';
require_once dirname( __FILE__ ) . '/classes/utils.php';
if ( ! isset( $_SESSION ) ) {
	session_start();
}

if ( isset( $_REQUEST['otherlanguage'] ) ) {
	$otherlanguage = $_REQUEST['otherlanguage'];
}
if ( isset( $_REQUEST['courseID'] ) ) {
	$courseID = $_REQUEST['courseID'];
}
if ( isset( $_REQUEST['exercisesID'] ) ) {
	$exercisesID = $_REQUEST['exercisesID'];
}

if ( isset( $_REQUEST['user_id'] ) ) {
	$user_id = $_REQUEST['user_id'];
}
if ( isset( $_REQUEST['id_resource_lti'] ) ) {
	$id_resource_lti = $_REQUEST['id_resource_lti'];
}

if ( empty( $courseID ) ) {
	echo json_encode( array( 'result' => 'Missing parameter course.' ) );
	die();
}
$gestorBD = new GestorBD();
$gestorBD->update_last_action_time( $user_id, $courseID );
if ( isset( $_POST['action'] ) && $_POST['action'] === 'botnotify' ) {
	$total_users_waiting = $gestorBD->getUsersWaitingByLanguage( $courseID, '', true );
	$params              = array(
		'users'    => $total_users_waiting,
		'token'    => '{2+.*H@-5#++)e&u',
		'lang'     => $_SESSION[ LANG ],
		'instance' => $courseID
	);
	echo doRequest( 'https://pilots04.elearnlab.org/tandem_bot/api/tandem_waiting', true, $params );

	die();
}

if ( empty( $otherlanguage ) ) {
	echo json_encode( array( 'result' => 'Missing parameter language.' ) );
	die();
}

$response = $gestorBD->checkForTandems( $exercisesID, $courseID, $otherlanguage, $user_id,
	$_SESSION[ USE_WAITING_ROOM_NO_TEAMS ], $_SESSION[ USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE ] );
$debug    = false;
//error_reporting(E_ALL);
if ( $debug ) {
	error_log( "checking for exercise " . $exercisesID . " with user " . $user_id . " otherlanguage " . $otherlanguage );
}
//if we have a positive response , it means we have found someone to do a tandem with.
if ( $response ) {

	if ( $debug ) {
		error_log( "has response" );
	}
	$response = $response[0];
	if ( isset( $response['tandem_id'] ) ) {
		$tandem_id = $response['tandem_id'];
	} else {
		$user_language = $_SESSION[ LANG ];
        $_SESSION[TANDEM_NUMBER_FIELD] = -1;
		$tandem_id     = $gestorBD->createTandemFromWaiting( $response, $user_id, $id_resource_lti,
			$_SERVER['HTTP_USER_AGENT'], $_SESSION[ USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE ] );
	}

	$gestorBD->deleteFromWaitingRoom( $user_id, $tandem_id );
	echo json_encode( array( "tandem_id" => $tandem_id ) );
	if ( $debug ) {
		error_log( "RESPONSE tandem ID " . $tandem_id );
	}
} else {
	if ( $debug ) {
		error_log( "NOOO has response" );
	}
	//check if I had been invited
	$tandem_id = $gestorBD->checkForInvitedTandems( $user_id, $exercisesID, $courseID );
	if ( $tandem_id ) {
		if ( $debug ) {
			error_log( "tandem ID " . $tandem_id );
		}
		$gestorBD->deleteFromWaitingRoom( $user_id, $tandem_id );
		echo json_encode( array( "tandem_id" => $tandem_id ) );
	} else {
		//then update my timestamp
		$gestorBD->updateMyWaitingTime( $user_id );

		if ( $debug ) {
			error_log( "updated my waiting " );
		}
	}
}

