<?php
require_once dirname(__FILE__).'/../classes/lang.php';
require_once dirname(__FILE__).'/../classes/utils.php';
require_once dirname(__FILE__).'/../classes/constants.php';
require_once dirname(__FILE__).'/../classes/gestorBD.php';
require_once dirname(__FILE__).'/../classes/IntegrationTandemBLTI.php';

$id = isset($_REQUEST['id'])?intval($_REQUEST['id'],10):0;
$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$force_select_room = isset($_SESSION[FORCE_SELECT_ROOM]) ? $_SESSION[FORCE_SELECT_ROOM] : false;
$open_tool_id = isset($_SESSION[OPEN_TOOL_ID]) ? $_SESSION[OPEN_TOOL_ID] : false;
$sent_url = isset($_REQUEST['sent_url']) ? base64_decode($_REQUEST['sent_url']) : ''; 
$userab = isset($_REQUEST['userab']) ? $_REQUEST['userab'] : ''; 
$tandem = false;
$gestorBD = new GestorBD();
$return = new stdclass();
$return->result = 'error';
$timePassed = 0;

if ($id>0) {
	$tandem = $gestorBD->obteTandem($id); 
}
if ($tandem) {	  	
	
	//lets update the session_user
  if( !empty($user_obj->id) && !empty($force_select_room) && !empty($open_tool_id) && !empty($sent_url) )
	 $timePassed = $gestorBD->updateSessionUser($id,$user_obj->id,$force_select_room,$open_tool_id,$sent_url);

  	if($gestorBD->checkTandemSession($id)){
  		if (isset($_SESSION[ID_FEEDBACK]) && $_SESSION[ID_FEEDBACK]>0) {
  			$_SESSION[ID_EXTERNAL] = $gestorBD->getFeedbackExternalIdTool($id);
  		}  		
  		$return->result = "ok";
  	}elseif($timePassed > MAX_TANDEM_WAITING){
  		
  		//time has reach the limit, lets send a notification to the partner to come do the tandem
  		if($gestorBD->TandemTimeOutNotificationEmail($id,$user_obj->id,$LanguageInstance,$force_select_room,$open_tool_id,$sent_url,$userab))
  		$return->emailsent = 1;

    }	
    
}

echo json_encode($return);