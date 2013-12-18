<?php
/**
 * lt_utils based on LTI_Tool_Provider - PHP class to include in an external tool to handle connections with a LTI 1 compliant tool consumer
 * Copyright (C) 2012  Stephen P Vickers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Contact: stephen@spvsoftwareproducts.com
 *
 * Version history:
 *   2.0.00  30-Jun-12  Initial release (replacing version 1.1.01 of BasicLTI_Tool_Provider)
 *   2.1.00   3-Jul-12  Added option to restrict use of consumer key based on tool consumer GUID value
 *                      Added field to record day of last access for each consumer key
 */

require_once(dirname(__FILE__).'/bltiUocWrapper.php');

class LTI_Context {

	const EXT_READ = 1;
	const EXT_WRITE = 2;
	const EXT_DELETE = 3;

	const EXT_TYPE_DECIMAL = 'decimal';
	const EXT_TYPE_PERCENTAGE = 'percentage';
	const EXT_TYPE_RATIO = 'ratio';
	const EXT_TYPE_LETTER_AF = 'letteraf';
	const EXT_TYPE_LETTER_AF_PLUS = 'letterafplus';
	const EXT_TYPE_PASS_FAIL = 'passfail';
	const EXT_TYPE_TEXT = 'freetext';

	const MIN_SHARE_KEY_LENGTH = 5;
	const MAX_SHARE_KEY_LENGTH = 32;

	public $consumer_instance = NULL;
	public $id = NULL;
	public $lti_context_id = NULL;
	public $lti_resource_id = NULL;
	public $title = NULL;
	public $ext_response = NULL;
	public $primary_consumer_instance_guid = NULL;
	public $primary_context_id = NULL;
	public $share_approved = NULL;
	public $created = NULL;
	public $updated = NULL;

	private $settings_changed = FALSE;
	private $settings = NULL;
	private $ext_doc = NULL;
	private $ext_nodes = NULL;
	private $token_session_lti = NULL;
	
	//
	//    Class constructor
	//
	public function __construct(&$consumer_instance) {

		$this->consumer_instance = $consumer_instance;
		$this->settings = array();
		$this->load($consumer_instance);

	}

	/**
	* Get tool consumer.
	*
	* @return object LTI_Tool_Consumer object for this context.
	*/
	public function getConsumer() {
	
		return $this->consumer;
	
	}
	
	/**
	* Get the tool consumer key.
	*
	* @return string Consumer key value
	*/
	public function getKey() {
	
		return $this->key;
	
	}
	
	
  /**
   * 
   * Get a setting value
   * @param unknown_type $name
   * @param unknown_type $default
   */
  	public function getSetting($name, $default = '') {

		if (array_key_exists($name, $this->settings)) {
			$value = $this->settings[$name];
		} else {
			$value = $default;
		}

		return $value;

	}

		/**
		 * 
		 * Set a setting value
		 * @param unknown_type $name
		 * @param unknown_type $value
		 */
		public function setSetting($name, $value = NULL) {

			$old_value = $this->getSetting($name);
			if ($value != $old_value) {
				if (!empty($value)) {
					$this->settings[$name] = $value;
				} else {
					unset($this->settings[$name]);
				}
				$this->settings_changed = TRUE;
			}

		}

		/**
		 * 
		 * Get an array of all setting values
		 */	
		public function getSettings() {

			return $this->settings;

		}
		/**
		 * 
		 * Check if the Outcomes service is supported
		 */
		public function hasOutcomesService() {

			$url = $this->getSetting('ext_ims_lis_basic_outcome_url') . $this->getSetting('lis_outcome_service_url');
	
			return !empty($url);

		}

		/**
		 * 
		 * Check if the Memberships service is supported
		 */			
	  	public function hasMembershipsService() {
	
		    $url = $this->getSetting('ext_ims_lis_memberships_url');

			return !empty($url);

		}

		/**
		 * 
		 * Check if the Setting service is supported
		 */
		public function hasSettingService() {

    		$url = $this->getSetting('ext_ims_lti_tool_setting_url');

			return !empty($url);

		}

		/**
		 * 
		 * Perform an Outcomes service request
		 * @param unknown_type $action
		 * @param unknown_type $lti_outcome
		 */
		public function doOutcomesService($action, $lti_outcome) {

			$response = FALSE;
			$this->ext_response = NULL;
			//
			/// Use LTI 1.1 service in preference to extension service if it is available
			$urlLTI11 = $this->getSetting('lis_outcome_service_url');
			$urlExt = $this->getSetting('ext_ims_lis_basic_outcome_url');
			if ($urlExt || $urlLTI11) {
      		switch ($action) {
				case self::EXT_READ:
					if ($urlLTI11) {
            			$do = 'readResult';
					} else {
						$do = 'basic-lis-readresult';
					}
				break;
				case self::EXT_WRITE:
					if ($urlLTI11 && $this->checkValueType($lti_outcome, array(self::EXT_TYPE_DECIMAL))) {
           	 			$do = 'replaceResult';
					} else if ($this->checkValueType($lti_outcome)) {
						$urlLTI11 = NULL;
						$do = 'basic-lis-updateresult';
					}
				break;
				case self::EXT_DELETE:
	          		if ($urlLTI11) {
						$do = 'deleteResult';
					} else {
						$do = 'basic-lis-deleteresult';
					}
				break;
      		 }
    		}
    		if (isset($do)) {
				if ($urlLTI11) {
					$xml = <<<EOF
					<resultRecord>
					<sourcedGUID>
					<sourcedId>{$lti_outcome->getSourcedid()}</sourcedId>
						</sourcedGUID>
        			<result>
						<resultScore>
							<language>{$lti_outcome->language}</language>
							<textString>{$lti_outcome->getValue()}</textString>
	          			</resultScore>
        			</result>
					</resultRecord>
EOF;
        		if ($this->doLTI11Service($do, $urlLTI11, $xml)) {
	          		switch ($action) {
    			        case self::EXT_READ:
              				if (isset($this->ext_nodes['imsx_POXBody']["{$do}Response"]['result']['resultScore']['textString'])) {
                				$response = $this->ext_nodes['imsx_POXBody']["{$do}Response"]['result']['resultScore']['textString'];
							}
						break;
						case self::EXT_WRITE:
						case self::EXT_DELETE:
							$response = TRUE;
						break;
          			}
        		   }
				} else {
					$params = array();
					$params['sourcedid'] = $lti_outcome->getSourcedid();
        			$params['result_resultscore_textstring'] = $lti_outcome->getValue();
					if (!empty($lti_outcome->language)) {
						$params['result_resultscore_language'] = $lti_outcome->language;
					}
					if (!empty($lti_outcome->status)) {
						$params['result_statusofresult'] = $lti_outcome->status;
					}
					if (!empty($lti_outcome->date)) {
						$params['result_date'] = $lti_outcome->date;
					}
					if (!empty($lti_outcome->type)) {
          				$params['result_resultvaluesourcedid'] = $lti_outcome->type;
					}
					if (!empty($lti_outcome->data_source)) {
          				$params['result_datasource'] = $lti_outcome->data_source;
					}
					if ($this->doService($do, $urlExt, $params)) {
						switch ($action) {
						case self::EXT_READ:
							if (isset($this->ext_nodes['result']['resultscore']['textstring'])) {
								$response = $this->ext_nodes['result']['resultscore']['textstring'];
							}
		              	break;
              			case self::EXT_WRITE:
						case self::EXT_DELETE:
							$response = TRUE;
						break;
						}
					}
				}
			}

			return $response;

		}

		/**
		 * 
		 * Perform a Memberships service request
		 * @param array $old_users
		 */
		//TODO mirar de passar la llista dels usuaris antics o millor que retorni els actuals
	  public function doMembershipsService($old_users) {

		$users = array();
    	//$old_users = $this->getUserResultSourcedIDs(TRUE, BasicLTI_Tool_Provider::ID_SCOPE_RESOURCE);
		$this->ext_response = NULL;
		$url = $this->getSetting('ext_ims_lis_memberships_url');
		$params = array();
		$params['id'] = $this->getSetting('ext_ims_lis_memberships_id');

		if ($this->doService('basic-lis-readmembershipsforcontext', $url, $params)) {
			if (!isset($this->ext_nodes['memberships']['member'])) {
				$members = array();
			} else if (!isset($this->ext_nodes['memberships']['member'][0])) {
        		$members = array();
				$members[0] = $this->ext_nodes['memberships']['member'];
      		} else {
				$members = $this->ext_nodes['memberships']['member'];
      		}

			for ($i = 0; $i < count($members); $i++) {

        		$user = new LTI_User($this, $members[$i]['user_id']);
				//
				/// Set the user name
				//
        		$firstname = (isset($members[$i]['person_name_given'])) ? $members[$i]['person_name_given'] : '';
        		$lastname = (isset($members[$i]['person_name_family'])) ? $members[$i]['person_name_family'] : '';
				$fullname = (isset($members[$i]['person_name_full'])) ? $members[$i]['person_name_full'] : '';
				$user->setNames($firstname, $lastname, $fullname);
				//
				//// Set the user email
				//
				$email = (isset($members[$i]['person_contact_email_primary'])) ? $members[$i]['person_contact_email_primary'] : '';
        		$user->setEmail($email);
				//
				//// Set the user roles
				//
        		if (isset($members[$i]['roles'])) {
					$user->roles = explode(',', $members[$i]['roles']);
				}
				//
				//// If a result sourcedid is provided save the user
				//
				if (isset($members[$i]['lis_result_sourcedid'])) {
					$user->lti_result_sourcedid = $members[$i]['lis_result_sourcedid'];
					//$user->save(); //TODO revisar per tal de salvar
				}
				$users[] = $user;
				//
				//// Remove old user (if it exists)
				//
				//unset($old_users[$user->getId(BasicLTI_Tool_Provider::ID_SCOPE_RESOURCE)]);
			}
			//
			//// Delete any old users which were not in the latest list from the tool consumer
			//
			/*foreach ($old_users as $id => $user) {
				$user->delete(); //TODO revisar
			}*/
		} else {
			$users = $old_users;
		}

		return $users;

		}

		/**
		 * 
		 * Perform a Setting service request
		 * @param unknown_type $action
		 * @param unknown_type $value
		 */				
		public function doSettingService($action, $value = NULL) {

			$response = FALSE;
			$this->ext_response = NULL;
			switch ($action) {
				case self::EXT_READ:
					$do = 'basic-lti-loadsetting';
					break;
				case self::EXT_WRITE:
					$do = 'basic-lti-savesetting';
					break;
				case self::EXT_DELETE:
					$do = 'basic-lti-deletesetting';
					break;
			}
			if (isset($do)) {
				$url = $this->getSetting('ext_ims_lti_tool_setting_url');
      			$params = array();
				$params['id'] = $this->getSetting('ext_ims_lti_tool_setting_id');
				if (is_null($value)) {
					$value = '';
				}
      			$params['setting'] = $value;

				if ($this->doService($do, $url, $params)) {
					switch ($action) {
          				case self::EXT_READ:
							if (isset($this->ext_nodes['setting']['value'])) {
              					$response = $this->ext_nodes['setting']['value'];
								if (is_array($response)) {
                					$response = '';
								}
							}
							break;
						case self::EXT_WRITE:
							$this->setSetting('ext_ims_lti_tool_setting', $value);
							$this->saveSettings();
            				$response = TRUE;
							break;
						case self::EXT_DELETE:
							$response = TRUE;
							break;
					}
				}

		}

		return $response;

	}


	/**
	 * 
	 *     Obtain an array of LTI_User objects for users with a result sourcedId.  The array may include users from other
	 *     contexts which are sharing this context.  It may also be optionally indexed by the user ID of a specified scope.
	 * @param unknown_type $context_only
	 * @param unknown_type $id_scope
	 */
	/*public function getUserResultSourcedIDs($context_only = FALSE, $id_scope = NULL) {

		$users = array();

		if ($context_only) {
			$sql = sprintf('SELECT u.consumer_instance_guid, u.context_id, u.user_id, u.lti_result_sourcedid ' .
						"FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME . ' AS u '  .
						"INNER JOIN {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' AS c '  .
                     'ON u.consumer_instance_guid = c.consumer_instance_guid AND u.context_id = c.context_id ' .
						"WHERE (c.consumer_instance_guid = %s AND c.context_id = %s AND c.primary_consumer_instance_guid IS NULL AND c.primary_context_id IS NULL)",
						BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
	    } else {
    	  $sql = sprintf('SELECT u.consumer_instance_guid, u.context_id, u.user_id, u.lti_result_sourcedid ' .
                     "FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::USER_TABLE_NAME . ' AS u '  .
						"INNER JOIN {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' AS c '  .
						'ON u.consumer_instance_guid = c.consumer_instance_guid AND u.context_id = c.context_id ' .
						"WHERE (c.consumer_instance_guid = %s AND c.context_id = %s AND c.primary_consumer_instance_guid IS NULL AND c.primary_context_id IS NULL) OR " .
                     "(c.primary_consumer_instance_guid = %s AND c.primary_context_id = %s AND share_approved = 1)",
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id),
         BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
    	}
    	$rs_user = mysql_query($sql);
    	if ($rs_user) {
			while ($row = mysql_fetch_object($rs_user)) {
        	$user = new LTI_User($this, $row->user_id);
        	$user->consumer_instance_guid = $row->consumer_instance_guid;
			$user->context_id = $row->context_id;
			$user->lti_result_sourcedid = $row->lti_result_sourcedid;
			if (is_null($id_scope)) {
						$users[] = $user;
			} else {
						$users[$user->getId($id_scope)] = $user;
			}
			}
		}

		return $users;

	}*/

	/**
	 * 
	 * Generate a new share key
	 * @param unknown_type $life
	 * @param unknown_type $auto_approve
	 * @param unknown_type $length
	 * #to delete
	 */
	/*public function getNewShareKey($life, $auto_approve = FALSE, $length = NULL) {

	$expires = time() + ($life * 60 * 60);
	if ($auto_approve) {
		$approve = 1;
	} else {
		$approve = 0;
	}
	if (empty($length) || !is_numeric($length)) {
		$length = self::MAX_SHARE_KEY_LENGTH;
	} else {
		$length = max(min($length, self::MAX_SHARE_KEY_LENGTH), self::MIN_SHARE_KEY_LENGTH);
	}
		$key = LTI_Tool_Consumer_Instance::getRandomString($length);
	
	$sql = sprintf("INSERT INTO {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_SHARE_KEY_TABLE_NAME .
	' (share_key, primary_consumer_instance_guid, primary_context_id, auto_approve, expires) ' .
	"VALUES (%s, %s, %s, {$approve}, {$expires})",
		BasicLTI_Tool_Provider::quoted($key), BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
	$ok = mysql_query($sql);
	if (!$ok) {
		$key = '';
	}
	
		return $key;
	
	}*/

	/**
	 * 
	 * Get an array of LTI_Context_Share objects for each context which is sharing this context
	public function getShares() {
	
		$shares = array();
	
	    $sql = sprintf('SELECT consumer_instance_guid, context_id, title, share_approved ' .
	"FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
	" WHERE primary_consumer_instance_guid = %s AND primary_context_id = %s" .
	' ORDER BY consumer_instance_guid',
	BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
		$rs_share = mysql_query($sql);
		if ($rs_share) {
			while ($row = mysql_fetch_object($rs_share)) {
			$share = new LTI_Context_Share();
				$share->consumer_instance_guid = $row->consumer_instance_guid;
			        $share->context_id = $row->context_id;
				$share->title = $row->title;
				$share->approved = $row->share_approved;
				$shares[] = $share;
			}
		}

	return $shares;

	}

	/**
	 * 
	 * Set the approval status of a share
	 * @param unknown_type $consumer_instance_guid
	 * @param unknown_type $context_id
	 * @param unknown_type $approve
	 * @return resource
	 
  public function doApproveShare($consumer_instance_guid, $context_id, $approve) {

    if ($approve) {
		$approved = 1;
	} else {
		$approved = 0;
	}
	$sql = sprintf("UPDATE {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
	" SET share_approved = {$approved} " .
	"WHERE consumer_instance_guid = %s AND context_id = %s",
	BasicLTI_Tool_Provider::quoted($consumer_instance_guid), BasicLTI_Tool_Provider::quoted($context_id));

	$ok = mysql_query($sql);

	return $ok;

	}

	/**
	 * 
	 * Cancel a context share
	 * @param unknown_type $consumer_instance_guid
	 * @param unknown_type $context_id
	 
	public function doCancelShare($consumer_instance_guid, $context_id) {

		$sql = sprintf("UPDATE {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME .
		" SET primary_consumer_instance_guid = NULL, primary_context_id = NULL, share_approved = NULL " .
		"WHERE consumer_instance_guid = %s AND context_id = %s",
		BasicLTI_Tool_Provider::quoted($consumer_instance_guid), BasicLTI_Tool_Provider::quoted($context_id));
	
	    $ok = mysql_query($sql);
	
		return $ok;

	}

//
//  PRIVATE METHODS
//
	/**
	 * 
	 * get the LTI values from session
	 * @param unknown_type $parameter
	 * @author abertranb
	 */
	/*private function setLTIValueFromSession($parameter) {
		if ($this->token_session_lti==NULL) {
			$this->token_session_lti = isset($_SESSION['lti_context_id'])?$_SESSION['lti_context_id']:'';
		}
		$this->$parameter = $_SESSION[$this->token_session_lti.$parameter];
	}*/
	
	/**
	*
	* Load the context from session
	* @param stdClass $consumer_instance
	* @author abertranb
	*/
	private function load($consumer_instance) {
	
		//session_start();
		$this->lti_context_id 					= $consumer_instance->lti_context_id;
		$this->lti_resource_id 					= $consumer_instance->lti_resource_id;
		$this->title 							= $consumer_instance->title;
		$this->settings		 					= $consumer_instance->settings;
		$this->settings 						= unserialize($this->settings);
		if (!is_array($this->settings)) {
			$this->settings = array();
		}
		$this->primary_consumer_instance_guid	= $consumer_instance->primary_consumer_instance_guid;
		$this->primary_context_id 				= $consumer_instance->primary_context_id;
		//TODO revisar abertranb 20120904
		//$this->setLTIValueFromSession('share_approved');
		//$this->share_approved = (is_null($row->share_approved)) ? NULL : ($row->share_approved == 1);

	}
	
	/**
	 * 
	 * Load the context from the database
	private function load_old() {

		$this->lti_context_id = NULL;
		$this->lti_resource_id = NULL;
		$this->title = '';
		$this->settings = array();
		$this->primary_consumer_instance_guid = NULL;
		$this->primary_context_id = NULL;
		$this->share_approved = NULL;
		$this->created = NULL;
		$this->updated = NULL;

		$sql = sprintf('SELECT c.* ' .
			"FROM {$this->consumer_instance->dbTableNamePrefix}" . BasicLTI_Tool_Provider::CONTEXT_TABLE_NAME . ' AS c ' .
                   "WHERE consumer_instance_guid = %s AND context_id = %s",
       	BasicLTI_Tool_Provider::quoted($this->consumer_instance->guid), BasicLTI_Tool_Provider::quoted($this->id));
		$rs_context = mysql_query($sql);
		if ($rs_context) {
			$row = mysql_fetch_object($rs_context);
		if ($row) {
			$this->lti_context_id = $row->lti_context_id;
			$this->lti_resource_id = $row->lti_resource_id;
			$this->title = $row->title;
			$this->settings = unserialize($row->settings);
			if (!is_array($this->settings)) {
				$this->settings = array();
			}
			if (!is_null($row->primary_consumer_instance_guid)) {
				$this->primary_consumer_instance_guid = $row->primary_consumer_instance_guid;
			}
			if (!is_null($row->primary_context_id)) {
				$this->primary_context_id = $row->primary_context_id;
			}
			$this->share_approved = (is_null($row->share_approved)) ? NULL : ($row->share_approved == 1);
        	$this->created = strtotime($row->created);
			$this->updated = strtotime($row->updated);
		}
	 }
  }
  */

 /**
  * 
  *  Convert data type of value to a supported type if possible
  * @param unknown_type $lti_outcome
  * @param unknown_type $supported_types
  */
   public function checkValueType(&$lti_outcome, $supported_types = NULL) {
	
	if (empty($supported_types)) {
		$supported_types = explode(',', str_replace(' ', '', strtolower($this->getSetting('ext_ims_lis_resultvalue_sourcedids', self::EXT_TYPE_DECIMAL))));
	}
	$type = $lti_outcome->type;
	$value = $lti_outcome->getValue();
	// Check whether the type is supported or there is no value
	$ok = in_array($type, $supported_types) || (strlen($value) <= 0);
	if (!$ok) {
			// Convert numeric values to decimal
			if ($type == self::EXT_TYPE_PERCENTAGE) {
				if (substr($value, -1) == '%') {
					$value = substr($value, 0, -1);
				}
				$ok = is_numeric($value) && ($value >= 0) && ($value <= 100);
				if ($ok) {
					$lti_outcome->setValue($value / 100);
					$lti_outcome->type = self::EXT_TYPE_DECIMAL;
				}
			} else if ($type == self::EXT_TYPE_RATIO) {
				$parts = explode('/', $value, 2);
				$ok = (count($parts) == 2) && is_numeric($parts[0]) && is_numeric($parts[1]) && ($parts[0] >= 0) && ($parts[1] > 0);
				if ($ok) {
				$lti_outcome->setValue($parts[0] / $parts[1]);
				$lti_outcome->type = self::EXT_TYPE_DECIMAL;
				}
				// Convert letter_af to letter_af_plus or text
			} else if ($type == self::EXT_TYPE_LETTER_AF) {
		        if (in_array(self::EXT_TYPE_LETTER_AF_PLUS, $supported_types)) {
					$ok = TRUE;
					$lti_outcome->type = self::EXT_TYPE_LETTER_AF_PLUS;
				} else if (in_array(self::EXT_TYPE_TEXT, $supported_types)) {
					$ok = TRUE;
					$lti_outcome->type = self::EXT_TYPE_TEXT;
				}
			// Convert letter_af_plus to letter_af or text
			} else if ($type == self::EXT_TYPE_LETTER_AF_PLUS) {
				if (in_array(self::EXT_TYPE_LETTER_AF, $supported_types) && (strlen($value) == 1)) {
				$ok = TRUE;
				$lti_outcome->type = self::EXT_TYPE_LETTER_AF;
			} else if (in_array(self::EXT_TYPE_TEXT, $supported_types)) {
				$ok = TRUE;
				$lti_outcome->type = self::EXT_TYPE_TEXT;
				}
				// Convert text to decimal
			} else if ($type == self::EXT_TYPE_TEXT) {
		        $ok = is_numeric($value) && ($value >= 0) && ($value <=1);
			if ($ok) {
				$lti_outcome->type = self::EXT_TYPE_DECIMAL;
			} else if (substr($value, -1) == '%') {
				$value = substr($value, 0, -1);
				$ok = is_numeric($value) && ($value >= 0) && ($value <=100);
				if ($ok) {
					if (in_array(self::EXT_TYPE_PERCENTAGE, $supported_types)) {
						$lti_outcome->type = self::EXT_TYPE_PERCENTAGE;
					} else {
						$lti_outcome->setValue($value / 100);
						$lti_outcome->type = self::EXT_TYPE_DECIMAL;
					}
				}
			}
		}
	 }
	
	 return $ok;

	}

		/**
		 * 
		 * Send a service request to the tool consumer
		 * @param unknown_type $type
		 * @param unknown_type $url
		 * @param unknown_type $params
		 */
		private function doService($type, $url, $params) {
	
			$this->ext_response = NULL;
			if (!empty($url)) {
				// Check for query parameters which need to be included in the signature
				$query_params = array();
				$query_string = parse_url($url, PHP_URL_QUERY);
				if (!is_null($query_string)) {
					$query_items = explode('&', $query_string);
					foreach ($query_items as $item) {
						if (strpos($item, '=') !== FALSE) {
						list($name, $value) = explode('=', $item);
							$query_params[$name] = $value;
						} else {
							$query_params[$name] = '';
						}
					}
				}
				$params = $params + $query_params;
				// Add standard parameters
				$params['oauth_consumer_key'] = $this->consumer_instance->guid;
				$params['lti_version'] = bltiUocWrapper::LTI_VERSION;
				$params['lti_message_type'] = $type;
				// Add OAuth signature
				$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
				$consumer = new OAuthConsumer($this->consumer_instance->guid, $this->consumer_instance->secret, NULL);
				$req = OAuthRequest::from_consumer_and_token($consumer, NULL, 'POST', $url, $params);
				$req->sign_request($hmac_method, $consumer, NULL);
				$params = $req->get_parameters();
				// Remove parameters being passed on the query string
				foreach (array_keys($query_params) as $name) {
					unset($params[$name]);
				}
				// Connect to tool consumer
				$this->ext_response = $this->do_post_request($url, $params);
					// Parse XML response
				$this->ext_doc = new DOMDocument();
				$this->ext_doc->loadXML($this->ext_response);
				$this->ext_nodes = $this->domnode_to_array($this->ext_doc->documentElement);
				if (!isset($this->ext_nodes['statusinfo']['codemajor']) || ($this->ext_nodes['statusinfo']['codemajor'] != 'Success')) {
					$this->ext_response = NULL;
				}
			}

			return !is_null($this->ext_response);

		}

		/**
		 * 
		 * Send a service request to the tool consumer
		 * @param unknown_type $type
		 * @param unknown_type $url
		 * @param unknown_type $xml
		 */
		private function doLTI11Service($type, $url, $xml) {

			$this->ext_response = NULL;
			if (!empty($url)) {
				$id = uniqid();
				$xmlRequest = <<<EOF
					<?xml version = "1.0" encoding = "UTF-8"?>
						<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/lis/oms1p0/pox">
							<imsx_POXHeader>
								<imsx_POXRequestHeaderInfo>
		      						<imsx_version>V1.0</imsx_version>
									<imsx_messageIdentifier>{$id}</imsx_messageIdentifier>
								</imsx_POXRequestHeaderInfo>
							</imsx_POXHeader>
							<imsx_POXBody>
								<{$type}Request>
								{$xml}
								</{$type}Request>
							</imsx_POXBody>
						</imsx_POXEnvelopeRequest>
EOF;
					// Calculate body hash
				$hash = base64_encode(sha1($xmlRequest, TRUE));
				$params = array('oauth_body_hash' => $hash);

				// Add OAuth signature
				$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
				$consumer = new OAuthConsumer($this->consumer_instance->guid, $this->consumer_instance->secret, NULL);
				$req = OAuthRequest::from_consumer_and_token($consumer, NULL, 'POST', $url, $params);
				$req->sign_request($hmac_method, $consumer, NULL);
				$params = $req->get_parameters();
				$header = $req->to_header();
				$header = $header . "\nContent-Type: application/xml";

				// Connect to tool consumer
      			$this->ext_response = $this->do_post_request($url, $xmlRequest, $header);
				// Parse XML response
				$this->ext_doc = new DOMDocument();
				$this->ext_doc->loadXML($this->ext_response);
				$this->ext_nodes = $this->domnode_to_array($this->ext_doc->documentElement);
				if (!isset($this->ext_nodes['imsx_POXHeader']['imsx_POXResponseHeaderInfo']['imsx_statusInfo']['imsx_codeMajor']) ||
          			($this->ext_nodes['imsx_POXHeader']['imsx_POXResponseHeaderInfo']['imsx_statusInfo']['imsx_codeMajor'] != 'success')) {
        				$this->ext_response = NULL;
      				}
    		}

    	return !is_null($this->ext_response);

  	}

	/**
	 * 
	 * Get the response from an HTTP POST request
	 * @param unknown_type $url
	 * @param unknown_type $params
	 * @param unknown_type $header
	 */
    private function do_post_request($url, $params, $header = NULL) {

		$response = '';
		if (is_array($params)) {
			$data = http_build_query($params);
		} else {
			$data = $params;
		}
		$opts = array('method' => 'POST',
			'content' => $data
			);
		if (!empty($header)) {
			$opts['header'] = $header;
		}
		$ctx = stream_context_create(array('http' => $opts));
		$fp = @fopen($url, 'rb', false, $ctx);
		if ($fp) {
			$resp = @stream_get_contents($fp);
			if ($resp !== FALSE) {
				$response = $resp;
			}
		}

		return $response;

	}

	/**
	 * 
	 * Convert DOM nodes to array
	 * @param unknown_type $node
	 * @return Ambigous <string, multitype:multitype: multitype:string  unknown >
	 */
  	private function domnode_to_array($node) {

		$output = array();
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			case XML_ELEMENT_NODE:
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = $this->domnode_to_array($child);
							
			        if (isset($child->tagName)) {
			        	$t = $child->tagName;
						if (!isset($output[$t])) {
			            	$output[$t] = array();
			            }
			            $output[$t][] = $v;
					} else if($v) {
						$output = (string) $v;
					}
				}
				if (is_array($output)) {
					if ($node->attributes->length) {
					$a = array();
					foreach ($node->attributes as $attrName => $attrNode) {
						$a[$attrName] = (string) $attrNode->value;
					}
					$output['@attributes'] = $a;
					}
							
					foreach ($output as $t => $v) {
						if (is_array($v) && count($v)==1 && $t!='@attributes') {
							$output[$t] = $v[0];
						}
					}
				}
	        	break;
		}

		return $output;

	}

 }


	//
	///  Class to represent an outcome
	//
	class LTI_Outcome {
	
		public $language = NULL;
		public $status = NULL;
		public $date = NULL;
		public $type = NULL;
		public $data_source = NULL;
	
		private $sourcedid = NULL;
		private $value = NULL;
	
		public function __construct($sourcedid, $value = NULL) {
	
			$this->sourcedid = $sourcedid;
			$this->value = $value;
			$this->language = 'en-US';
			$this->date = gmdate('Y-m-d\TH:i:s\Z', time());
			$this->type = 'decimal';
	
		}
	
		public function getSourcedid() {
	
			return $this->sourcedid;
	
		}
	
		public function getValue() {
	
			return $this->value;
	
		}

		public function setValue($value) {

			$this->value = $value;

		}

	}


	//
	///  Class to represent a context share
	//
	class LTI_Context_Share {

		public $consumer_instance_guid = NULL;
		public $context_id = NULL;
		public $title = NULL;
		public $approved = NULL;
		
		public function __construct() {

		}

	}
	
	/**
	* Class to represent a tool consumer user
	*
	* @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
	* @version 2.0.0
	* @license http://creativecommons.org/licenses/GPL/2.0/ GNU Public License
	*/
	class LTI_User {
	
		/**
		 * User's first name.
		 */
		public $firstname = '';
		/**
		 * User's last name (surname or family name).
		 */
		public $lastname = '';
		/**
		 * User's fullname.
		 */
		public $fullname = '';
		/**
		 * User's email address.
		 */
		public $email = '';
		/**
		 * Array of roles for user.
		 */
		public $roles = array();
		/**
		 * User's result sourcedid.
		 */
		public $lti_result_sourcedid = NULL;
		/**
		 * Date/time the record was created.
		 */
		public $created = NULL;
		/**
		 * Date/time the record was last updated.
		 */
		public $updated = NULL;
	
		/**
		 * LTI_Context object.
		 */
		private $context = NULL;
		/**
		 * User ID value.
		 */
		private $id = NULL;
	
		/**
		 * Class constructor.
		 *
		 * @param LTI_Context $context Context object
		 * @param string      $id      User ID value
		 */
		public function __construct($context, $id) {
	
			$this->initialise();
			$this->context = $context;
			$this->id = $id;
			$this->load();
	
		}
	
		/**
		 * Initialise the user.
		 */
		public function initialise() {
	
			$this->firstname = '';
			$this->lastname = '';
			$this->fullname = '';
			$this->email = '';
			$this->roles = array();
			$this->lti_result_sourcedid = NULL;
			$this->created = NULL;
			$this->updated = NULL;
	
		}
	
		/**
		 * Load the user from the database.
		 *
		 * @return boolean True if the user object was successfully loaded
		 */
		public function load() {
	
			$this->initialise();
			//TODO Manage users
			//$this->context->getConsumer()->getDataConnector()->User_load($this);
	
		}
	
		/**
		 * Save the user to the database.
		 *
		 * @return boolean True if the user object was successfully saved
		 */
		public function save() {
	
			if (condition) {
				//TODO Manage users
			//	$ok = $this->context->getConsumer()->getDataConnector()->User_save($this);
			} else {
				$ok = TRUE;
			}
	
			return $ok;
	
		}
	
		/**
		 * Delete the user from the database.
		 *
		 * @return boolean True if the user object was successfully deleted
		 */
		public function delete() {
	
			//TODO Manage users
			//return $this->context->getConsumer()->getDataConnector()->User_delete($this);
			return false;
	
		}
	
		/**
		 * Get context.
		 *
		 * @return LTI_Context Context object
		 */
		public function getContext() {
	
			return $this->context;
	
		}
	
		/**
		 * Get the user ID (which may be a compound of the tool consumer and context IDs).
		 *
		 * @param int $id_scope Scope to use for user ID (optional, default is null for consumer default setting)
		 *
		 * @return string User ID value
		 */
		public function getId($id_scope = NULL) {
	
			if (empty($id_scope)) {
				$id_scope = $this->context->getConsumer()->id_scope;
			}
			switch ($id_scope) {
				case bltiUocWrapper::ID_SCOPE_GLOBAL:
					$id = $this->context->getKey() . bltiUocWrapper::ID_SCOPE_SEPARATOR . $this->id;
					break;
				case bltiUocWrapper::ID_SCOPE_CONTEXT:
					$id = $this->context->getKey();
					if ($this->context->lti_context_id) {
						$id .= bltiUocWrapper::ID_SCOPE_SEPARATOR . $this->context->lti_context_id;
					}
					$id .= bltiUocWrapper::ID_SCOPE_SEPARATOR . $this->id;
					break;
				case bltiUocWrapper::ID_SCOPE_RESOURCE:
					$id = $this->context->getKey();
					if ($this->context->lti_resource_id) {
						$id .= bltiUocWrapper::ID_SCOPE_SEPARATOR . $this->context->lti_resource_id;
					}
					$id .= bltiUocWrapper::ID_SCOPE_SEPARATOR . $this->id;
					break;
				default:
					$id = $this->id;
				break;
			}
	
			return $id;
	
		}
	
		/**
		 * Set the user's name.
		 *
		 * @param string $firstname User's first name.
		 * @param string $lastname User's last name.
		 * @param string $fullname User's full name.
		 */
		public function setNames($firstname, $lastname, $fullname) {
	
			$names = array(0 => '', 1 => '');
			if (!empty($fullname)) {
				$this->fullname = trim($fullname);
				$names = preg_split("/[\s]+/", $this->fullname, 2);
			}
			if (!empty($firstname)) {
				$this->firstname = trim($firstname);
				$names[0] = $this->firstname;
			} else if (!empty($names[0])) {
				$this->firstname = $names[0];
			} else {
				$this->firstname = 'User';
			}
			if (!empty($lastname)) {
				$this->lastname = trim($lastname);
				$names[1] = $this->lastname;
			} else if (!empty($names[1])) {
				$this->lastname = $names[1];
			} else {
				$this->lastname = $this->id;
			}
			if (empty($this->fullname)) {
				$this->fullname = "{$this->firstname} {$this->lastname}";
			}
	
		}
	
		/**
		 * Set the user's email address.
		 *
		 * @param string $email        Email address value
		 * @param string $defaultEmail Value to use if no email is provided (optional, default is none)
		 */
		public function setEmail($email, $defaultEmail = NULL) {
	
			if (!empty($email)) {
				$this->email = $email;
			} else if (!empty($defaultEmail)) {
				$this->email = $defaultEmail;
				if (substr($this->email, 0, 1) == '@') {
					$this->email = $this->getId() . $this->email;
				}
			} else {
				$this->email = '';
			}
	
		}
	
		/**
		 * Check if the user is an administrator.
		 *
		 * @return boolean True if the user has a role of administrator
		 */
		public function isAdmin() {
	
			return $this->hasRole('admin');
	
		}
	
		/**
		 * Check if the user is staff.
		 *
		 * @return boolean True if the user has a role of instructor, contentdeveloper or teachingassistant
		 */
		public function isStaff() {
	
			return ($this->hasRole('instructor') || $this->hasRole('contentdeveloper') || $this->hasRole('teachingassistant'));
	
		}
	
		/**
		 * Check if the user is a learner.
		 *
		 * @return boolean True if the user has a role of learner
		 */
		public function isLearner() {
	
			return $this->hasRole('learner');
	
		}
	
		###
		###  PRIVATE METHODS
		###
	
		/**
		* Check whether the user has a specified role name.
		*
		* @param string $role Name of role
		*
		* @return boolean True if the user has the specified role
		*/
		private function hasRole($role) {
	
			$roles = strtolower(implode(',', $this->roles));
	
			return (strpos($roles, $role) !== FALSE);
	
		}
	
	}

