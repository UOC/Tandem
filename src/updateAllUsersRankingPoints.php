<?php 
require_once 'classes/lang.php';
require_once 'classes/gestorBD.php';

$gestorBD = new GestorBD();

//$gestorBD->updateUserRankingPoints(5406);
if(!empty($_REQUEST['course_id']))
	$course_id = $_REQUEST['course_id'];
else
 	die("Falta el course_id");

echo $gestorBD->updateAllUsersRankingPoints($course_id);


