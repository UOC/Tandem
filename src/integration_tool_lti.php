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
require_once dirname(__FILE__).'/classes/lang.php';
require_once dirname(__FILE__).'/classes/utils.php';

$debug = false;
if (isset($_SESSION['debug']) && $_SESSION['debug'] == '1')
	$debug = true;

//Netegem la sessio
session_unset();

if ( ! $debug && isset($_GET['debug'])) {
	$debug = $_GET['debug']=='1';
	if ($debug)
	  $_SESSION['debug'] = '1';
}

$required_class = 'IMSBasicLTI/uoc-blti/bltiUocWrapper.php';
$exists = filexists_tandem ($required_class);
if (!$exists) {
	
	die('Required classes LTI not exists check the include_path is correct');
        
} 

require_once $required_class;
require_once 'IMSBasicLTI/utils/UtilsPropertiesBLTI.php';
require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';
require_once dirname(__FILE__).'/classes/constants.php';

if ( ! is_basic_lti_request() ) { 
		$good_message_type = $_REQUEST["lti_message_type"] == "basic-lti-launch-request";
		$good_lti_version = $_REQUEST["lti_version"] == "LTI-1p0";
		$resource_link_id = $_REQUEST["resource_link_id"];
		if ($good_message_type && $good_lti_version && !isset($resource_link_id) ) {
			$launch_presentation_return_url = $_REQUEST["launch_presentation_return_url"];
			if (isset($launch_presentation_return_url)) {
				header('Location: '.$launch_presentation_return_url);
				exit();
			}
		}
	die('LTI Authentication Failed, not valid request');
 }
    // See if we get a context, do not set session, do not redirect
    $context = new bltiUocWrapper(false, false);
    if ( ! $context->valid ) {
        die('LTI Authentication Failed, not valid request (make sure that consumer is authorized and secret is correct)');
    }
    
debugMessageIT('LTI call validated', $debug);


try {

	$gestorBD = new gestorBD();
	$tandemBLTI = new IntegrationTandemBLTI();
	//Getting BLTI data
	$user_obj = new stdClass();
	$user_obj->username = lti_get_username($context);
	$user_obj->fullname = $context->getUserName();
	$user_obj->name = isset($context->info['lis_person_name_given'])?$context->info['lis_person_name_given']:$user_obj->fullname;
	$user_obj->surname = isset($context->info['lis_person_name_family'])?$context->info['lis_person_name_family']:$user_obj->fullname;
	$user_obj->email = $context->getUserEmail();
	$user_obj->admin = strpos(strtolower($context->info['roles']), 'administrator')!==false;
	$user_obj->instructor = $context->isInstructor();
	$user_obj->icq = $tandemBLTI->getDataInfo($context, 'custom_icq');
	$user_obj->skype = $tandemBLTI->getDataInfo($context, 'custom_skype');
	$user_obj->yahoo = $tandemBLTI->getDataInfo($context, 'custom_yahoo');
	$user_obj->msn = $tandemBLTI->getDataInfo($context, 'custom_msn');
	$user_obj->points = rand ( 0, 1000 );
	$user_obj->lis_result_sourceid = $tandemBLTI->getDataInfo($context, LIS_RESULT_SOURCEID);
	//Icon
	$user_obj->image = $context->getUserImage();
	$_SESSION[LANG] = lti_get_lang($context);
	$_SESSION[LTI_CONTEXT] = serialize($context->getLTI_Context());
	
	
	$user = $gestorBD->get_user_by_username($user_obj->username);
	if (!$user) {
	
		$user_id = $gestorBD->register_user($user_obj->username, $user_obj->name, $user_obj->surname, $user_obj->fullname, $user_obj->email, $user_obj->image, $user_obj->icq, $user_obj->skype, $user_obj->yahoo, $user_obj->msn);
		if ($user_id) {
			$user = $gestorBD->get_user_by_username($user_obj->username);
		} else {
			show_error("register_user_error");
			$user=false;
		}
	} else {
		if (!$gestorBD->update_user($user_obj->username, $user_obj->name, $user_obj->surname, $user_obj->fullname, $user_obj->email, $user_obj->image, $user_obj->icq, $user_obj->skype, $user_obj->yahoo, $user_obj->msn))
    	{
    		show_error("updated_user_error");
    		$user = false;
    	}
    	 
    }
    if ($user) {
	    $user_id = $user['id'];
	    $user_obj->id = $user_id;
	    
	    
	    //Check if course exists
	    $course_name = $context->getCourseName();
	    $course_key = $context->getCourseKey();
	    	
	    $course = $gestorBD->get_course_by_courseKey($course_key);
	    if (!$course) {
	    	if (!$gestorBD->register_course($course_key, $course_name)) {
	    		show_error('lti:errorregistercourse');
	    	}
	    	else {
	    		$course = $gestorBD->get_course_by_courseKey($course_key);
	    	}
	    }
	    else {
	    	if (!$gestorBD->update_course($course_key, $course_name)) {
	    		show_error('lti:errorupdatingcourse');
	    	}
	    }
	    if ($course) {
	    	$course_id = $course['id'];
	    	//20120830 abertranb register the course folder
	    	$_SESSION[TANDEM_COURSE_FOLDER] = 'course/'.$course_id;
	    	//FIIIII
	    	if (!$gestorBD->join_course($course_id, $user_id, $user_obj->instructor, $user_obj->lis_result_sourceid)) {
	    		show_error('lti:errorjoincourse');
	    	}
		    $id_resource = sanitise_string($context->getResourceKey()); //Així es unica //Si no es per curs $context->getCourseKey();
		    //Gets the object of tandem if exists, if not this user is the host 
		    $tandem = false;
		    //disabled to select current tandem // $tandem = $gestorBD->is_invited_to_join($user_id, $id_resource, $course_id);
		    $_SESSION[ID_RESOURCE] = $id_resource;
		    if (!$tandem) {
		    	if (posa_osid_context_session($gestorBD, $course_id, $context)) {
					$user_obj->is_host = true;
		    	
		    		//Anem a seleccionar la room
					$_SESSION[CURRENT_USER] = $user_obj;
		    		$_SESSION[COURSE_ID] = $course_id;
		    	
		    		header ('Location: selectUserAndRoom.php');
		    	} 
		    	//sino ja mostrara error
		    } else {
				$user_obj->is_host = false;
		    	//T'han convidat
				$exercise = $tandem['name_xml_file'];
				$room = '';
				$room = sanitise_string($exercise.getTandemIdentifier($tandem['id'], $id_resource));
				$_SESSION[CURRENT_TANDEM] = $tandem['id'];
				//we need to identify the exercise
				//Now we try to get data course
				$data_exercise = $tandemBLTI->getDataExercise($exercise);
				$user_obj->id_resource = $id_resource;
				$user_obj->type_user = 'b';
	
				if(!is_file($room.".xml")) { 
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
				
				header ('Location: '.$data_exercise->classOf.'.php?room='.$room.'&user='.$user_obj->type_user.'&nextSample='.$data_exercise->nextSample.'&node='.$data_exercise->node.'&data='.$exercise);
	     	}
    	} else {
	    	show_error("error_getting_course");
	    }
    } else {
    	show_error("error_getting_user");
    }

} catch (Exception $e) {
	var_dump($e);
	die("Error");
}

?>