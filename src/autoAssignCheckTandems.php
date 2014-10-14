<?php
 ini_set("display_errors",1);
 error_reporting(E_ALL ^ E_DEPRECATED);
//Here we will be checking if there is there is on the waiting room we can do a tandem with.
require_once dirname(__FILE__) . '/classes/gestorBD.php';  

if(isset($_REQUEST['otherlanguage'])){
    $otherlanguage = $_REQUEST['otherlanguage'];
}
if(isset($_REQUEST['courseID'])){
    $courseID = $_REQUEST['courseID'];
}
if(isset($_REQUEST['exercisesID'])){
    $exercisesID = $_REQUEST['exercisesID'];    
}
if(isset($_REQUEST['user_id'])){
    $user_id = $_REQUEST['user_id'];    
}
if(isset($_REQUEST['id_resource_lti'])){
    $id_resource_lti = $_REQUEST['id_resource_lti'];    
}
if(empty($otherlanguage) or empty($courseID) or empty($exercisesID)){
	echo json_encode(array("result" => "Missing parameter")); die();
}

$gestordb = new GestorBD();
$response = $gestordb->checkForTandems($exercisesID, $courseID, $otherlanguage);

//if we have a positive response , it means we have found someone to do a tandem with.
if($response){
	
	$response = $response[0];
	$tandem_id = $gestordb->createTandemFromWaiting($response,$user_id,$id_resource_lti);
	echo json_encode(array("tandem_id" => $tandem_id));
	
} else {
	//then update my timestamp
	$gestordb->updateMyWaitingTime($user_id, $exercisesID, $courseID, $otherlanguage);
}


