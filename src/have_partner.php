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
	$id = isset($_REQUEST['id'])?$_REQUEST['id']:0;
	$gestorBD = new gestorBD();
	$tandem = $gestorBD->obteTandem($id);
	if ($tandem && $tandem['id_user_guest']>0){
		
		echo '<tandem>';
			echo '<id>'.$tandem['id'].'</id>';
			
		echo '</tandem>';
		
	}
echo '</tandems>';
} 