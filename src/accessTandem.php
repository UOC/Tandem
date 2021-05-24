<?php
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/utils.php';
require_once dirname(__FILE__).'/classes/constants.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';

$id = intval($_REQUEST['id'],10);
$is_roulette = isset($_REQUEST['is_roulette'])?intval($_REQUEST['is_roulette'],10)===1:false;
$tandem = false;
$gestorBD = new GestorBD();
if ($id>0) {
	$tandem = $gestorBD->obteTandem($id);
}
$user_obj = $_SESSION[CURRENT_USER];
if ($tandem) {
	$_SESSION[CURRENT_TANDEM] = $id;
	$id_resource = $_SESSION[ID_RESOURCE];
	$user_obj->is_host = false;
	//T'han convidat
	$exercise = $tandem['name_xml_file'];
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
	if ($user_obj->id!=$tandem['id_user_host']) {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (!$gestorBD->update_user_guest_tandem($tandem['id'], $user_agent)) {
			die(show_error('Error updating tandem logged guest user'));
		}
        $_SESSION[TANDEM_NUMBER_FIELD] = -1;
		if (!$gestorBD->update_user_guest_tandem_others($tandem)) {
			die(show_error('Error updating tandem logged guest user'));
		}
	}
	if (isset($_SESSION[USE_WAITING_ROOM]) && $_SESSION[USE_WAITING_ROOM]==1 && isset($_GET['return_id'])) {
		//Lets go to insert the current tandem data
		$user_language = $_SESSION[LANG];
		$other_language = ($user_language == "es_ES") ? "en_US" : "es_ES";
		$id_partner = $tandem['id_user_guest']==$user_obj->id?$tandem['id_user_host']:$tandem['id_user_guest'];
		$id_feedback = $gestorBD->createFeedbackTandem($tandem['id'], $_GET['return_id'], $user_obj->id, $user_language, $id_partner, $other_language);
		if (!$id_feedback) {
			die ($LanguageInstance->get('There are a problem storing data, try it again'));
		}
		$_SESSION[ID_FEEDBACK] = $id_feedback;
		$_SESSION[ID_EXTERNAL] = $_GET['return_id'];
	}
    include_once __DIR__ .'/tandemLog.php';

	header ('Location: '.$data_exercise->classOf.'.php?room='.$room.'&user='.$user_obj->type_user.'&nextSample='.$data_exercise->nextSample.'&node='.$data_exercise->node.'&data='.$exercise.'&is_roulette='.$is_roulette);
} else {
	echo $LanguageInstance->get('no estas autoritzat');
}
