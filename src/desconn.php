<?php

require_once dirname(__FILE__).'/classes/utils.php';
require_once 'classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/constants.php';
session_start();

//Recibimos las variables desde la url
$room = PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$_GET["room"];
//Avoid file deletion
//if(is_file($room.".xml")) unlink($room.".xml");


$user_obj = $_SESSION['current_user'];
$id_user_host = $user_obj->id;
$id_course = $_SESSION[COURSE_ID];
$id_user_guest = isset($_SESSION[ID_USER_GUEST])?$_SESSION[ID_USER_GUEST]:0;

function setinTandemStatus($user,$id_course,$status){
	$gestorBD = new GestorBD();
	$val = $gestorBD->set_userInTandem($user,$id_course,$status);
	//error_log("User:".$user." - course:".$id_course."status:".$status);
	return $val;
}
function setlastAccessTandemStatus($user,$id_course,$date){
	$gestorBD = new GestorBD();
	$val = $gestorBD->set_userLastAccess($user,$id_course,$date);
	//error_log("User:".$user." - course:".$id_course."date:".$date);
	return $val;
}

setinTandemStatus($id_user_host,$id_course,0);
setinTandemStatus($id_user_guest,$id_course,0);
$dataHour = '0000-00-00 00:00:00';
setlastAccessTandemStatus($id_user_host,$id_course,$dataHour);
setlastAccessTandemStatus($id_user_guest,$id_course,$dataHour);

$return = new stdclass();
$return->result = 'error';

if (isset($_REQUEST['close_session']) && $_REQUEST['close_session']==1
	&& isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1
	&& isset($_SESSION[ID_FEEDBACK]) && $_SESSION[ID_FEEDBACK]>0) { 
	//get the feedback url and call if it needed using curl
	$gestorBD = new GestorBD();
	$feedbackDetails = $gestorBD->getFeedbackDetails($_SESSION[ID_FEEDBACK]);
	if ($feedbackDetails) {
		$end_external_service = $feedbackDetails->end_external_service;
		if ($end_external_service && strlen($end_external_service)>0) {
			doRequest($end_external_service, false);
		}
	}
	
}

?>