<?php
/*
 * 
 * Campus Project (Campus Virtual de Programari Lliure). http://www.campusproject.org
 * Welcome Application. A Campus Project application example.   
 *
 * Copyright (c) 2010 Universitat Oberta de Catalunya
 * 
 * This file is part of Campus Virtual de Programari Lliure (CVPLl).  
 * CVPLl is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation; either version 2 of the License, or 
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * General Public License for more details, currently published 
 * at http://www.gnu.org/copyleft/gpl.html or in the gpl.txt in 
 * the root folder of this distribution.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.   
 *
 *
 * Author: Antoni Bertran Bellido (antoni@tresipunt.com)
 * Date: June 2010
 *
 * Project email: campusproject@uoc.edu
 *
 * Description: This template of BLTI Provider, checks if is a valid signature of parameters, 
 * then if users exists in system updates (if not create), the same with courses and roles in a course 
 *
 */
ini_set("display_errors",0);
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/utils.php';

//Netegem la sessio
session_unset();

require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/constants.php';


$tandem_id = isset($_GET['tandem_id'])?$_GET['tandem_id']:false;
$user_id = isset($_GET['user_id'])?$_GET['user_id']:false;
$token = isset($_GET['token'])?$_GET['token']:false;
if (!$tandem_id || !$user_id || !$token) {
	die('Missing parameters');
}

try {
       
	$gestorBD = new gestorBD();
	$session_data = $gestorBD->getSessionData($tandem_id, $user_id, $token);

	if (!$session_data) {
		die('Can\'t found the user session');
	}

	$tandem = $gestorBD->obteTandem($tandem_id);
	if (!$tandem) {
		die('Tandem id not found');
	}
	$course_id = $tandem['id_course'];
	$user = $gestorBD->getUserB($user_id);
	if($user==null){
		die('User not found');
	}		
							
	$course = $gestorBD->get_course_by_id($course_id);
	if (!$course) {
		die('Course not found');
	}
	$waiting_room_select_room = $session_data['select_room'];
	$open_tool_id = $session_data['open_tool_id'];

	//$_SESSION[LANG] = lti_get_lang($context);
	//$_SESSION[LTI_CONTEXT] = serialize($context->getLTI_Context());
	$_SESSION[TANDEM_COURSE_FOLDER] = 'course/'.$course_id;
    $_SESSION[ID_RESOURCE] = $tandem['id_resource_lti'];
    $_SESSION[USE_WAITING_ROOM] = 1;
    $_SESSION[FORCE_SELECT_ROOM] = $waiting_room_select_room;
    $_SESSION[OPEN_TOOL_ID] = $open_tool_id;
    $_SESSION[WEEK] = false;
    $_SESSION[PREVIOUS_WEEK] = false;
    $_SESSION[CURRENT_TANDEM]= $tandem_id;
    $_SESSION[CURRENT_USER] = $user;
    $_SESSION[COURSE_ID] = $course_id;
	
	$redirectTo = $session_data['url_sent'];
	header ('Location: '.$redirectTo);

} catch (Exception $e) {
	die("Error");
}
