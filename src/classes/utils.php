<?php
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
			$osidContext->assignContext(AUTHORIZATION_KEY_FIELD_OKI, AUTHORIZATION_KEY_OKI);
			$_SESSION[HTTPATTRIBUTEKEY_OSIDCONTEXT]=serialize($osidContext);
		}
	} catch (Exception $e) {
		show_error($e->getMessage());
	}
	return $osidContext;
}

function lti_get_lang($context) {
	$lang = 'en-US';
	if (isset($context->info[LAUNCH_PRESENTATION_LOCALE]))
	$custom_lang_id = $context->info[LAUNCH_PRESENTATION_LOCALE];
	$custom_lang_id = '';
	if (isset($context->info[CUSTOM_LANG]))
	$custom_lang_id = $context->info[CUSTOM_LANG];
	switch ($custom_lang_id)
	{
		case "a":
			$lang="ca-ES";
			break;
		case "b":
			$lang="es-ES";
			break;
		case "d":
			$lang="fr-FR";
			break;
		default:
			$lang="en-US";
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
function show_error($msg) {
	echo '<div class="error">'.$msg.'</div>';
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
	if(is_file($room.".xml")) {
		//DELETE - 20121005 - abertranb - Deletes the unset session 
		//Netegem la sessio
		//session_unset();
		//END
		$r = unlink($room.".xml");
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
		if($extension[1]=="xml" && $isSys[1]!="")
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
		if (copy($source.$file, $destination.$file)) {
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

