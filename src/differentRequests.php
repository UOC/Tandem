<?php

require_once dirname(__FILE__) . '/classes/gestorBD.php';  


if(isset($_POST['language'])){
    $language = $_POST['language'];
}

if(isset($_POST['courseID'])){
    $courseID = $_POST['courseID'];
}

if(isset($_POST['userID'])){
    $userID = $_POST['userID'];
}

if(isset($_POST['typeClose'])){
    $typeClose = $_POST['typeClose'];
}


$oWiewport = new GestorBD();



$ok = $oWiewport->userIsNoWaitingMore($language,$courseID,$userID,$typeClose,$tandemID=0);


//print_r($ok);



