<?PHP 
require_once('config.inc.php');
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/lang.php';


$user_obj = isset($_SESSION[CURRENT_USER])?$_SESSION[CURRENT_USER]:false;

$course_id = isset($_SESSION[COURSE_ID])?$_SESSION[COURSE_ID]:false;

require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';


if (!$user_obj || !$course_id) {

} else {
	header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
	header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
	header( 'Cache-Control: post-check=0, pre-check=0', false ); 
	header( 'Pragma: no-cache' ); 


	$room = $_REQUEST['room'];
	header ("content-type: text/xml");
	$xml = '';
	if (file_exists(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.'.xml')) {
		$xml = file_get_contents(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.'.xml');
	} else {
		error_log("Could not find file $room.xml", 0);	
	}
	echo $xml;
}