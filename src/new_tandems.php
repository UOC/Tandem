<?php 
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';

$user_obj = isset($_SESSION[CURRENT_USER])?$_SESSION[CURRENT_USER]:false;

$course_id = isset($_SESSION[COURSE_ID])?$_SESSION[COURSE_ID]:false;

require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';

header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

header ("content-type: text/xml");
echo '<?xml version="1.0" encoding="utf-8"?>';
if (!$user_obj || !$course_id) {
	//Tornem a l'index
} else {
echo '<tandems>';
	$id_resource_lti = $_SESSION[ID_RESOURCE];
	$id_last_insert = isset($_REQUEST['id'])?$_REQUEST['id']:0;
	$gestorBD = new gestorBD();
	$pending_invitations = $gestorBD->get_new_invited_to_join($id_last_insert, $user_obj->id, $id_resource_lti, $course_id, true);
	if ($pending_invitations){
		foreach ($pending_invitations as $tandem) {
		echo '<tandem>';
			echo '<id>'.$tandem['id'].'</id>';
			echo '<created>'.$tandem['created'].'</created>';
			echo '<nameuser>'.$tandem['surname'].', '.$tandem['firstname'].'</nameuser>';
			echo '<exercise>'.$tandem['name'].'</exercise>';
		echo '</tandem>';
		}
	}
echo '</tandems>';
} 