<?php

require_once dirname(__FILE__).'/lang.php';
require_once dirname(__FILE__).'/constants.php';
require_once dirname(__FILE__).'/gestorBD.php';
require_once dirname(__FILE__).'/utils.php';

$gestorBDRegister = new GestorBD();

$user_register_obj = isset($_SESSION[CURRENT_USER])?$_SESSION[CURRENT_USER]:false;
$course_register_id = isset($_SESSION[COURSE_ID])?$_SESSION[COURSE_ID]:false;
$id_register_tandem = isset($_SESSION[CURRENT_TANDEM])?$_SESSION[CURRENT_TANDEM]:false;

if ($user_register_obj && $course_register_id && $id_register_tandem && isset($user_register_obj->id) && $user_register_obj->id>0) {

	//Regitrem total
	if (!$gestorBDRegister->registra_user_tandem($id_register_tandem, $user_register_obj->id, $is_final)) {
		die(show_error('Error updating tandem logged guest user'));
	}
	
	//Registrem la tasca
	//Mirem si hem de finalitzar l'anterior
	$number_task_old = isset($_SESSION[TANDEM_NUMBER_FIELD])?intval($_SESSION[TANDEM_NUMBER_FIELD],10):-1;
	
	$number_task = isset($_GET[TANDEM_NUMBER_FIELD])?intval($_GET[TANDEM_NUMBER_FIELD],10):-1;
	
	
	if ($number_task > 0) {
		$_SESSION[TANDEM_NUMBER_FIELD] = $number_task;
	} else {
		$number_task = isset($_SESSION[TANDEM_NUMBER_FIELD])?intval($_SESSION[TANDEM_NUMBER_FIELD],10):-1;
	}
	$task_changed = $number_task_old!=$number_task || $is_final;
	if ($number_task_old > 0) {
		if (!$gestorBDRegister->register_task_user_tandem($id_register_tandem, $user_register_obj->id, $number_task_old, $task_changed)) {
			die(show_error('Error updating tandem logged guest user'));
		}
	}
	
	if ($number_task > 0 && $task_changed) {
		if (!$gestorBDRegister->register_task_user_tandem($id_register_tandem, $user_register_obj->id, $number_task, $is_final)) {
			die(show_error('Error updating tandem logged guest user'));
		}	
	}
	
	//Registrem la pregunta ATENCIO comena am 0
	$number_task_question_old = isset($_SESSION[TANDEM_NUMBER_OF_NODE_FIELD])?intval($_SESSION[TANDEM_NUMBER_OF_NODE_FIELD],10):-1;
	$number_task_question = isset($_GET[TANDEM_NUMBER_OF_NODE_FIELD])?intval($_GET[TANDEM_NUMBER_OF_NODE_FIELD],10):-1;
	if ($number_task_question >= 0) {
		$_SESSION[TANDEM_NUMBER_OF_NODE_FIELD] = $number_task_question;
	} else {
		$number_task_question = isset($_SESSION[TANDEM_NUMBER_OF_NODE_FIELD])?intval($_SESSION[TANDEM_NUMBER_OF_NODE_FIELD],10):-1;
	}
	
	$task_question_changed = $task_changed || $number_task_question_old!=$number_task_question;
	//Mirem si cal finalitzar l'anterior
	if ($task_question_changed && $number_task_old>0 && $number_task_question_old>=0) {
		if (!$gestorBDRegister->register_task_question_user_tandem($id_register_tandem, $user_register_obj->id, $number_task_old, $number_task_question_old, $task_question_changed)) {
			die(show_error('Error updating tandem logged guest user'));
		}
	}
	
	if ($number_task_question<0 && $task_changed) {
		//Iniciem a 0 i salvem a sessio
		$number_task_question = 0;
		$_SESSION[TANDEM_NUMBER_OF_NODE_FIELD] = $number_task_question;
	}

	if ($number_task<=0) {
		$number_task = $number_task_old;
	}
	
	if ($number_task_question >= 0 && ($task_question_changed)) {
		if (!$gestorBDRegister->register_task_question_user_tandem($id_register_tandem, $user_register_obj->id, $number_task, $number_task_question, $is_final)) {
			die(show_error('Error updating tandem logged guest user'));
		}
	}
	
	
	$tandem_register = $gestorBDRegister->obteTandem($id_register_tandem);
	$id_resource_register = $_SESSION[ID_RESOURCE];
	//$user_obj->is_host = false;
	//T'han convidat
	$exercise_register = $tandem_register['name_xml_file'];
	$room_register = '';
	$room_register = sanitise_string($exercise_register.getTandemIdentifier($id_register_tandem, $id_resource_register));


	if(is_file($room_register.".xml")) {
		//Obtenim el xml i el guardem a la bd
		$xml_register = file_get_contents($room_register.".xml");
		$gestorBDRegister->registra_xml_tandem($id_register_tandem, $xml_register);
		if ($is_final) {
			delete_xml_file($room_register);
		}
	}
} else {
	if ($is_final) {
		$room_register = $_REQUEST['room'];
		delete_xml_file($room_register);
	}
}