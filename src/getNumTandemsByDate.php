<?php
//Here we will be checking if there is there is on the waiting room we can do a tandem with.
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';  

$gestorBD = new GestorBD();
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
$date = !empty($_REQUEST['date']) ? $_REQUEST['date'] : date("d-m-Y");

$getNumtandemsByDate = $gestorBD->getNumtandemsByDate($date,$course_id);
echo json_encode(array("tandems" => $getNumtandemsByDate));