<?php
//Here we will be checking if there is there is on the waiting room we can do a tandem with.
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';  

$gestorBD = new GestorBD();
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

$active_tandems = 0;
if (isset($_POST['active_tandems']) && $_POST['active_tandems']==1) {
    $active_tandems = $gestorBD->currentActiveTandems($course_id);
}

$getUsersWaitingEs = $gestorBD->getUsersWaitingByLanguage($course_id,"es_ES");
$getUsersWaitingEn = $gestorBD->getUsersWaitingByLanguage($course_id,"en_US");
echo json_encode(array("users_en" => $getUsersWaitingEn,
		"users_es" => $getUsersWaitingEs,
    "active_tandems" => $active_tandems));