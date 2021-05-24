<?php

require_once __DIR__ . '/classes/lang.php';
require_once __DIR__ . '/classes/utils.php';
require_once __DIR__ . '/classes/constants.php';
require_once __DIR__ . '/classes/gestorBD.php';

$user_register_obj  = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$course_register_id = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : - 1;
$id_register_tandem = isset( $_SESSION[ CURRENT_TANDEM ] ) ? $_SESSION[ CURRENT_TANDEM ] : - 1;
$user_id            = - 1;
if ( ! empty( $_POST['url'] ) && $user_register_obj && isset( $user_register_obj->id ) && $user_register_obj->id > 0 ) {
	$user_id = $user_register_obj->id;
	if ( ! isset( $gestorBD ) ) {
		$gestorBD = new GestorBD();
	}
	if (isset($_POST['url'])) {
		$url       = $_POST['url'];
		$url_parts = parse_url( $url );
		$page      = basename( $url_parts['path'] );
		$params    = json_encode( $_POST );
	} else {
		$page = basename($_SERVER['PHP_SELF']);
		$params = json_encode($_GET);
	}

	$gestorBD->logAction( $user_id, $course_register_id, $id_register_tandem, $page, $params );
}