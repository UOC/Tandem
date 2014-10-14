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

	//ok first lets see if this tandem isnt already created by the user.
	$sql= "select id from tandem where id_exercise = ".$response['id_exercise']." 
			and id_course = ".$response['id_course']."
			and (id_user_host = ".$response['guest_user_id']." and id_user_guest = ".$user_id.")";
	$result = $gestordb->consulta($sql);

	//if the tandem was already created by the other user, then we are the guests.
    if ($gestordb->numResultats($result) > 0){ 

    	//to make sure we will delete ourselfs from all the waiting rooms
    	$gestordb->deleteFromWaitingRoom($user_id);
        $result = $gestordb->obteComArray($result);
        echo json_encode(array("tandem_id" => $result[0]['id']));
    }else{ 
    	//the tandem is not yet created, lets created it and we will be he host.
		$tandem_id = $gestordb->register_tandem($response['id_exercise'], $response['id_course'], $id_resource_lti, $user_id, $response['guest_user_id'], "", "");
 		
		$gestordb->deleteFromWaitingRoom($user_id);
 		echo json_encode(array("tandem_id" => $tandem_id));
 		die();
	}

}

