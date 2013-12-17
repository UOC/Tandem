<?php 
require_once 'constants.php';
session_start();
$lang = isset($_SESSION[LANG])?$_SESSION[LANG]:false;
if (!$lang) {
	$lang = 'es-ES';
	$_SESSION[LANG] = $lang;
}
require_once dirname(__FILE__).'/../languages/'.$lang.'.php';

class Language {

	/**
	 * 
	 * Get the translation
	 * @param string $string
	 */
	public static function get($string) {
		global $language;		
		if (isset($language[$string])) {
			$string = $language[$string];
		}	
		return $string;
	}
	
	/**
	*
	* Get the translation and do sprintf
	* @param string $string
	* @param string $subs
	*/
	public static function getTag($string, $subs) {
		global $language;
		if (isset($language[$string])) {
			$string = $language[$string];
		}
		return sprintf($string, $subs);
	}
	
	/**
	*
	* Get the translation and do sprintf
	* @param string $string
	* @param string $subs1
	* @param string $subs2
	*/
	public static function getTagDouble($string, $subs1, $subs2) {
	global $language;
	if (isset($language[$string])) {
	$string = $language[$string];
	}
	return sprintf($string, $subs1, $subs2);
	}
}
?>