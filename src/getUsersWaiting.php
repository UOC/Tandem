<?php
//Here we will be checking if there is there is on the waiting room we can do a tandem with.
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';  

$gestorBD = new GestorBD();
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
$lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : false;

$details = $gestorBD->getUsersDetailsWaitingByLang($course_id, $lang);
echo json_encode($details);