<?php
require_once('constants.php');
/**
 * 
 * Gets if file exists in include path or directely
 * @param unknown_type $file
 */
function filexists_tandem($file)
{
  $ps = explode(":", ini_get('include_path'));
  if ($ps) {
  	foreach($ps as $path)
  	{
    	if(file_exists($path.'/'.$file)) return true;
  	}
  }
  if(file_exists($file)) return true;
  return false;
}
/**
*
* Obte el llistat d'usuaris
* @param unknown_type $gestorBD
* @param unknown_type $course_id
* @param unknown_type $context
*/
function posa_osid_context_session($gestorBD, $course_id, $context) {
	//Loads users
	$osidContext = false;
	try {
		$_SESSION[HTTPATTRIBUTEKEY_OSIDCONTEXT]=null;
		$required_class = 'org/campusproject/utils/OsidContextWrapper.php';
		$exists = filexists_tandem ( $required_class );
		if (!$exists) {
			return true;
		}
		require_once $required_class;
		$oauth_consumer_key = $context->info[OATUH_CONSUMER_KEY];
		$utilsProperties = new UtilsPropertiesBLTI(dirname(__FILE__).'/../configuration_oki.cfg');

		$okibusPHP_components = $utilsProperties->getProperty(PROVIDER_PREFIX_OKI_COMPONENTS.$oauth_consumer_key, false);

		$okibusPHP_okibusClient = $utilsProperties->getProperty(PROVIDER_PREFIX_OKI.$oauth_consumer_key, false);
		if (!$okibusPHP_components || !$okibusPHP_okibusClient) {
			//TODO posar configuracio a OKI IOC
			//show_error('review_configuration_oki');
			$osidContext = true;
		}
		else {
			putenv('okibusPHP_components='.$okibusPHP_components);
			$_SESSION[OKIBUSPHP_COMPONENTS] = $okibusPHP_components;
			$_SESSION[OKIBUSPHP_OKIBUSCLIENT] = $okibusPHP_okibusClient;
			$okibusPHP_okibusClient_num_fields = intval($utilsProperties->getProperty(PROVIDER_PREFIX_OKI.$oauth_consumer_key.'_num_fields', 0));
			$osidContext = new OsidContextWrapper();
			for ($i=1; $i<=$okibusPHP_okibusClient_num_fields; $i++) {
				$okibusPHP_okibusClient_field_lti = $utilsProperties->getProperty(PROVIDER_PREFIX_OKI.$oauth_consumer_key.'_field_lti_'.$i, '');
				$okibusPHP_okibusClient_field_oki = $utilsProperties->getProperty(PROVIDER_PREFIX_OKI.$oauth_consumer_key.'_field_oki_'.$i, '');
				$okibusPHP_okibusClient_field_prefix_oki = $utilsProperties->getProperty(PROVIDER_PREFIX_OKI.$oauth_consumer_key.'_field_prefix_oki_'.$i, '');
				if (isset($context->info[$okibusPHP_okibusClient_field_lti]))
				$osidContext->assignContext($okibusPHP_okibusClient_field_oki, $okibusPHP_okibusClient_field_prefix_oki.$context->info[$okibusPHP_okibusClient_field_lti]);
			}
			//Aquesta es de uoc i necessaria
			$osidContext->assignContext(AUTHORIZATION_KEY_FIELD_OKI, $utilsProperties->getProperty(PROVIDER_PREFIX_OKI.$oauth_consumer_key.'_'.AUTHORIZATION_KEY_FIELD_OKI));
			$_SESSION[HTTPATTRIBUTEKEY_OSIDCONTEXT]=serialize($osidContext);
		}
	} catch (Exception $e) {
		show_error($e->getMessage());

	}
	return $osidContext;
}

function lti_get_lang($context) {
	if (isset($context->info[LAUNCH_PRESENTATION_LOCALE])) {
		$lang = $context->info[LAUNCH_PRESENTATION_LOCALE];
	}
	$lang = str_replace('-', '_', $lang);
	if (isset($context->info[CUSTOM_LANG])) {
		$custom_lang_id = '';
		$custom_lang_id = $context->info[CUSTOM_LANG];
		switch ($custom_lang_id)
		{
			case "a":
				$lang="ca_ES";
				break;
			case "b":
				$lang="es_ES";
				break;
			case "d":
				$lang="fr_FR";
				break;
			default:
				$lang="en_US";
		}
	}
	if (strlen($lang)<4){
		switch ($lang)
		{
			case "en":
				$lang="en_US";
				break;
			case "es":
				$lang="es_ES";
				break;
			case "ca":
				$lang="ca_ES";
				break;
			case "fr":
				$lang="fr_FR";
				break;
			case "nl":
				$lang="nl_NL";
				break;
			case "de":
				$lang="de_DE";
				break;
			default:
				$lang="en_US";
		}
	}
	if ($lang=='en_GB') {
		$lang = 'en_US';
	}

    if (strlen($lang)==5) {
      //To Moodle 2 because send all as lowercase
      $lang = substr($lang, 0, 3).strtoupper(substr($lang, 3, 2));
    }

	return $lang;
}

function getTandemIdentifier($id_tandem, $id_resource) {
	return $id_resource.'_'.$id_tandem;
}

function lti_get_username($context) {
	$username = $context->getUserKey();
	if (isset($context->info[USERNAME])) {
		$username = $context->info[USERNAME];
	}
	$username = sanitise_string($username);
	return $username;
}

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
/**
 *
 * Shows the message
 * @param unknown_type $msg
 */
function show_error($msg, $die=false) {
	if ($die) {
		echo('<html>
<title>Tandem Error</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="stylesheet" type="text/css" media="all" href="css/tandem.css" />
<link rel="stylesheet" type="text/css" media="all" href="css/jquery-ui.css" />
<!-- 10082012: nfinney> ADDED COLORBOX CSS LINK -->
<link rel="stylesheet" type="text/css" media="all" href="css/colorbox.css" />
<!-- END -->
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery.ui.core.js"></script>
<script src="js/jquery.ui.widget.js"></script>
<script src="js/jquery.ui.button.js"></script>
<script src="js/jquery.ui.position.js"></script>
<script src="js/jquery.ui.autocomplete.js"></script>
<script src="js/jquery.colorbox-min.js"></script>
</head>
<body><br><br>');
	}
	echo '<h1 class="error alertjs-container">'.$msg.'</h1>';
	if ($die) {
		die('</body></html>');
	}
}
/**
 *
 * Sanitazes a string
 * @param unknown_type $str
 */
function sanitise_string($str) {
	return str_replace('-','_',str_replace(':','_',$str));
}
/**
 * 
 * Per eliminar la room al final
 * @param unknown_type $room
 */
function delete_xml_file($room) {
	$r = false;
	if(is_file(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml")) {
		//Avoid DELETION the XML because there are some problems
		//$r = unlink(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");
		
	}
	return $r;
		
}
/**
 * 
 * Gets the minutes of number of secconds
 * @param unknown_type $seconds
 */
function minutes( $seconds )
{
	return sprintf( "%02.2d:%02.2d", floor( $seconds / 60 ), $seconds % 60 );
}
/**
*
* Gets the time of number of secconds
* @param unknown_type $seconds
*/
function time_format( $seconds )
{
	return  gmdate("H:i:s", $seconds);
}

/**
 * 
 * Gets the name from unzipped package
 * @param unknown_type $directory
 */ 
function getNameXmlFileUnZipped($directory){
	$filename = false;
	$results = array();
	$handler = opendir($directory);
	while ($file = readdir($handler)) {
		if ($file != "." && $file != ".." && $file != ".DS_STORE") $results[] = $file;
	}
	closedir($handler);
	foreach($results as $value){
		$extension = explode(".", $value);
		$isSys = explode("data", $value);
		if(count($extension)>1 && $extension[1]=="xml" &&
			 count($isSys)>1 && $isSys[1]!="")
		{
			$filename = str_replace('.xml','',$isSys[1]);
			break;
		}
	}
	return $filename;
}
/**
 * 
 * Move from the temporal folder to course folder
 * @param unknown_type $source
 * @param unknown_type $destination
 */
function moveFromTempToCourseFolder($source, $destination, $delete) {
	if (in_array(basename($source), array('.','..','__MACOSX','.DS_Store'))) 
		return $delete;
	
	if (is_file($source)) {
		if (copy($source, $destination)) {
			$delete[] = $source;
		}
	} else {
		if (!file_exists($destination))
			mkdir($destination, 0777, true);
		// Get array of all source files
		$files = scandir($source);
		// Identify directories
		// Cycle through all source files
		foreach ($files as $file) {
			if (in_array($file, array('.','..','__MACOSX','.DS_Store'))) continue;
			$file = '/'.$file;
			$delete[] = moveFromTempToCourseFolder($source.$file, $destination.$file, $delete);
		}
	}
	return $delete;
}
/**
 * 
 * Deletes recursively the folder 
 * @param unknown_type $path
 */
function rrmdir($path)
{
	$r = false;
	if (in_array(basename($path), array('.','..'))){
		$r = true;
	} else {
		if (is_file($path)) {
			$r = @unlink($path); 
		} else {
			foreach (scandir($path) as $file) {
				$r = rrmdir($path.'/'.$file);
			}
			$r = rmdir($path);
		}
	}
	return $r;
}

/**
 * 
 * Check if session is correct if not redirects
 */
function check_user_session() {
	if (!$_SESSION) {
		session_start();
	}
	if (!isset($_SESSION[CURRENT_USER]) || !isset($_SESSION[CURRENT_USER]->id) || !isset($_SESSION[COURSE_ID])) {
		header('Location: index.php');
		die();
	}
}
/**
 * This function convert to utf8
 * @param type $s
 */
function convertToUtf8 ($s) {
    if (mb_detect_encoding ($s, 'ISO-8859-1', true)){
        $s = mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
    }
    return $s;
}
/**
 * Get the current url
 * @return [type] [description]
 */
function curPageURL() {
 $pageURL = 'http';
 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

/**
 * Do a Post request
 * @param  [type] $url    [description]
 * @param  [type] $is_post    [description]
 * @param  array  $params [description]
 * @param  [type] $header [description]
 * @return [type]         [description]
 */
function doRequest($url, $is_post, $params = array(), $header = NULL) {

    $response = '';
    if (is_array($params)) {
      $data = http_build_query($params);
    } else {
      $data = $params;
    }

    if (function_exists('curl_init')) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      if (!empty($header)) {
        $headers = explode("\n", $header);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      }

      curl_setopt($ch, CURLOPT_POST, $is_post);
      if ($is_post) {
      	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      }
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $response = curl_exec($ch);
      curl_close($ch);
    } else {
      $opts = array('method' => $is_post?'POST':'GET',
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
    }

    return $response;

  }
 
function is_url_exist($url){
    $ch = curl_init($url);  
    curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_exec($ch);

    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if($code == 200){
       $status = true;
    }else{
      $status = false;
    }
    curl_close($ch);
   return $status;
}

function secondsToTime($seconds)
    {
        // extract hours
        $hours = floor($seconds / (60 * 60));
     
        // extract minutes
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
     
        // extract the remaining seconds
        $divisor_for_seconds = $divisor_for_minutes % 60;
        $seconds = ceil($divisor_for_seconds);
     
        // return the final array
        $obj = array(
            "h" => (int) $hours,
            "m" => (int) $minutes,
            "s" => (int) $seconds,
        );
        return $obj;
    }

