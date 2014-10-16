<?php

class ManageLTI {
	
	function lti_consumer_launch($consumer, $user, $locale, $extra_custom_params = array()) {
		
		if (!class_exists("bltiUocWrapper")) {
			if (file_exists(dirname(__FILE__).'/../IMSBasicLTI/uoc-blti/bltiUocWrapper.php')) {
				require_once dirname(__FILE__).'/../IMSBasicLTI/uoc-blti/bltiUocWrapper.php';
				require_once dirname(__FILE__).'/../IMSBasicLTI/ims-blti/blti_util.php';
				require_once dirname(__FILE__).'/../IMSBasicLTI/utils/UtilsPropertiesBLTI.php';
			} else {
				require_once 'IMSBasicLTI/uoc-blti/bltiUocWrapper.php';
				require_once 'IMSBasicLTI/ims-blti/blti_util.php';
				require_once 'IMSBasicLTI/utils/UtilsPropertiesBLTI.php';
			}
		}
		require_once dirname(__FILE__).'/constants.php';
	
	//    $context = get_context_instance(CONTEXT_COURSE, $instance->course);
	    $username = $user->username;
	    if (strpos($username, $consumer->get('resourcekey'))===0) {
	    	$username = substr($username,strlen($consumer->get('resourcekey'))+1);
	    }
	    $role = isset($user->admin) && $user->admin?'Administrator':($user->instructor?'Instructor':'Learner');
		/*
	    $locale = '';//empty($USER->id) ? get_config('lang') : get_user_language($USER->id);
	    $locale = $this->basiclti_get_locale($locale);
		*/
	    $requestparams = array(
	        BasicLTIConstants::RESOURCE_LINK_ID => isset($_SESSION[CURRENT_TANDEM])?$_SESSION[CURRENT_TANDEM]:$consumer->get('id'),
	        BasicLTIConstants::RESOURCE_LINK_TITLE => $consumer->get('title'),
	        BasicLTIConstants::RESOURCE_LINK_DESCRIPTION => $consumer->get('description'),
	        BasicLTIConstants::USER_ID => $user->id,
	        BasicLTIConstants::ROLES => $role,
	        //TODO revisar si cal
	        BasicLTIConstants::CONTEXT_ID => isset($_SESSION[CURRENT_TANDEM])?$_SESSION[CURRENT_TANDEM]:$consumer->get('id'),
	        BasicLTIConstants::CONTEXT_LABEL => $consumer->get('title'),
	        BasicLTIConstants::CONTEXT_TITLE => $consumer->get('description'),
	        BasicLTIConstants::LAUNCH_PRESENTATION_LOCALE => $locale,
	    );
	    
	    // Send user's name and email data if appropriate
	    if ( $consumer->get('sendname') == 1 ||
	         ( $consumer->get('sendname') == 2 /*&& $instance->instructorchoicesendname == 1*/ ) ) {
	         	$name = $user->name;
	         	$lastname = $user->surname;
	         	$fullname = $name;
	         	if (strlen($fullname)>0 && strlen($name)>0) {
	         		$fullname .= ' ';
	         	}
	         	$fullname .= $lastname;
	         	
		        $requestparams[BasicLTIConstants::LIS_PERSON_NAME_GIVEN] =  $name;
		        $requestparams[BasicLTIConstants::LIS_PERSON_NAME_FAMILY] =  $lastname;
		        $requestparams[BasicLTIConstants::LIS_PERSON_NAME_FULL] =  $fullname;
	    }
	
	    if ( $consumer->get('sendemailaddr')  == 1 ||
	         ( $consumer->get('sendemailaddr') == 2 /*&& $instance->instructorchoicesendemailaddr == 1 */) ) {
	        $requestparams[BasicLTIConstants::LIS_PERSON_CONTACT_EMAIL_PRIMARY] =  $user->email;
	    }
	
	    $customstr = $consumer->get('customparameters');
	    if ( $customstr ) {
	        $custom = $this->blti_consumer_split_custom_parameters($customstr);
	        foreach ($custom as $key => $value) {
		    	$custom[$key] = self::replace_custom_variables($value);
	        }
	        $requestparams = array_merge($custom, $requestparams);
	    }
	    foreach ($extra_custom_params as $key => $custom_value) {
	    	$custom_value = self::replace_custom_variables($custom_value);
	    	$requestparams['custom_'.$key] = $custom_value;
	    } 
	    //TODO to get simple user 
	    $requestparams[BasicLTIConstants::LIS_PERSON_SOURCEDID] = $consumer->get('resourcekey').':'.$username;
	    //Adding all profile details canges of Antoni Bertran antoni@tresipunt.com
     	$requestparams[BasicLTIConstants::USER_IMAGE] = $user->image;
	    if ( $consumer->get('sendprofiledetails')==1 ||
	         ( $consumer->get('sendprofiledetails') == 2/* && $instance->instructorchoicesendprofiledet == 1 */) ) {
	         	//Pass the picture if exists
//                        $notfound = get_config('wwwroot').'thumb.php?type=profileicon&id='.$user->id;
	         	$requestparams['custom_username'] = $username;
	//         	require_once($CFG->dirroot.'/tag/lib.php');
	         	//TODO PRofiles
	         	/*$profile_fields = get_metadata($user->getGUID()); 
	         	foreach($profile_fields as $key => $field) {
	        		$requestparams['custom_'.$key] = $user->$field;
	         	}*/
	    }
	    
	    $org_id = $consumer->get('organizationid');
	    $endpoint = $consumer->get('toolurl');
	    $key = $consumer->get('resourcekey');
	    $secret = $consumer->get('password');
	    $debug = $consumer->get('debugmode');
	    $submit_text = 'Launch';
	    $height = $consumer->get('preferheight');
	    $launch = $consumer->get('launchinpopup');
	    $callbackUrl = '';//$consumer->callbackUrl;
	    //TODO fer configurable base64
	    $requestparams['custom_lti_message_encoded_base64'] = 1;
	    $requestparams = $this->encodeBase64($requestparams);
	    return $this->blti_consumer_sign_parameters($requestparams, $endpoint, $key, $secret, $submit_text, $org_id, $debug, $callbackUrl, $launch, $height);
	
	}

/**
 * Replace
 * %ID_TANDEM% that represents the current id.
 * %URL_TANDEM% that represents the url of.
 * @param  [type] $value [description]
 * @return [type]        [description]
 */
	function replace_custom_variables($value){
		if (strpos($value, '%ID_TANDEM%')!==false){
			$value = str_replace('%ID_TANDEM%', $_SESSION[CURRENT_TANDEM], $value);
		}
		if (strpos($value, '%EXTERNAL_ID%')!==false){
			$value = str_replace('%EXTERNAL_ID%', $_SESSION[ID_EXTERNAL], $value);
		}
		if (strpos($value, '%URL_TANDEM%')!==false){
			require_once(dirname(__FILE__).'/utils.php');
			$cur_url = curPageURL();
			$parts=parse_url($cur_url);
			
			$path_parts=explode('/', $parts['path']);
			unset($path_parts[count($path_parts)-1]);
			$value = str_replace('%URL_TANDEM%', $parts['scheme'].'://'.$parts['host'], $value);
			
		}

		return $value;
	}
	
	/**
	* Data submitter are in base64 then we have to decode
	* @author Antoni Bertran (antoni@tresipunt.com)
	* @param $info array
	*/
	function encodeBase64($info) {
		if (isset($info['custom_lti_message_encoded_base64']) && $info['custom_lti_message_encoded_base64']==1) {
			$keysNoEncode = array("lti_version", "lti_message_type", "tool_consumer_instance_description", "tool_consumer_instance_guid", "oauth_consumer_key", "custom_lti_message_encoded_base64", "oauth_nonce", "oauth_version", "oauth_callback", "oauth_timestamp", "basiclti_submit", "oauth_signature_method");
			foreach ($info as $key => $item){
				if (!in_array($key, $keysNoEncode))
				$info[$key] = base64_encode($item);
			}
		}
		return $info;
	}
	
	
	function blti_consumer_sign_parameters($requestparams, $endpoint, $key, $secret, $submit_text, $org_id, $debuglaunch=false, $callbackUrl, $launch=2, $height) {
	    // Make sure we let the tool know what LMS they are being called from
	    $requestparams["ext_lms"] = "tandem";
	
	    $makeiframe = $launch==1;
	    // Add oauth_callback to be compliant with the 1.0A spec
	    if (!isset($callbackUrl))
	    	$requestparams["oauth_callback"] = "about:blank";
		else
			$requestparams["oauth_callback"] = $callbackUrl;
	    
		$parms = signParameters($requestparams, $endpoint, "POST", $key, $secret, $submit_text, $org_id /*, $org_desc*/);
	
	    if ( $makeiframe ) {
	        $height = isset($instance->preferheight)?$instance->preferheight:0;
	        if ( empty($height) || ! $height ) $height = "1200";
	        $content = postLaunchHTML($parms, $endpoint, $debuglaunch,
	            "width=\"100%\" height=\"".$height."\" scrolling=\"auto\" frameborder=\"1\" transparency");
	    } else {
	        $content = postLaunchHTML($parms, $endpoint, $debuglaunch, false);
	        if ($launch == 2) { //popup 
	        	$content= str_replace('name="ltiLaunchForm" id="ltiLaunchForm"','name="ltiLaunchForm" id="ltiLaunchForm" target="_blank"',$content);
	        }
	    }
	    return $content;
	}
	
	
	function blti_consumer_split_custom_parameters($customstr) {
	    $lines = preg_split("/[\n;]/",$customstr);
	    $retval = array();
	    foreach ($lines as $line){
	        $pos = strpos($line,"=");
	        if ( $pos === false || $pos < 1 ) continue;
	        $key = trim(substr($line, 0, $pos));
	        $val = trim(substr($line, $pos+1));
	        $key = $this->blti_consumer_map_keyname($key);
	        $retval['custom_'.$key] = $val;
	    }
	    return $retval;
	}
	
	function blti_consumer_map_keyname($key) {
	    $newkey = "";
	    $key = strtolower(trim($key));
	    foreach (str_split($key) as $ch) {
	        if ( ($ch >= 'a' && $ch <= 'z') || ($ch >= '0' && $ch <= '9') ) {
	            $newkey .= $ch;
	        } else {
	            $newkey .= '_';
	        }
	    }
	    return $newkey;
	}

	/**
	*
	* Added to get in correct format
	* 20120821 abertranb
	* @param unknown_type $l
	* @return string
	*/
	function basiclti_get_locale($l) {
		switch ($l) {
			case 'en.utf8':
				$l = 'en-GB';
				break;
			case 'nl.utf8':
				$l = 'nl-NL';
				break;
			case 'sv.utf8':
				$l = 'sv-SE';
				break;
			case 'pl.utf8':
				$l = 'pl-PL';
				break;
			case 'ca.utf8':
				$l = 'ca-ES';
				break;
			case 'es.utf8':
				$l = 'es-ES';
				break;
			case 'fr.utf8':
				$l = 'fr-FR';
				break;
				//Belorussia';
			case 'be.utf8':
				$l = 'be_BYL';
				break;
			case 'bg.utf8':
				//Bulgarian';
				$l = 'bg-BG';
				break;
			case 'zh_cn.utf8':
				//Chinese';
				$l = 'zh-HK';
				break;
			case 'hr.utf8':
				//Croatian';
				$l = 'hr-HR';
				break;
			case 'cs.utf8':
				//Czech';
				$l = 'cs-CZ';
				break;
			case 'da.utf8':
				//Danish';
				$l = 'da-DK';
			case 'nl_be.utf8':
				//Dutch';Belgium';
				$l = 'nl-BE';
				break;
			case 'ie.utf8':
				//Ireland';
				$l = 'en-IE';
				break;
			case 'nz.utf8':
				//New Zealand';
				$l = 'en-NZ';
				break;
			case '<a.utf8':
				//South Africa';
				$l = 'en-ZA';
				break;
			case 'et.utf8':
				//Estonian';
				$l = 'et-EE';
				break;
			case 'fi.utf8':
				//Finnish';
				$l = 'fi-FI';
				break;
			case 'fr_be.utf8':
				//French';Belgium';
				$l = 'fr-BE';
				break;
			case 'fr_lu.utf8':
				//French';Luxembourg';
				$l = 'fr-LU';
				break;
			case 'fr_ch.utf8':
				//French';Switzerland';
				$l = 'fr-CH';
				break;
			case 'de_at.utf8':
				//German';Austria';
				$l = 'de-AT';
				break;
			case 'de_lu.utf8':
				//German';Luxembourg';
				$l = 'de-LU';
				break;
			case 'de_ch.utf8':
				//German';Switzerland';
				$l = 'de-CH';
				break;
			case 'el.utf8':
				//Greek';Greece';
				$l = 'el-GR';
				break;
			case 'hu.utf8':
				//Hungarian';
				$l = 'hu-HU';
				break;
			case 'is.utf8':
				//Iceland';
				$l = 'is-IS';
				break;
			case 'it_ch.utf8':
				//Italian';Switzerland';
				$l = 'it-CH';
				break;
			case 'lv.utf8':
				//Latvian';
				$l = 'lv-LV';
				break;
			case 'lt.utf8':
				//Lithuanian';
				$l = 'lt-LT';
				break;
			case 'mk.utf8':
				//Macedonian';
				$l = 'mk-MK';
				break;
			case 'no.utf8':
				//Norway';
				$l = 'no-NO';
				break;
			case 'pt.utf8':
				//Portugal';
				$l = 'pt-PT';
				break;
			case 'ro.utf8':
				//Romanian';
				$l = 'ro-RO';
				break;
			case 'ru.utf8':
				//Russian';
				$l = 'ru-RU';
				break;
			case 'sr.utf8':
				//Serbian (Cyrillic)';
				$l = 'sr-YU';
				break;
			case 'sk.utf8':
				//Slovak';
				$l = 'sk-SK';
				break;
			case 'sl.utf8':
				//Slovenian';
				$l = 'sl-SI';
				break;
			case 'tr.utf8':
				//Turkish';
				$l = 'tr-TR';
				break;
			case 'uk.utf8':
				//Ukrainian';
				$l = 'uk-UA';
				break;
			default:
				$l = 'en-GB';
		}
		return $l;
	
	}
	
}