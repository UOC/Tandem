<?PHP
require_once( 'config.inc.php' );
require_once dirname( __FILE__ ) . '/classes/constants.php';
require_once dirname( __FILE__ ) . '/classes/lang.php';


$user_obj = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;

$course_id = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : false;

require_once dirname( __FILE__ ) . '/classes/IntegrationTandemBLTI.php';


if ( ! $user_obj || ! $course_id ) {

} else {
	$room = isset( $_REQUEST['room'] ) ? $_REQUEST['room'] : false;
	if ( $room ) {
		$id_register_tandem = isset( $_SESSION[ CURRENT_TANDEM ] ) ? $_SESSION[ CURRENT_TANDEM ] : - 1;
		// tandem_id value on session sometimes goes to -1 because the user has acceess to LTI tool again the we try to get it from
		if ( $id_register_tandem == - 1 ) {
			$exploded           = explode( '_', $room );
			$id_register_tandem = intval( end( $exploded ) );
			if ( $id_register_tandem > 0 ) {
				$_SESSION[ CURRENT_TANDEM ] = $id_register_tandem;
			}
		}
		$current_time = time();
//every 20 seconds we update the tandem total time
		if ( ! isset( $_SESSION['udpated_tandem'] ) || ( $_SESSION['udpated_tandem'] < ( $current_time - 20 ) ) ) {
			$_SESSION['udpated_tandem'] = $current_time;
			$is_final                   = false;
			include_once( dirname( __FILE__ ) . '/classes/register_action_user.php' );
		}
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );


		header( "content-type: text/xml" );
		$xml = '';
		if ( file_exists( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . '.xml' ) ) {
			$xml = file_get_contents( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . '.xml' );
		} else {
			error_log( "Could not find file $room.xml", 0 );
		}
		echo $xml;
	} else {
		error_log( "can not get room in check when tandem_id was -1!!!" );
	}

}
