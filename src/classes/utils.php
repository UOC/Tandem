<?php

require_once dirname(__FILE__) . '/../lib/vendor/autoload.php';

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

require_once('constants.php');

/**
 * Gets if file exists in include path or directely
 *
 * @param $file
 *
 * @return bool
 */
function filexists_tandem($file) {
    $ps = explode(":", ini_get('include_path'));
    if ($ps) {
        foreach ($ps as $path) {
            if (file_exists($path . '/' . $file)) {
                return true;
            }
        }
    }
    if (file_exists($file)) {
        return true;
    }

    return false;
}

/**
 * Obte el llistat d'usuaris
 *
 * @param $gestorBD
 * @param $course_id
 * @param $context
 *
 * @return mixed
 */
function posa_osid_context_session($gestorBD, $course_id, $context) {
    //Loads users
    $osidContext = false;
    try {
        $_SESSION[HTTPATTRIBUTEKEY_OSIDCONTEXT] = null;
        $required_class = 'org/campusproject/utils/OsidContextWrapper.php';
        $exists = filexists_tandem($required_class);
        if (!$exists) {
            return true;
        }
        require_once $required_class;
        $oauth_consumer_key = $context->info[OATUH_CONSUMER_KEY];
        $utilsProperties = new UtilsPropertiesBLTI(dirname(__FILE__) . '/../configuration_oki.cfg');

        $okibusPHP_components = $utilsProperties->getProperty(PROVIDER_PREFIX_OKI_COMPONENTS . $oauth_consumer_key,
                false);

        $okibusPHP_okibusClient = $utilsProperties->getProperty(PROVIDER_PREFIX_OKI . $oauth_consumer_key, false);
        if (!$okibusPHP_components || !$okibusPHP_okibusClient) {
            //TODO posar configuracio a OKI IOC
            //show_error('review_configuration_oki');
            $osidContext = true;
        } else {
            putenv('okibusPHP_components=' . $okibusPHP_components);
            $_SESSION[OKIBUSPHP_COMPONENTS] = $okibusPHP_components;
            $_SESSION[OKIBUSPHP_OKIBUSCLIENT] = $okibusPHP_okibusClient;
            $okibusPHP_okibusClient_num_fields =
                    intval($utilsProperties->getProperty(PROVIDER_PREFIX_OKI . $oauth_consumer_key . '_num_fields',
                            0));
            $osidContext = new OsidContextWrapper();
            for ($i = 1; $i <= $okibusPHP_okibusClient_num_fields; $i++) {
                $okibusPHP_okibusClient_field_lti =
                        $utilsProperties->getProperty(PROVIDER_PREFIX_OKI . $oauth_consumer_key . '_field_lti_' . $i,
                                '');
                $okibusPHP_okibusClient_field_oki =
                        $utilsProperties->getProperty(PROVIDER_PREFIX_OKI . $oauth_consumer_key . '_field_oki_' . $i,
                                '');
                $okibusPHP_okibusClient_field_prefix_oki =
                        $utilsProperties->getProperty(PROVIDER_PREFIX_OKI . $oauth_consumer_key . '_field_prefix_oki_' . $i,
                                '');
                if (isset($context->info[$okibusPHP_okibusClient_field_lti])) {
                    $osidContext->assignContext($okibusPHP_okibusClient_field_oki,
                            $okibusPHP_okibusClient_field_prefix_oki . $context->info[$okibusPHP_okibusClient_field_lti]);
                }
            }
            //Aquesta es de uoc i necessaria
            $osidContext->assignContext(AUTHORIZATION_KEY_FIELD_OKI,
                    $utilsProperties->getProperty(PROVIDER_PREFIX_OKI . $oauth_consumer_key . '_' . AUTHORIZATION_KEY_FIELD_OKI));
            $_SESSION[HTTPATTRIBUTEKEY_OSIDCONTEXT] = serialize($osidContext);
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
        switch ($custom_lang_id) {
            case "a":
                $lang = "ca_ES";
                break;
            case "b":
                $lang = "es_ES";
                break;
            case "d":
                $lang = "fr_FR";
                break;
            default:
                $lang = "en_US";
        }
    }
    if (strlen($lang) < 4) {
        switch ($lang) {
            case "en":
                $lang = "en_US";
                break;
            case "es":
                $lang = "es_ES";
                break;
            case "ca":
                $lang = "ca_ES";
                break;
            case "fr":
                $lang = "fr_FR";
                break;
            case "nl":
                $lang = "nl_NL";
                break;
            case "it":
                $lang = "it_IT";
                break;
            case "fi":
                $lang = "fi_FI";
                break;
            case "de":
                $lang = "de_DE";
                break;
            case "pl":
                $lang = "pl_PL";
                break;
            case "hr":
                $lang = "hr_HR";
                break;
            case "sv":
                $lang = "sv_SE";
                break;
            case "ga":
                $lang = "ga_IE";
                break;
            default:
                $lang = "en_US";

        }
    }
    if ($lang == 'en_GB') {
        $lang = 'en_US';
    }

    if (strlen($lang) == 5) {
        //To Moodle 2 because send all as lowercase
        $lang = substr($lang, 0, 3) . strtoupper(substr($lang, 3, 2));
    }

    return $lang;
}

/**
 * @param $id_tandem
 * @param $id_resource
 *
 * @return string
 */
function getTandemIdentifier($id_tandem, $id_resource) {
    return $id_resource . '_' . $id_tandem;
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
 *
 * @param $message
 * @param bool $debug
 */
function debugMessageIT($message, $debug = false) {
    if ($debug) {
        echo "<p>$message</p><br>";
    }
}

/**
 * Shows the message
 *
 * @param $msg
 * @param bool $die
 */
function show_error($msg, $die = false) {
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
    echo '<h1 class="error alertjs-container">' . $msg . '</h1>';
    if ($die) {
        die('</body></html>');
    }
}

/**
 * Sanitizes a string
 *
 * @param string $str
 *
 * @return string
 */
function sanitise_string($str) {
    return str_replace(array(':', '-'), '_', $str);
}

/**
 * Per eliminar la room al final
 *
 * @param $room
 *
 * @return bool
 */
function delete_xml_file($room) {
    $r = false;
    if (is_file(PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml")) {
        //Avoid DELETION the XML because there are some problems
        //$r = unlink(PROTECTED_FOLDER.DIRECTORY_SEPARATOR.$room.".xml");

    }

    return $r;

}

/**
 * Gets the minutes of number of secconds
 *
 * @param $seconds
 *
 * @return string
 */
function minutes($seconds) {
    return sprintf("%02.2d:%02.2d", floor($seconds / 60), $seconds % 60);
}

/**
 * Gets the time of number of secconds
 *
 * @param $seconds
 *
 * @return false|string
 */
function time_format($seconds) {
    return gmdate("H:i:s", $seconds);
}

/**
 * Gets the name from unzipped package
 *
 * @param $directory
 *
 * @return bool|mixed
 */
function getNameXmlFileUnZipped($directory) {
    $filename = false;
    $results = array();
    $handler = opendir($directory);
    while ($file = readdir($handler)) {
        if ($file != "." && $file != ".." && $file != ".DS_STORE") {
            $results[] = $file;
        }
    }
    closedir($handler);
    foreach ($results as $value) {
        $extension = explode(".", $value);
        $isSys = explode("data", $value);
        if (count($extension) > 1 && $extension[1] == "xml" &&
                count($isSys) > 1 && $isSys[1] != "") {
            $filename = str_replace('.xml', '', $isSys[1]);
            break;
        }
    }

    return $filename;
}

/**
 * Move from the temporal folder to course folder
 *
 * @param $source
 * @param $destination
 * @param $delete
 *
 * @return array
 */
function moveFromTempToCourseFolder($source, $destination, $delete) {
    if (in_array(basename($source), array('.', '..', '__MACOSX', '.DS_Store'))) {
        return $delete;
    }

    if (is_file($source)) {
        if (copy($source, $destination)) {
            $delete[] = $source;
        }
    } else {
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }
        // Get array of all source files
        $files = scandir($source);
        // Identify directories
        // Cycle through all source files
        foreach ($files as $file) {
            if (in_array($file, array('.', '..', '__MACOSX', '.DS_Store'))) {
                continue;
            }
            $file = '/' . $file;
            $delete[] = moveFromTempToCourseFolder($source . $file, $destination . $file, $delete);
        }
    }

    return $delete;
}

/**
 * Deletes recursively the folder
 *
 * @param $path
 *
 * @return bool
 */
function rrmdir($path) {
    $r = false;
    if (in_array(basename($path), array('.', '..'))) {
        $r = true;
    } else {
        if (is_file($path)) {
            $r = @unlink($path);
        } else {
            foreach (scandir($path) as $file) {
                $r = rrmdir($path . '/' . $file);
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
 *
 * @param type $s
 *
 * @return string|type
 */
function convertToUtf8($s) {
    if (mb_detect_encoding($s, 'ISO-8859-1', true)) {
        $s = mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
    }

    return $s;
}

/**
 * Get the current url
 *
 * @return string
 */
function curPageURL() {
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    return $pageURL;
}

/**
 * Do a Post request
 *
 * @param  $url
 * @param  $is_post
 * @param array $params
 * @param  $header
 *
 * @return bool|mixed|string
 */
function doRequest($url, $is_post, $params = array(), $header = null) {

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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $opts = array(
                'method' => $is_post ? 'POST' : 'GET',
                'content' => $data
        );
        if (!empty($header)) {
            $opts['header'] = $header;
        }
        $ctx = stream_context_create(array('http' => $opts));
        $fp = @fopen($url, 'rb', false, $ctx);
        if ($fp) {
            $resp = @stream_get_contents($fp);
            if ($resp !== false) {
                $response = $resp;
            }
        }
    }

    return $response;

}

function is_url_exist($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_exec($ch);

    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($code == 200) {
        $status = true;
    } else {
        $status = false;
    }
    curl_close($ch);

    return $status;
}

function secondsToTime($seconds) {
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

/**
 * Get the Overall As Number to add it to ranking
 *
 * @param  $skills_grade
 *
 * @return int
 */
function getOverallAsNumber($skills_grade) {
    $value = 0;
    switch ($skills_grade) {
        case 'A':
            $value = 100;
            break;
        case 'B':
            $value = 80;
            break;
        case 'C':
            $value = 60;
            break;
        case 'D':
            $value = 40;
            break;
        case 'F':
            $value = 20;
            break;
    }

    return $value;
}

/**
 * Gets the equivalence between number and Identifier
 *
 * @param  $value
 *
 * @return string
 */
function getOverallAsIdentifier($value) {
    $skills_grade = 'C';
    if ($value > 80) {
        $skills_grade = 'A';
    } else if ($value > 60) {
        $skills_grade = 'B';
    } else if ($value > 40) {
        $skills_grade = 'C';
    } else if ($value > 20) {
        $skills_grade = 'D';
    } else {
        $skills_grade = 'F';
    }

    return $skills_grade;
}

/**
 * Translate from A to grade
 *
 * @param  $skills_grade
 * @param  $LanguageInstance
 *
 * @return string
 */
function getSkillsLevel($skills_grade, $LanguageInstance) {
    $skillGrade = '';
    switch ($skills_grade) {
        case 'A':
            $skillGrade = $LanguageInstance->get('Excellent');
            break;
        case 'B':
            $skillGrade = $LanguageInstance->get('Very Good');
            break;
        case 'C':
            $skillGrade = $LanguageInstance->get('Good');
            break;
        case 'D':
            $skillGrade = $LanguageInstance->get('Pass');
            break;
        case 'F':
            $skillGrade = $LanguageInstance->get('Fail');
            break;
    }

    return $skillGrade;
}

/**
 * Get Scale by Grade
 *
 * @param  $LanguageInstance
 * @param  $level
 *
 * @return mixed
 */
function getScaleGrade($LanguageInstance, $level) {
    $levelArray = array('not at all', 'a little', 'quite a bit', 'a lot');

    return $LanguageInstance->get($levelArray[$level - 1]);
}

/**
 * @param GestorBD $gestorBD
 * @param Language $LanguageInstance
 * @param $user
 * @param $feedbackSelfreflectionForm
 * @param $courseId
 * @param $feedbacks
 *
 * @return array
 */
function getRankingAndProgressData(
        $gestorBD,
        $LanguageInstance,
        $user,
        $feedbackSelfreflectionForm,
        $courseId,
        $feedbacks
) {
    $userPointsData = $gestorBD->getUserRankingPoints($user['id'], $courseId);

    $data = array();

    if (isset($userPointsData->points)) {
        $data[] = array(
                'label' => $LanguageInstance->get('points'),
                'value' => $userPointsData->points,
        );
    }

    if (isset($userPointsData->number_of_tandems)) {
        $value = $userPointsData->number_of_tandems;
        if (!empty(CERTIFICATION_MINIMUM_TANDEMS)) {
            $tandemsProgress = round($userPointsData->number_of_tandems * 100 / CERTIFICATION_MINIMUM_TANDEMS);
            $tandemsProgress = $tandemsProgress > 100 ? 100 : $tandemsProgress;
            $value .= ' (' . $tandemsProgress . '%)';
        }

        $data[] = array(
                'label' => $LanguageInstance->get('Number of Tandems'),
                'value' => $value,
        );
    }

    if (isset($userPointsData->total_time)) {
        $value = gmdate('H:i:s', (int) $userPointsData->total_time);
        if (!empty(CERTIFICATION_MINIMUM_HOURS)) {
            $hoursProgress = round($userPointsData->total_time * 100 / (CERTIFICATION_MINIMUM_HOURS * 3600));
            $hoursProgress = $hoursProgress > 100 ? 100 : $hoursProgress;
            $value .= ' (' . $hoursProgress . '%)';
        }

        $data[] = array(
                'label' => $LanguageInstance->get('Total Duration'),
                'value' => $value,
        );
    }

    if (!$feedbackSelfreflectionForm && isset($userPointsData->user_fluency)) {
        $data[] = array(
                'label' => $LanguageInstance->get('Fluency'),
                'value' => $userPointsData->user_fluency,
        );
    }

    if (!$feedbackSelfreflectionForm && isset($userPointsData->user_accuracy)) {
        $data[] = array(
                'label' => $LanguageInstance->get('Accuracy'),
                'value' => $userPointsData->user_accuracy,
        );
    }

    if (!$feedbackSelfreflectionForm && isset($userPointsData->user_overall_grade)) {
        $data[] = array(
                'label' => $LanguageInstance->get('Overall Grade'),
                'value' => $userPointsData->user_overall_grade,
        );
    }

    if (isset($userPointsData->number_of_tandems_with_feedback)) {
        $data[] = array(
                'label' => $LanguageInstance->get('Feedback received'),
                'value' => $userPointsData->number_of_tandems_with_feedback,
        );
    }

    if (!empty($feedbacks)) {
        $numberOfGivenFeedbacks = count($feedbacks);
        $value = $numberOfGivenFeedbacks;
        if (!empty(CERTIFICATION_MINIMUM_FEEDBACKS)) {
            $feedbacksProgress = round($numberOfGivenFeedbacks * 100 / CERTIFICATION_MINIMUM_FEEDBACKS);
            $feedbacksProgress = $feedbacksProgress > 100 ? 100 : $feedbacksProgress;
            $value .= ' (' . $feedbacksProgress . '%)';
        }

        $data[] = array(
                'label' => $LanguageInstance->get('Feedback given'),
                'value' => $value,
        );
    }

    return $data;
}

/**
 * @param Language $LanguageInstance
 * @param $newProfileForm
 *
 * @return array
 */
function getNewProfileData($LanguageInstance, $newProfileForm) {
    $profileGrammaticalResource =
            isset($newProfileForm['data']->grammaticalresource) ? $newProfileForm['data']->grammaticalresource : '0';
    $profileLexicalResource = isset($newProfileForm['data']->lexicalresource) ? $newProfileForm['data']->lexicalresource : '0';
    $profileDiscourseMangement =
            isset($newProfileForm['data']->discoursemangement) ? $newProfileForm['data']->discoursemangement : '0';
    $profilePronunciation = !empty($newProfileForm['data']->pronunciation) ? $newProfileForm['data']->pronunciation : '0';
    $profileInteractiveCommunication =
            !empty($newProfileForm['data']->interactivecommunication) ? $newProfileForm['data']->interactivecommunication : '0';

    $data = array();

    $data[] = array(
            'label' => $LanguageInstance->get('Grammatical Resource'),
            'value' => $profileGrammaticalResource . '%',
    );
    $data[] = array(
            'label' => $LanguageInstance->get('Lexical Resource'),
            'value' => $profileLexicalResource . '%',
    );
    $data[] = array(
            'label' => $LanguageInstance->get('Discourse Mangement'),
            'value' => $profileDiscourseMangement . '%',
    );
    $data[] = array(
            'label' => $LanguageInstance->get('Pronunciation'),
            'value' => $profilePronunciation . '%',
    );
    $data[] = array(
            'label' => $LanguageInstance->get('Interactive Communication'),
            'value' => $profileInteractiveCommunication . '%',
    );

    return $data;
}

/**
 * @param Language $LanguageInstance
 * @param $firstProfileForm
 *
 * @return array
 */
function getFirstProfileData($LanguageInstance, $firstProfileForm) {
    $profileFluency = isset($firstProfileForm['data']->fluency) ? $firstProfileForm['data']->fluency : '0';
    $accuracyProfile = isset($firstProfileForm['data']->accuracy) ? $firstProfileForm['data']->accuracy : '0';
    $myPronunciation =
            isset($firstProfileForm['data']->improve_pronunciation) ? $firstProfileForm['data']->improve_pronunciation : '';
    $myVocabulary = !empty($firstProfileForm['data']->improve_vocabulary) ? $firstProfileForm['data']->improve_vocabulary : '';
    $myGrammar = !empty($firstProfileForm['data']->improve_grammar) ? $firstProfileForm['data']->improve_grammar : '';

    $data = array();

    $data[] = array(
            'label' => $LanguageInstance->get('Grade your speaking skills'),
            'value' => getSkillsLevel($firstProfileForm['data']->skills_grade, $LanguageInstance),
    );
    $data[] = array(
            'label' => $LanguageInstance->get('Fluency'),
            'value' => $profileFluency . '%',
    );
    $data[] = array(
            'label' => $LanguageInstance->get('Accuracy'),
            'value' => $accuracyProfile . '%',
    );
    $data[] = array(
            'label' => $LanguageInstance->get('My pronunciation'),
            'value' => $myPronunciation,
    );
    $data[] = array(
            'label' => $LanguageInstance->get('My vocabulary'),
            'value' => $myVocabulary,
    );
    $data[] = array(
            'label' => $LanguageInstance->get('My grammar'),
            'value' => $myGrammar,
    );

    return $data;
}

/**
 * With BigBlueButton we have to check
 *
 * @param $download_url
 *
 * @return mixed
 *
 * function getDownloadURLFromBBB ( $download_url ) {
 * if ( defined( 'BBB_SECRET' ) ) {
 * $download_url = getBBBFile($download_url);
 * }
 * return $download_url;
 * }
 *
 * function getBBBFile($download_url, $level = 0) {
 * $url_check = false;
 * if ($level < 20) {
 * $url_check = $download_url . str_replace( '$ID$', $level, 'capture-$ID$.m4v' );
 * error_log("Check $url_check");
 * if ( ! is_url_exist( $url_check ) ) {
 * $url_check = getBBBFile( $download_url, 1 + $level );
 * }
 * }
 * return $url_check;
 * }*/

function uploadVideoToS3($download_url, $new_name = false) {
    $upload = false;
    if (defined('AWS_URL') && defined('AWS_S3_BUCKET') && defined('AWS_S3_FOLDER') && defined('AWS_S3_USER') &&
            defined('AWS_S3_SECRET')) {
        try {
            // No required because BBB use authenticated URL
            // $download_url = getDownloadURLFromBBB ( $download_url );
            if (!$download_url) {
                return;
            }
            $ch = curl_init($download_url);
            $file_nameArray = explode('/', $download_url);
            $file_name = $file_nameArray[count($file_nameArray) - 1];
            $file_name = str_replace('/', '_', $file_name);
            $file_name = str_replace(':', '_', $file_name);
            $file_name = str_replace('=', '_', $file_name);

            $fp = fopen(TMP_FOLDER . '/' . $file_name, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            if (file_exists(TMP_FOLDER . '/' . $file_name)) {
                $key = AWS_S3_FOLDER . '/' . ($new_name ? $new_name : $file_name);
                // Instantiate an S3 client
                $s3 = S3Client::factory(array(
                        'key' => AWS_S3_USER,
                        'secret' => AWS_S3_SECRET,
                ));

                // Upload a publicly accessible file. The file size, file type, and MD5 hash
                // are automatically calculated by the SDK.
                $s3->putObject(array(
                        'Bucket' => AWS_S3_BUCKET,
                        'Key' => $key,
                        'Body' => fopen(TMP_FOLDER . '/' . $file_name, 'r'),
                        'ACL' => 'public-read',
                ));
                unlink(TMP_FOLDER . '/' . $file_name);
                $upload = true;
            } else {
                error_log("There was an error downloading the file. FROM " . $download_url);
            }
        } catch (S3Exception $e) {
            error_log("There was an error uploading the file. " . $e->getMessage());
        }

    }

    return $upload;
}

function get_video_from_s3($url_video) {
    if (defined('AWS_URL') && defined('AWS_S3_BUCKET') && defined('AWS_S3_FOLDER') && defined('AWS_S3_USER') &&
            defined('AWS_S3_SECRET')) {
        $file_nameArray = explode('/', $url_video);
        $file_name = $file_nameArray[count($file_nameArray) - 1];
        $file_name = str_replace(array('/', ':', '='), '_', $file_name);
        $awsurl = AWS_URL . AWS_S3_BUCKET . '/' . AWS_S3_FOLDER . '/' . $file_name;
        if (is_url_exist($awsurl)) {
            $url_video = $awsurl;
        }
    }
    return $url_video;
}

function check_post_file_is_ok($files, $name) {
    if (isset($files[$name]) && !empty($files[$name]['tmp_name'])) {

        if (the_file_is_image($files[$name]['tmp_name'])) {
            return FILE_OK;
        }
        return FILE_FORMAT_ERROR;
    }
    return FILE_NOT_SEND_IN_FORM;
}

function the_file_is_image($path) {
    $a = getimagesize($path);
    $image_type = $a[2];

    if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
        return true;
    }
    return false;
}

function get_repository_path() {
    $path = __DIR__ . '/../repository';
    mkdir_if_not_exist($path);
    return $path;
}

function get_task_path($course_id, $task_id) {
    $path = get_repository_path() . '/' . $course_id . '/' . $task_id;
    mkdir_if_not_exist($path);
    return $path;
}

function mkdir_if_not_exist($path) {
    if (!is_dir($path)) {
        //Directory does not exist, so lets create it.
        mkdir($path, 0755, true);
    }
}

function upload_files_task($course_id, $task_id, $images) {
    $path = get_task_path($course_id, $task_id);
    $results = array();
    foreach ($images as $image) {
        $result = check_post_file_is_ok($_FILES, $image);
        $relative_path = '';
        if ($result === FILE_OK) {
            $target_path = $path . '/' . $image;
            mkdir_if_not_exist($target_path);
            $target_path_file = $target_path . '/' . $_FILES[$image]['name'];
            if (move_uploaded_file($_FILES[$image]['tmp_name'], $target_path_file)) {
                $relative_path = str_replace(get_repository_path(), '', $target_path_file);
                $result = FILE_UPLOADED;
            } else {
                $result = FILE_CAN_NOT_BE_UPLOADED;
            }
        }
        $results[$image] = ['result' => $result, 'path' => $relative_path];
    }
    return $results;
}

function get_file_name($str) {
    if (!empty($str)) {
        $arr_file = explode('/', $str);
        $str = end($arr_file);
    }
    return $str;
}

function generate_image_from_repository($exerciseId, $gestorBD, $course_id, $course_folder) {
    $exercise_array = $gestorBD->get_exercise($exerciseId);
    if (count($exercise_array) > 0) {
        $exercise = $exercise_array[0];
        $tasks = $gestorBD->getLinkedTasks($exerciseId, $course_id);
        $target_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $course_folder . DIRECTORY_SEPARATOR . $exercise['id'];
        mkdir_if_not_exist($target_path);
        $xml_path = $target_path . DIRECTORY_SEPARATOR . 'data'. $exercise['name_xml_file'] . '.xml';
        $content = '<?xml version="1.0" encoding="UTF-8"?><exe lang="en">';
        $node = 0;
        $initpath = 'init';
        $endpath = 'end';

        foreach ($tasks as $task) {

            $is_same_picture = empty(strip_tags($task['descriptionB']));
            if ($node == 0) {
                $content .= '<nextType classOf="sampleConfirm" currSample="' .
                        get_repository_task_identifier($task, $is_same_picture) . '" node="' .
                        $node . '" numBtns="1" numUsers="2"/>';
            }
            $node++;
            $timer = $task['timer_duration'] > 0 ? 'timer="' . $task['timer_duration'] . ':00"' : '';
            $extra_userA = $is_same_picture ? '' : '_A';
            $extra_userB = $is_same_picture ? '' : '_B';
            $content .= '<nextType classOf="sampleConfirm" currSample="' . get_repository_task_identifier($task, $is_same_picture) . '" endHTML="' .
                    $endpath . '/index.php" initHTML="' . $initpath . '/index' . $extra_userA . '.php" initHTMLB="' . $initpath .
                    '/index' . $extra_userB . '.php" node="1" numBtns="1" numUsers="2" ' . $timer . '>
                <textE><![CDATA[<p><strong>' . $task['typology'] . '</strong></p>]]></textE>
            </nextType>';
            render_task_files($target_path, $initpath, $endpath, $task, $is_same_picture);
        }
        $content .= '</exe>';
        file_put_contents($xml_path, $content);
    }
}

function render_task_files($target_path, $initpath, $endpath, $task, $is_same_picture) {
    $path = $target_path . DIRECTORY_SEPARATOR . 'ejercicios'. DIRECTORY_SEPARATOR . get_repository_task_identifier($task, $is_same_picture);
    mkdir_if_not_exist($path);
    $initfullpath = $path . DIRECTORY_SEPARATOR . $initpath;
    mkdir_if_not_exist($initfullpath);
    if ($is_same_picture) {
        generate_file_html_task($initfullpath . DIRECTORY_SEPARATOR . 'index.php', $task['descriptionA'], $task['imageA'], $task['imageA2']);
    } else {
        generate_file_html_task($initfullpath . DIRECTORY_SEPARATOR . 'index_A.php', $task['descriptionA'], $task['imageA'], $task['imageA2']);
        generate_file_html_task($initfullpath . DIRECTORY_SEPARATOR . 'index_B.php', $task['descriptionB'], $task['imageB'], $task['imageB2']);
    }
    $endfullpath = $path . DIRECTORY_SEPARATOR . $endpath;
    mkdir_if_not_exist($endfullpath);
    generate_file_html_task($endfullpath . DIRECTORY_SEPARATOR . 'index.php', $task['solutionA'], $task['solutionImageA'], $task['solutionImageA2']);

}
function generate_file_html_task($filepath, $text, $image1, $image2) {
    file_put_contents($filepath, generate_html_task($text, $image1, $image2));
}
function get_repository_task_identifier($task, $is_same_picture) {
    $type = $is_same_picture ? 'sampleSamePicture.SamePicture' : 'sampleDifferentPictures.DifferentPictures';
    return $type . 'Repo' . $task['id'];
}
function get_text_image_task($image) {
    $r = '';
    if (!empty($image)) {
        $image_path = get_path_to_main_assets() . '/../repository'. $image;
        $r = '<p><span class="wikiattachmentlink"><a onclick="parent.$.fn.colorbox(&#123;href: this.href}); return false;" title="Zoom" href="'.$image_path.'"><img src="'.$image_path.'" alt="'.get_file_name($image).'"/></a></span>&nbsp;</p>';
    }
    return $r;
}
function get_path_to_main_assets() {
    return '../../../../..';
}
function generate_html_task($text, $image1, $image2) {

    $content = '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset=utf-8 />
    <title>Tandem</title>
    <link rel="stylesheet" type="text/css" href="'. get_path_to_main_assets(). '/css/task.css"/>
  </head>
  <body>
    <table width="800" border="0" cellpadding="4" cellspacing="17">
    <tbody>
      <tr>
        <td width="300" height="341" valign="top">'.$text.'</td>
        <td width="500" valign="top">'.get_text_image_task($image1).get_text_image_task($image2).'</td>
      </tr>
    </tbody>
    </table>
  </body>
</html>';

    return $content;

}