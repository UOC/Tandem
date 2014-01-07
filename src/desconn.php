<?php

require_once 'classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/constants.php';
session_start();

//Recibimos las variables desde la url
$room = $_GET["room"];
if(is_file($room.".xml")) unlink($room.".xml");


$user_obj = $_SESSION['current_user'];
$id_user_host = $user_obj->id;
$id_course = $_SESSION[COURSE_ID];

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
$dataHour = '0000-00-00 00:00:00';
setlastAccessTandemStatus($id_user_host,$id_course,$dataHour);

?>