<?php
require_once dirname(__FILE__).'/classes/lang.php';

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
 * Author: Juan Francisco Sánchez Ramos (jsanchezramos@uoc.edu)
 * Date: October 2010
 *
 * Project email: campusproject@uoc.edu
 *
 * Description: This template of BLTI Provider, checks if is a valid signature of parameters, 
 * then if users exists in system updates (if not create), the same with courses and roles in a course 
 *
 */

/**
* To enable debug you have to go 
* http://url-site/annotatie/integration_tool.php?debug=1
* and relaunch basicLTI call
*/
function debugMessageIT($message, $debug=false) {
	if ($debug) {
		echo "<p>$message</p><br>";	
	}
}

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
$exists=fopen ( $required_class, "r",1 );
if (!$exists) {
	
	die('Required classes BASIC LTI not exists check the include_path is correct');
        
} 

require_once $required_class;
require_once dirname(__FILE__).'/classes/IntegrationTandemBLTI.php';
require_once dirname(__FILE__).'/classes/gestorBD.php';

if ( ! is_basic_lti_request() ) { 
	die('BASIC LTI Authentication Failed, not valid request');
 }
    // See if we get a context, do not set session, do not redirect
    $context = new bltiUocWrapper(false, false);
    if ( ! $context->valid ) {
        die('BASIC LTI Authentication Failed, not valid request (make sure that consumer is authorized and secret is correct)');
    }
    
debugMessageIT('BLTI call validated', $debug);


try {

	$gestorBD = new gestorBD();
	$tandemBLTI = new IntegrationTandemBLTI();
	//Getting BLTI data
	$user_obj = new stdClass();
	$user_obj->username = $context->getUserKey();
	$user_obj->name = $context->getUserName();
	$user_obj->email = $context->getUserEmail();
	$user_obj->admin = strpos(strtolower($context->info['roles']), 'administrator')!==false;
	$user_obj->instructor = $context->isInstructor();
	$user_obj->is_host = $tandemBLTI->getDataInfo($context, 'custom_is_host')=='1';
	$user_obj->icq = $tandemBLTI->getDataInfo($context, 'custom_icq');
	$user_obj->skype = $tandemBLTI->getDataInfo($context, 'custom_skype');
	$user_obj->yahoo = $tandemBLTI->getDataInfo($context, 'custom_yahoo');
	$user_obj->msn = $tandemBLTI->getDataInfo($context, 'custom_msn');
	$user_obj->points = rand ( 0, 1000 );
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
		 
		$_SESSION[COURSE_ID] = $course_id;
	}
	$id_resource = $context->getResourceKey(); //Així es unica //Si no es per curs $context->getCourseKey();
	
	//we need to identify the exercise
	$exercise = isset($context->info['custom_exercise'])?$context->info['custom_exercise']:'';
	//we need to identify the custom_room, when this value is set we allow the users to add the exercise
	$custom_room = isset($context->info['custom_room_tandem'])?$context->info['custom_room_tandem']:false;
	$user_obj->custom_room = $custom_room;
	
	$room = '';
	if (isset($exercise) && strlen($exercise)>0) {
		//Now we try to get data course
		$data_exercise = $tandemBLTI->getDataExercise($exercise);
	
		//Ara cridem al createUser
		$DELIMITER = ':';
		if (strpos($id_resource, $DELIMITER)!==FALSE) {
			$id_resource = str_replace($DELIMITER, '_',$id_resource);
	//		$cArray = explode($DELIMITER, $id_resource);
	//		foreach ($cArray as $v){
	//			if ((int)$v>0){
	//				$id_resource = $v;
	//				break;
	//			}
	//		}
		}
		$room = $exercise.$id_resource;
	}
	$user_obj->id_resource = $id_resource;
	//Icon
	$profileicon = $context->getUserImage();
	$user_obj->image = $profileicon;
	
//	$link = curl_init();
//
//	if ($pos = strpos($profileicon, '&size=')) {
//		$profileicon = substr($profileicon, 0, $pos);
//	}
//	$file_image = dirname(__FILE__).'/images/user/'.$user_obj->username;
//	$fp = fopen($file_image, 'w');
//	curl_setopt($link, CURLOPT_FILE, $fp);
//	curl_setopt($link, CURLOPT_URL, $profileicon);
//	curl_setopt($link, CURLOPT_HEADER, 0); 
//	
//	if( ! $result = curl_exec($link)) 
//    { 
//    	//TODO mostrar l'error die(curl_error($link));
//    }  
//	
//	curl_close($link);
//	
//	if ($result) {
//		$profileicon_clas = array();
//		$profileicon_clas['name'] = $user_obj->username;
//		$profileicon_clas['type'] = mime_content_type($file_image);
//	}
	
	if (strlen($room)>0) {
	
		$user_obj->type_user = 'b';
		if(!is_file($room.".xml")) { $user_obj->type_user = 'a'; $tandemBLTI->makeXMLUser($user_obj,$room,$exercise);
		} else  $tandemBLTI->editXMLUser($user_obj,$room); 
		
		header ('Location: '.$data_exercise->classOf.'.php?room='.$room.'&user='.$user_obj->type_user.'&nextSample='.$data_exercise->nextSample.'&node='.$data_exercise->node.'&data='.$exercise);

	} else {
		
		//session_start();
		//Anem a seleccionar la room
		$_SESSION['current_user'] = $user_obj;
		
		header ('Location: selectRoom.php');
		
	}

} catch (Exception $e) {
	var_dump($e);
	die("Error");
}
?>