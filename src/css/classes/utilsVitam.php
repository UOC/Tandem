<?php

// Include the SDK
require_once 'constants.php';
require_once(dirname(__FILE__) . '/../config.inc.php');


class utilsVitam {

/**
 * Returns the total amount of rooms in vitam trhough their webservice
 */
  function getNumberOfRooms(){
  	//return file_get_contents("https://vitam.udg.edu/webrtc/licode/rest/getNumRooms/uoc");

    $curlInit = curl_init("https://vitam.udg.edu/webrtc/licode/rest/getNumRooms/uoc");
    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,5); 
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($curlInit);
    curl_close($curlInit);
        
    if( isset($response) )
         return intval($response);
    else
         return VITAM_MAX_ROOM;
  }
  /**
   *	Gets the user agent from the tandem table 
   */
  function hasChrome($tandemObj){    

  if ( (strpos($tandemObj['user_agent_host'], 'Chrome') !== false) && (strpos($tandemObj['user_agent_guest'], 'Chrome') !== false)  )
	{
		return true;
	}
		return false;
  }

}//end of class
