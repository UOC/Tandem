<?php

require_once("constants.php");

class IntegrationTandemBLTI {
	const MAX_USER_TANDEM = 2;

    /**
     * @param $exercise
     * @param bool $die_if_not_found
     * @param string $relative_path
     * @param string $path
     * @return null|stdClass
     */
	public function getDataExercise($exercise, $die_if_not_found=true, $relative_path='', $path='') {
		if ($path=='') {
			//Now we try to get data course
			//$path = '';
			//20120830 abertranb register the course folder
			//MODIFIED - 20120927 - abertran to avoid error  A session had already been started - ignoring session_start()
			if(!isset($_SESSION)) {
				session_start();
			}
			// ORIGINAL
			// session_start();
			// END
			if (isset($_SESSION[TANDEM_COURSE_FOLDER])) {
				$path = $_SESSION[TANDEM_COURSE_FOLDER];
			}
			//FIIIII
		}

		$file = dirname(__FILE__)."/../".$path.$relative_path."/data$exercise.xml";
		$file = str_replace('//', '/', $file);
		if (!file_exists($file)) {
			if ($die_if_not_found)
				die ("Exercise $file not exists");
			else
				return null;
		}
	    $xml = simplexml_load_file($file);
	    if ($xml!==FALSE) {

			$obj = new stdClass();
			foreach ($xml->nextType as $nextType) {
				$node = $nextType['node'].'';
				if ($node=='1') {
			    	$obj->classOf = $nextType['classOf'].'';
			    	$obj->numBtns = $nextType['numBtns'].'';
			    	$obj->numUsers = $nextType['numUsers'].'';
			    	$obj->nextSample = $nextType['currSample'].'';
			    	$obj->node = $node;
			    	break;
				}
			}
	    }


	    return $obj;
	}

	/**
	 * Creates XML User
	 * @param $user_obj
	 * @param $room
	 */
	public function makeXMLUser($user_obj, $room, $exercise){

		//Mirem de crear el xml perque el llegegixi l'altre
		$custom_room = isset($user_obj->custom_room)?$user_obj->custom_room:false;
		//$room = $room.'_'.$custom_room;
		$this->createXML($room);
		$this->editXMLUser($user_obj,$room);

		if ($custom_room && !file_exists($custom_room.".xml")) {
			//Salvem les dades temporal per indicar l'aula
			$doc = $this->createXML($custom_room);
			$xml = simplexml_load_file(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$custom_room.".xml");
			$xml->usuarios[0]->addAttribute('exercise',$exercise);
	  		$xml->asXML(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$custom_room.".xml");
		}
	}

    /**
     * Creates XML User amb la versi� LTI
     * @param $user_obj
     * @param $room
     */
	public function makeXMLUserLTI($user_obj, $room, $exercise){

		$this->createXML($room);
		$this->editXMLUser($user_obj,$room);

		$xml = simplexml_load_file(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.'.xml');
		$xml->usuarios[0]->addAttribute('exercise',$exercise);
		$xml->asXML(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");
	}

    /**
     * Get the selected exercise selected for the firs user
     * @param $user_obj
     * @return bool|string
     */
	public function checkXML2GetExercise($user_obj){
		//Mirem de crear el xml perque el llegegixi l'altre
		$custom_room = isset($user_obj->custom_room)?$user_obj->custom_room:false;
		$filename = $custom_room.'.xml';
		if ($custom_room && file_exists($filename)) {
			//Salvem les dades temporal per indicar l'aula
			$xml = simplexml_load_file($filename);
			$user = $xml->usuarios[0];
			if (isset($user['exercise'])) {
				unlink($filename);
				return $user['exercise'].'';
			}
		}
		return false;
	}

    /**
     * Creates XML object
     * Enter description here ...
     * @param $room
     * @return DOMDocument
     */
	public function createXML($room) {
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$ini = $doc->createElement( "tandem" );
		$doc->appendChild( $ini );
		$u = $doc->createElement( "usuarios" );
		$roomN = $doc->createAttribute( "room" );
		$roomNumber = $doc->createTextNode($room);
		$roomN->appendChild($roomNumber);
		$u->appendChild($roomN);
		$doc->appendChild( $u );
		$ini->appendChild( $u );
		$doc->save(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");
		return $doc;
	}

	/**
	 * Adds user to XML
	 * @param stdClass $user_obj
	 * @param String $room
	 */
	public function editXMLUser($user_obj, $room){
		$xml = simplexml_load_file(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");
		if(count($xml->usuarios[0]->usuario)==0) {
			$this->putDataXML($xml, $user_obj);
		}
		else if(count($xml->usuarios[0]->usuario)==1 && $xml->usuarios[0]->usuario[0]!=$user_obj->type_user
			&& $xml->usuarios[0]->usuario[0]['email']!=$user_obj->email) {
			$this->putDataXML($xml, $user_obj);
		}
	  	$xml->asXML(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");
	}

    /**
     * Set user disconnected from External Tool
     * @param $user_id
     * @param String $room
     * @param string $username
     */
	public function endSessionExternalToolXMLUser($user_id, $room, $username=''){
		if (file_exists(PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml")) {
            $xml = simplexml_load_file(PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml");
            if ($username == '') {
                $username = ' ';
            }
            $externalToolClosed = $this->addDataXML($xml, 'externalToolClosed', $username);
            $xml->asXML(PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml");
        }
	}

    /**
     * Gets if user can login in Tandem
     * @param stdClass $user_obj
     * @param String $room
     * @return bool
     */
	public function canUserLoginInTandem($user_obj, $room){
		if (!file_exists($room.".xml")) {
			$room = PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room;
		}
		$xml = simplexml_load_file($room.".xml");
		$total_users = count($xml->usuarios[0]->usuario);
		$return = $total_users<IntegrationTandemBLTI::MAX_USER_TANDEM;

		if (!$return) {
			if ($total_users==IntegrationTandemBLTI::MAX_USER_TANDEM) {
				//Testing if one of the users is the current user
				for ($i=0; $i<$total_users; $i++) {
					if ($xml->usuarios[0]->usuario[$i]==$user_obj->type_user) {
						$return = true;
						break;
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Stores data to user XML
	 * @param $xml
	 * @param stdClass $user_obj
	 */
	public function putDataXML($xml, $user_obj) {
		//TODO fer multiusuari
//		$usuario = $this->addDataXML($xml, 'usuario',$user_obj->username
		$usuario = $this->addDataXML($xml, 'usuario',$user_obj->type_user);
		$this->addAttributeXML($usuario, 'name',$user_obj->name);
		$this->addAttributeXML($usuario, 'email',$user_obj->email);
		$this->addAttributeXML($usuario, 'icq',$user_obj->icq);
		$this->addAttributeXML($usuario, 'skype',$user_obj->skype);
		$this->addAttributeXML($usuario, 'msn',$user_obj->msn);
		$this->addAttributeXML($usuario, 'yahoo',$user_obj->yahoo);
		$this->addAttributeXML($usuario, 'image',$user_obj->image);
		$this->addAttributeXML($usuario, 'points',$user_obj->points);
	}

    /**
     * Adds data to XML user
     * @param $xml
     * @param String $name
     * @param String $value
     * @return mixed
     */
	public function addDataXML($xml, $name, $value) {
		return $xml->usuarios[0]->addChild($name,$value);
	}

    /**
     * Adds attribute to XML user
     * @param $xml
     * @param String $name
     * @param String $value
     * @return mixed
     */
	public function addAttributeXML($xml, $name, $value) {
		return $xml->addAttribute($name,$value);
	}

    /**
     * Gets the data of context
     * @param bltiUocWrapper $context
     * @param String $key
     * @return string
     */
	public function getDataInfo($context, $key) {
		$r = '';
		if (isset($context->info[$key]) && strlen($context->info[$key])>0) {
			$r = $context->info[$key];
		}
		return $r;
	}

    /**
     * @param bltiUocWrapper $context
     * @param string $contextKey
     * @param string $sessionKey
     * @param boolean $is_boolean
     * @return bool
     */
	public function getDataFromContextOrDefaultToSession($context, $contextKey, $sessionKey, $is_boolean=true) {
        if (isset($_POST[$contextKey])) {
            /** @noinspection TypeUnsafeComparisonInspection */
            $value = $this->getDataInfo($context, $contextKey);

            return $is_boolean?($value == 1):$value;
        }

        if (isset($_SESSION[$sessionKey])) {
            return $_SESSION[$sessionKey];
        }

        return false;
    }
}
