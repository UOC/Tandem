<?php
/**
 * Returns user information of the users that are waiting to join a Tandem.
 */

require_once __DIR__ . '/classes/lang.php';
require_once __DIR__ . '/classes/gestorBD.php';

$gestorBD = new GestorBD();
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
$lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : false;

if ($lang === 'all') {
    $details = $gestorBD->getUsersDetailsWaiting($course_id);
} else {
    $details = $gestorBD->getUsersDetailsWaitingByLang($course_id, $lang);
}

echo json_encode($details);
