<?php
/**
 * This lang.php file does quite a few things:
 * - Import constants from config.inc.php and constants.php
 * - Initializes session
 * - Instantiates Language class as $LanguageInstance (this instance handles multilanguage across all pages)
 */

require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/constants.php';

session_start();

class Language {
    /**
     * @return Language|null
     */
	public static function &_instance() {
	    static $instance = null;
	    if (null === $instance) {
	      $instance = new Language();
	    }

	    return $instance;
	}

    /**
     * @return Language|null
     */
	public static function init() {

        $locale = isset($_SESSION[LANG]) ? $_SESSION[LANG] : false;

        if (defined('DEBUG_MULTILANGUAGE_ENABLED') && DEBUG_MULTILANGUAGE_ENABLED) {
            $locale = defined('DEBUG_FORCED_LOCALE') ? DEBUG_FORCED_LOCALE : $locale;
            $locales_root = __DIR__ . '/../languages';
            $domain = 'messages';
            setlocale(LC_ALL, $locale);
            setlocale(LC_TIME, $locale);
            putenv("LANG=$locale");
            $filename = "$locales_root/$locale/LC_MESSAGES/$domain.mo";
            $mtime = filemtime($filename);
            $filename_new = "$locales_root/$locale/LC_MESSAGES/{$domain}_{$mtime}.mo";
            if (!file_exists($filename_new)) { // check if we have created it before
                copy($filename, $filename_new);
            }
            $domain_new = "{$domain}_{$mtime}";
            bind_textdomain_codeset('messages', 'UTF-8');
            bindtextdomain($domain_new, $locales_root);
            textdomain($domain_new);
        } else {
            if (!$locale) {
                $locale = 'en_US';
                $_SESSION[LANG] = $locale;
            }
            if (!file_exists(__DIR__ . '/../languages/' . $locale . '/LC_MESSAGES/messages.po')) {
                $locale = 'en_US';
                $_SESSION[LANG] = $locale;
            }
            bind_textdomain_codeset('messages', 'UTF-8');
            putenv('LANG=' . $locale);
            setlocale(LC_ALL, $locale);
            setlocale(LC_TIME, $locale);
            bindtextdomain('messages', 'languages');
            textdomain('messages');
        }

        $instance = &Language::_instance();

        return $instance;
    }

    /**
     * Get the translation
     * @param string $string
     * @return string
     */
	public function get($string) {
		return gettext($string);
	}

    /**
     * Get the translation and do sprintf
     * @param string $string
     * @param string $subs
     * @return string
     */
	public function getTag($string, $subs) {
		return sprintf(gettext($string), $subs);
	}

    /**
     * Get the translation and do sprintf
     * @param string $string
     * @param string $subs1
     * @param string $subs2
     * @return string
     */
	public function getTagDouble($string, $subs1, $subs2) {
		return sprintf(gettext($string), $subs1, $subs2);
	}
}

$LanguageInstance = Language::init();
