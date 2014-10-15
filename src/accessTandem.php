<?php
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/utils.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';

$id = intval($_REQUEST['id'],10);
$tandem = false;
$gestorBD = new GestorBD();
if ($id>0) {
	$tandem = $gestorBD->obteTandem($id); 
}
$user_obj = $_SESSION['current_user'];
if ($tandem) {
	$_SESSION[CURRENT_TANDEM] = $id;
	$id_resource = $_SESSION[ID_RESOURCE];
	$user_obj->is_host = false;
	//T'han convidat
	$exercise = $tandem['name_xml_file'];
	$room = '';
	$room = sanitise_string($exercise.getTandemIdentifier($id, $id_resource));
	$tandemBLTI = new IntegrationTandemBLTI();
	//we need to identify the exercise
	//Now we try to get data course
	$relative_path = isset($tandem['relative_path']) && strlen($tandem['relative_path'])>0 ? $tandem['relative_path'].DIRECTORY_SEPARATOR:'';
	$data_exercise = $tandemBLTI->getDataExercise($exercise, true, $relative_path);
	$user_obj->id_resource = $id_resource;
	$user_obj->type_user = 'b';

	if(!is_file(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml")) {
		$user_obj->type_user = 'a';
		$tandemBLTI->makeXMLUser($user_obj,$room,$exercise);
	} else {
		$tandemBLTI->editXMLUser($user_obj,$room);
	}
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (!$gestorBD->update_user_guest_tandem($tandem['id'], $user_agent)) {
		die(show_error('Error updating tandem logged guest user'));
	}
	if (!$gestorBD->update_user_guest_tandem_others($tandem)) {
		die(show_error('Error updating tandem logged guest user'));
	}
	$extra_params = isset($_GET['not_init'])?'&not_init='.$_GET['not_init']:'';
	if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1 && isset($_GET['return_id'])) {
		//Lets go to insert the current tandem data
		$user_language = $_SESSION[LANG];
		$other_language = ($user_language == "es_ES") ? "en_US" : "es_ES";
		$id_partner = $tandem['id_user_guest']==$user_obj->id?$tandem['id_user_host']:$tandem['id_user_guest'];
		$id = $gestorBD->createFeedbackTandem($tandem['id'], $_GET['return_id'], $user_obj->id, $user_language, $id_partner, $other_language);
		if (!$id) {
			die ($LanguageInstance->get('There are a problem storing data, try it again'));		
		}
	}
	

	header ('Location: '.$data_exercise->classOf.'.php?room='.$room.'&user='.$user_obj->type_user.'&nextSample='.$data_exercise->nextSample.'&node='.$data_exercise->node.'&data='.$exercise.$extra_params);
} else {
	echo $LanguageInstance->get('no estas autoritzat');
}