<?php
//Here we will be checking if there is there is on the waiting room we can do a tandem with.
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';  

$gestorBD = new GestorBD();
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$ok = $gestorBD->moveUserToHistory(-1, $user_obj->id);
echo json_encode(array("ok" => $ok));