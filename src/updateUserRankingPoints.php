<?php
require_once 'classes/lang.php';
require_once 'classes/gestorBD.php';

$gestorBD = new GestorBD();

//$gestorBD->updateUserRankingPoints(5406);
if ( ! empty( $_REQUEST['course_id'] ) ) {
	$course_id = $_REQUEST['course_id'];
} else {
	die( "Falta el course_id" );
}

if ( ! empty( $_REQUEST['user_id'] ) ) {
	$user_id = $_REQUEST['user_id'];
} else {
	die( "falta el user_id" );
}

if ( ! empty( $_REQUEST['lang'] ) ) {
	$lang = $_REQUEST['lang'];
} else {
	die( "falta el lang" );
}

echo $gestorBD->updateUserRankingPoints( $user_id, $course_id, false, true );
//$gestorBD->updateAllUsersRankingPoints(255);

//$gestorBD->updateUserRankingPoints(5726);

