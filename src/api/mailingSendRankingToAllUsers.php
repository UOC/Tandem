<?php

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../classes/mailingClient.php';
require_once __DIR__ . '/../classes/mailingQueue.php';
require_once __DIR__ . '/../classes/IntegrationTandemBLTI.php';

$user = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$courseId = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
$disableProfileForm = isset($_SESSION[DISABLE_PROFILE_FORM]) ? $_SESSION[DISABLE_PROFILE_FORM] : false;
$feedbackSelfreflectionForm = isset($_SESSION[FEEDBACK_SELFREFLECTION_FORM]) ? $_SESSION[FEEDBACK_SELFREFLECTION_FORM] : false;
$showRanking = defined('SHOW_RANKING') && SHOW_RANKING == 1 && !$_SESSION[USE_WAITING_ROOM_NO_TEAMS];
$finishedTandem = $user->instructor && $user->instructor == 1 && !empty($_POST['finishedTandem']) ? $_POST['finishedTandem'] : -1;
$showFeedback = !empty($_POST['showFeedback']) ? $_POST['showFeedback'] : -1;
$useWaitingRoomNoTeams = $_SESSION[USE_WAITING_ROOM_NO_TEAMS];

$sendMailsOnlyToStudents = false; // If true, no emails will be sent to instructors.
$useMailingQueue = true; // If true the emails will be added to a mailing queue instead of being sent immediately.

if (!$user || !$courseId || empty($user->instructor)) {
    header('Location: index.php');
    exit();
}

$return = new stdclass();
$return->result = 'error';
$return->errors = array();
$return->warnings = array();

$gestorBD = new GestorBD();
$students = $gestorBD->getAllUsers($courseId);
if (empty($students)) {
    header('Content-Type: application/json');
    $return->result = 'ok';
    $return->warnings[] = 'No users found within the course ' . $courseId;
    echo json_encode($return);
    exit();
}

$mailing = null;
$mailingQueue = null;
if ($useMailingQueue) {
    $mailingQueue = new MailingQueue($gestorBD);
} else {
    $mailing = new MailingClient(null, null, count($students) > 1);
}

$subject = 'Your progress in TandemMOOC';

foreach ($students as $key => $student) {
    $studentData = $gestorBD->getUserData($student['id']);
    if (empty($studentData)) {
        $return->warnings[] = 'No user data found for user ' . $student['id'];
        continue;
    }

    if ($sendMailsOnlyToStudents) {
        $userRole = $gestorBD->obte_rol($courseId, $student['id']);
        if (1 === (int) $userRole['is_instructor']) {
            continue;
        }
    }

    if ($disableProfileForm && $showRanking) {
        $feedbacks = $gestorBD->getAllUserFeedbacks($student['id'], $courseId, $showFeedback, $finishedTandem, '', '', $useWaitingRoomNoTeams);
        $data = getRankingAndProgressData($gestorBD, $LanguageInstance, $student, $feedbackSelfreflectionForm, $courseId, $feedbacks);
    } else if (!$disableProfileForm && ($newProfileForm = $gestorBD->getUserPortfolioProfile('new', $student['id'])) && !empty($newProfileForm)) {
        $data = getNewProfileData($LanguageInstance, $newProfileForm);
    } else if (!$disableProfileForm && ($firstProfileForm = $gestorBD->getUserPortfolioProfile('first', $student['id'])) && !empty($firstProfileForm)) {
        $data = getFirstProfileData($LanguageInstance, $firstProfileForm);
    } else {
        $return->warnings[] = 'Unable to fetch feedback information for user ' . $student['id'];
        continue;
    }

    $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    $body .= '<html xmlns="http://www.w3.org/1999/xhtml">';
    $body .= '<head>';
    $body .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    $body .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
    $body .= '<title>' . $subject . '</title>';
    $body .= '</head>';
    $body .= '<body>';
    $body .= '<p>Hello ' . $studentData['firstname'] . ', </p>';
    $body .= '<p>This is your progression in TandemMOOC:</p>';
    $body .= '<table>';
    foreach ($data as $row) {
        $body .= '<tr><td>' . $row['label'] . '</td><td>' . $row['value'] . '</td></tr>';
    }
    $body .= '</table>';
    $body .= MailingClient::addTandemHtmlFooter();
    $body .= '</body>';
    $body .= '</html>';

    if ($useMailingQueue) {
        if (!$mailingQueue->addMailToQueue($studentData['email'], $studentData['fullname'], $subject, $body)) {
            $return->errors[] = 'Error adding email to queue for recipient ' . $studentData['fullname'] . ' <' . $studentData['email'] . '>';
        }
    } else {
        if (!$mailing->sendEmail($studentData['email'], $studentData['fullname'], $subject, $body)) {
            $return->errors[] = 'Error sending email to recipient ' . $studentData['fullname'] . ' <' . $studentData['email'] . '>';
        }
    }
}

if (empty($return->errors)) {
    $return->result = 'ok';
}

header('Content-Type: application/json');
echo json_encode($return);
