<?php

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../classes/mailingClient.php';
require_once __DIR__ . '/../classes/IntegrationTandemBLTI.php';

$id = isset($_SESSION['current_tandem'])?$_SESSION['current_tandem']:0;
$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$tandem = false;
$gestorBD = new GestorBD();
$mailing = new MailingClient($gestorBD, $LanguageInstance);
$return = new stdclass();
$return->result = 'error';
$timePassed = 0;

if ($id>0) {
	$tandem = $gestorBD->obteTandem($id); 
}
if ($tandem) {
    $force_select_room = isset($_SESSION[FORCE_SELECT_ROOM]) ? $_SESSION[FORCE_SELECT_ROOM] : false;
    $open_tool_id = isset($_SESSION[OPEN_TOOL_ID]) ? $_SESSION[OPEN_TOOL_ID] : false;
    $sent_url = isset($_SESSION['sent_url']) ? $_SESSION['sent_url'] : '';
    $userab = isset($_SESSION['userab']) ? $_SESSION['userab'] : '';
    $gestorBD->updateSessionUser($id,$user_obj->id,$force_select_room,$open_tool_id,$sent_url);
    // Time has reach the limit, lets send a notification to the partner to come do the tandem.
    if ($mailing->tandemTimeOutNotificationEmail($id, $user_obj->id, $force_select_room, $open_tool_id, $sent_url, $userab)) {
        $return->emailsent = 1;
    }
}

echo json_encode($return);
