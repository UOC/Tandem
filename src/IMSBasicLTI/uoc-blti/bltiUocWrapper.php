<?php
/**
 * 
 * Blti Wrapper for UOC. http://www.imsglobal.org/lti/blti/bltiv1p0/ltiBLTIimgv1p0.html.   
 *
 * Copyright (c) 2011 Universitat Oberta de Catalunya
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
 * Author: Antoni Bertran / UOC / abertranb@uoc.edu
 * Date: January 2011
 *
 * Project email: campusproject@uoc.edu
 *
 **/

require_once(dirname(__FILE__).'/../ims-blti/blti.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'utils'.DIRECTORY_SEPARATOR.'UtilsPropertiesBLTI.php');
require_once(dirname(__FILE__).'/lti_utils.php');


class bltiUocWrapper extends BLTI {
	
	/**
	* LTI version for messages.
	*/
	const LTI_VERSION = 'LTI-1p0';
	/**
	* Use ID value only.
	*/
	const ID_SCOPE_ID_ONLY = 0;
	/**
	 * Prefix an ID with the consumer key.
	 */
	const ID_SCOPE_GLOBAL = 1;
	/**
	 * Prefix the ID with the consumer key and context ID.
	 */
	const ID_SCOPE_CONTEXT = 2;
	/**
	 * Prefix the ID with the consumer key and resource ID.
	 */
	const ID_SCOPE_RESOURCE = 3;
	/**
	 * Character used to separate each element of an ID.
	 */
	const ID_SCOPE_SEPARATOR = ':';
	
	
	
	var $configuration = null;
	var $configuration_file = null;
	var $lti_context = null;
	private $lti_settings_names = array('ext_resource_link_content', 'ext_resource_link_content_signature',
	                                      'lis_result_sourcedid', 'lis_outcome_service_url',
	                                      'ext_ims_lis_basic_outcome_url', 'ext_ims_lis_resultvalue_sourcedids',
	                                      'ext_ims_lis_memberships_id', 'ext_ims_lis_memberships_url',
	                                      'ext_ims_lti_tool_setting', 'ext_ims_lti_tool_setting_id', 'ext_ims_lti_tool_setting_url');
	
	/** 
	 * Added base64 support
	 * @param $parm
	 * @param $usesession
	 * @param $doredirect
	 */
	 function __construct($usesession=true, $doredirect=true, $overwriteconfigurationfile=null, $secret=null) {

	 	$valid = false;
	 	if ($secret==null) { //Configuration file
		 	$default_conf_file = (dirname(__FILE__).'/../configuration/authorizedConsumersKey.cfg');
		 	if ($overwriteconfigurationfile!=null)
		 		$default_conf_file = $overwriteconfigurationfile;
		 	$this->configuration_file = $default_conf_file;
		 	$this->loadConfiguration();
		 	
		 	$valid = $this->isValidConsumerKey();
		 	if ($valid)
		    	$secret = 	$this->getSecret();
	 	}
	 	if ($secret!=null) {
	 		parent::__construct($secret, $usesession, $doredirect);
		 	$newinfo = $this->info;
		 	if (isset($newinfo['custom_lti_message_encoded_base64']) && $newinfo['custom_lti_message_encoded_base64']==1)
		 		$newinfo = $this->decodeBase64($newinfo);
		 	
		 	$this->info = $newinfo;
		 	
		 	//abertranb - 20120905 - Adding ext services
		 	$consumer_instance = new stdClass();
		 	$consumer_instance->guid	= $this->getConsumerKeyFromPost();
		 	$consumer_instance->secret = $secret;
		 	$consumer_instance->lti_context_id = $this->context_id;
		 	$consumer_instance->lti_resource_id = $this->info['resource_link_id']; 
		 	$consumer_instance->title = $this->info['context_title']; 
		 	$consumer_instance->primary_consumer_instance_guid = $this->info['tool_consumer_instance_guid'];
		 	$consumer_instance->primary_context_id = $this->info['tool_consumer_instance_guid'];
		 	
		 	$consumer_instance->settings = $this->loadSettings();
		 	
		 	$this->lti_context = new LTI_Context($consumer_instance);
		 	
	 	}
	 	return $this->valid;
	 }
	 
	 /**
	  * 
	  * Loads the settings obtained by LTI call 
	  */
	 private function loadSettings() {
	 	$settings = array();
	 	foreach ($this->lti_settings_names AS $key) {
	 		$v = $this->get_value_from_info($key);
	 		if ($v) {
	 			$settings[$key] = $v;
	 		}
	 	}
	 	return serialize($settings);
	 }
	 
	 /**
	  * 
	  * Gets the value of a key in LTI Object
	  * @param String $key
	  */
	 private function get_value_from_info($key) {
	 	$value = false;
	 	if (isset($this->info[$key])) {
	 		$value = $this->info[$key];
	 	}
	 	return $value;
	 }
	 
	 /**
	  * This function try to load configuration
	  */
	 function loadConfiguration() {
	 	
	 	if ($this->configuration == null) {
		 	//If everything ok then load configuration
			$this->configuration = new UtilsPropertiesBLTI($this->configuration_file);
	 	}
		return $this->configuration;
	 }
	 
	 /**
	  * Gets the consumer guid from post to load the configuration
	  * This is a unique identifier for the TC.  
	  * A common practice is to use the DNS of the organization or the DNS 
	  * of the TC instance.  
	  * If the organization has multiple TC instances, 
	  * then the best practice is to prefix the domain name with a locally 
	  * unique identifier for the TC instance.
	  */
	function getConsumerKeyFromPost() {
		//Never encoded base64
		$tool_consumer_instance_guid = $_POST['oauth_consumer_key'];
		return $tool_consumer_instance_guid;
	} 
	 
	/**
	 * This function uses the consumer key to get if exists and is enabled
	 */
	function isValidConsumerKey() {
		
		try {

			$key = 'consumer_key.'.$this->getConsumerKeyFromPost().'.enabled';
			return $this->configuration->getProperty($key)=='1';
			
		} catch (Exception $e) {
			return false;
		}
		
	}
	
	/**
	 * This function uses the consumer key to get the secret
	 */
	function getSecret() {
		
		try {

			$key = 'consumer_key.'.$this->getConsumerKeyFromPost().'.secret';
			return $this->configuration->getProperty($key);
			
		} catch (Exception $e) {
			return false;
		}
		
	}
	/**
	 * This function gets the User key format of UOC
	 * @see IMSBasicLTI/ims-blti/BLTI#getUserKey()
	 */
    function getUserKey2() {
       $lis_person_sourcedid = $this->info['lis_person_sourcedid'];
       $oauth = $this->info['oauth_consumer_key'];
       $pos = strpos($lis_person_sourcedid,$oauth.":");
       if ($pos !== false && strlen($lis_person_sourcedid)>0)
         return $lis_person_sourcedid;
         $id = $this->info['user_id'];
        if ( strlen($id) > 0 && strlen($oauth) > 0 ) return $oauth . ':' . $id;
        return false;
    }
 
 /**
	* get if user is authorized in this course */
    function isAuthorizedUserInCourse() {
    	$roles = $this->info['roles'];
    	$roles = strtolower($roles);
	    if ( ! ( strpos($roles,"instructor") === false ) ) return true;
    	if ( ! ( strpos($roles,"administrator") === false ) ) return true;
	    if ( ! ( strpos($roles,"learner") === false ) ) return true;
    	return false;
    }
 
    /**
     * Data submitter are in base64 then we have to decode
     * @author Antoni Bertran (abertranb@uoc.edu)
     * @param $info array
     */
    function decodeBase64($info) {
      $keysNoEncode = array("lti_version", "lti_message_type", "tool_consumer_instance_description", "tool_consumer_instance_guid", "oauth_consumer_key", "custom_lti_message_encoded_base64", "oauth_nonce", "oauth_version", "oauth_callback", "oauth_timestamp", "basiclti_submit", "oauth_signature_method");
      foreach ($info as $key => $item){
        if (!in_array($key, $keysNoEncode))
          $info[$key] = base64_decode($item);
      }
      return $info;
    }
    

    /**
     * to get firstName of user
     * @see BLTI::getUserShortName()
     */
    function getUserFirstName() {
        $givenname = $this->info['lis_person_name_given'];
        $familyname = $this->info['lis_person_name_family'];
        $fullname = $this->info['lis_person_name_full'];
        if ( strlen($givenname) > 0 ) return $givenname;
        if ( strlen($familyname) > 0 ) return $familyname;
        return $this->getUserName();
    }

    /**
     * to get firstName of user
     * @see BLTI::getUserShortName()
     */
    function getUserLastName() {
        $familyname = $this->info['lis_person_name_family'];
        $fullname = $this->info['lis_person_name_full'];
        if ( strlen($familyname) > 0 ) return $familyname;
        return $this->getUserName();
    }
    
    /***** FUNCTIONS ADDED TO MANAGE lti_utils.php (based on code LTI_Tool_Provider ****/
    /**
    *
    * Check if the Outcomes service is supported
    */
    public function hasOutcomesService() {
    
    	return $this->lti_context->hasOutcomesService();
    
    }
    
    /**
     *
     * Check if the Memberships service is supported
     */
    public function hasMembershipsService() {
    
    	return $this->lti_context->hasMembershipsService();
    
    }
    
    /**
     *
     * Check if the Setting service is supported
     */
    public function hasSettingService() {
    
    	return $this->lti_context->hasSettingService();
    
    }
    
    /**
     *
     * Perform an Outcomes service request
     * @param unknown_type $action
     * @param unknown_type $lti_outcome
     */
    public function doOutcomesService($action, $lti_outcome) {
    
    	return $this->lti_context->doOutcomesService($action, $lti_outcome);
    
    }
    
    /**
     *
     * Perform a Memberships service request
     * @param array $old_users
     */
    public function doMembershipsService($old_users) {
    	
    	return $this->lti_context->doMembershipsService($old_users);
    
    }
    
    /**
     *
     * Perform a Setting service request
     * @param unknown_type $action
     * @param unknown_type $value
     */
    public function doSettingService($action, $value = NULL) {
    
    	return $this->lti_context->doSettingService($action, $value);
    
    }
    
    /**
     * 
     * Returns the LTI_Context
     * @return NULL
     */
    public function getLTI_Context() {
    	return $this->lti_context;
    }
    
    
    /***** END FUNCTIONS ADDED TO MANAGE lti_utils.php (based on code LTI_Tool_Provider ****/
}
