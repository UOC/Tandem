<?php
require_once dirname(__FILE__) . '/../classes/lang.php';

require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../IMSBasicLTI/uoc-blti/lti_utils.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

$gestorBD = new GestorBD();
if (empty($user_obj) || $user_obj->instructor != 1) {
    header('Location: ../index.php');
    die();
}

$exerciseId = isset($_POST['exerciseId'])?$_POST['exerciseId']:0;
$taskId = isset($_POST['taskId'])?$_POST['taskId']:0;
$unlink = isset($_POST['unlink'])?$_POST['unlink']:0;

$result = $gestorBD->linkTask($exerciseId, $taskId, $unlink);

if ($result['success']) {
    $course_folder = $_SESSION[TANDEM_COURSE_FOLDER];
    generate_image_from_repository($exerciseId, $gestorBD, $course_id, $course_folder);
}

echo json_encode($result);