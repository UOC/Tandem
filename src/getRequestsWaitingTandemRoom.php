<?php

/**************************************************************************************************************** */
/********** M A N A G E   W A I T I N G    R O O M    F O R     D B    I N S E R T I O N S ********************** */
/**************************************************************************************************************** */  
     
require_once dirname(__FILE__) . '/classes/gestorBD.php';  

if(isset($_POST['language'])){
    $language = $_POST['language'];
}




if(isset($_POST['courseID'])){
    $courseID = $_POST['courseID'];
}

if(isset($_POST['exerciseID'])){
    $exerciseID = $_POST['exerciseID'];
    
}

if(isset($_POST['userID'])){
    $userID = $_POST['userID'];
}

if(isset($_POST['ltiID'])){
    $idRscLti = $_POST['ltiID'];
}

if(isset($_POST['onlyExID'])){
    $onlyExID = $_POST['onlyExID'];
}
if(isset($_POST['is_tandem'])){
    $is_tandem = $_POST['is_tandem']==1;
}else
$is_tandem = false;

$oWiewport = new GestorBD();


//waiting

$RESPONSE = array();

$RESPONSE = $oWiewport->updateWaitingDB($language,$courseID, $exerciseID,$userID,$idRscLti,$onlyExID,$is_tandem);

echo json_encode($RESPONSE);

//tandem

//$aTandemDB=$oWiewport->updateTandemDB($language, $courseID);


//Anem a fer els métodes i ja veurem com passem els diferents paràmetres !!


//$mWT = array('waiting'=>$aWaitingDB,'tandem'=>$aTandemDB);



//$oJson=json_encode($mWT,JSON_FORCE_OBJECT);


//echo $oJson;



