<?php

/**************************************************************************************************************** */
/********** M A N A G E   W A I T I N G    R O O M    F O R     D B    I N S E R T I O N S ********************** */
/**************************************************************************************************************** */  
     
//Here we will be checking if there is there is on the waiting room we can do a tandem with.
require_once dirname(__FILE__) . '/classes/gestorBD.php';  

if(isset($_POST['language'])){
    $language = $_POST['language'];
}

if(isset($_POST['courseID'])){
    $courseID = $_POST['courseID'];
}

if(isset($_POST['exercisesID'])){
    $exerciseID = $_POST['exercisesID'];    
}

if(empty($language) or empty($courseID) or empty($exercisesID))
	echo json_encode(array("result" => "Missing parameter")); die();

$gestordb = new GestorBD();

$response = $gestordb->updateWaitingDB($language,$courseID, $exercisesID);

echo json_encode($response);



