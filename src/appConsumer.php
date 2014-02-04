<?php
require_once('classes/constants.php');
require_once('classes/typeApp.php');
require_once('classes/manageRemoteTool.php');
require_once('classes/lang.php');
require_once('classes/utils.php');
check_user_session();

$id = $_REQUEST['id'];
$consumer = new TypeApp($id);
if ($consumer->get('id')>0) {
	if (isset($_SESSION[CURRENT_USER])) {
		$user = $_SESSION[CURRENT_USER];
		$appName 				= $consumer->get('name');
		$endpoint 				= $consumer->get('toolurl');
		$launchurl 				= isset($_REQUEST['launchurl'])?$_REQUEST['launchurl']:'';
		$overwriteUrl = (isset($_REQUEST["overwriteUrl"]) && "1" === $_REQUEST["overwriteUrl"]) && !empty($launchurl) && strlen($launchurl)>0;
		if ($overwriteUrl)
			$endpoint = $launchurl;
			
		//TODO mirar d'agafar si es en popup o no
		$show_pop_up			= $consumer->get('launchinpopup')==1;
		if ($user->session_id)
			$endpoint = str_replace(SESSION_VARIABLE, $user->session_id, $endpoint);
		if ($user->domain_id)
			$endpoint = str_replace(DOMAIN_VARIABLE, $user->domain_id, $endpoint);
		
		header ('Location: '.$endpoint);
		
	} else {
		show_error(Language::get('invalid_session'));
		exit();
	}
} else {
	show_error(Language::get('invalid_tool'));
}