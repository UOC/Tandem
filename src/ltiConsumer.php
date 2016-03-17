<?php
require_once('classes/constants.php');
require_once('classes/typeLTI.php');
require_once('classes/manageLTI.php');
require_once('classes/lang.php');
require_once('classes/utils.php');
check_user_session();
$id = $_REQUEST['id'];
$consumer = new TypeLTI($id);
if ($consumer->get('id')>0) {
	$user = $_SESSION[CURRENT_USER];
	// load course
	$g = new GestorBD();
	$course = $g->get_course_by_id($_SESSION[COURSE_ID]);	
	$lang = isset($_GET[LANG])?$_GET[LANG]:(isset($_SESSION[LANG])?$_SESSION[LANG]:false);
	if (!$lang) {
		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
	}
	// LTI consumer launch
	$manageLTI = new ManageLTI();
	echo $manageLTI->lti_consumer_launch($consumer, $user, $lang, array('course_id' => $_SESSION[COURSE_ID], 'course_title' => $course["title"]));
} else {
	show_error(Language::get('invalid_tool'));
}