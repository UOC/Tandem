<?php

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../classes/moodleServicesConsumer.php';
require_once __DIR__ . '/../classes/phpexcel-1.8.0/PHPExcel.php';

$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
if (!$course_id) {
    die('Course not defined. Session may be expired.');
}

$gestorBD = new GestorBD();
$courseKey = $gestorBD->get_coursekey_from_courseid($course_id);
$moodleCourseId = $gestorBD->getMoodleCourseIdFromCourseKey($courseKey);
$today = date('Ymd');

// Fetch data for the sheets
$sheet0data = $gestorBD->userTandemResults($course_id);
$sheet1data = MoodleServicesConsumer::last_access_moodle($moodleCourseId);
$sheet2data = $gestorBD->resultWaitingRoom($course_id);

// Generate Excel
$objPHPExcel = new PHPExcel();
$objPHPExcel
    ->getProperties()
    ->setCreator('Tandem MOOC')
    ->setLastModifiedBy('Tandem MOOC')
    ->setTitle('speakMOOC ' . date('Y'))
    ->setSubject('Report');

/*
 * Sheet 0. Users Tandem result
 */
try {
    $objWorkSheet = $objPHPExcel->createSheet(0);
    $objWorkSheet->setTitle($LanguageInstance->get('Users Tandem Result') . ' ' . $today)
        ->setCellValue('A1', $LanguageInstance->get('Number'))
        ->setCellValue('B1', $LanguageInstance->get('Fullname'))
        ->setCellValue('C1', $LanguageInstance->get('Email'))
        ->setCellValue('D1', $LanguageInstance->get('TandemType'))
        ->setCellValue('E1', $LanguageInstance->get('TandemResult'));
    if (!empty($sheet0data)) {
        foreach ($sheet0data as $key => $data) {
            $row = $key + 2;
            $objWorkSheet
                ->setCellValue('A' . $row, $data['Number'])
                ->setCellValue('B' . $row, $data['Fullname'])
                ->setCellValue('C' . $row, $data['Email'])
                ->setCellValue('D' . $row, $data['TandemType'])
                ->setCellValue('E' . $row, $data['TandemResult']);
        }
    }
} catch (PHPExcel_Exception $e) {
    die($e->getMessage());
}

/*
 * Sheet 1. Last Access Moodle
 */

try {
    $objWorkSheet = $objPHPExcel->createSheet(1);
    $objWorkSheet->setTitle($LanguageInstance->get('Last Access Moodle') . ' ' . $today)
        ->setCellValue('A1', $LanguageInstance->get('last_access'))
        ->setCellValue('B1', $LanguageInstance->get('firstname'))
        ->setCellValue('C1', $LanguageInstance->get('lastname'))
        ->setCellValue('D1', $LanguageInstance->get('email'));
    if (!empty($sheet1data)) {
        foreach ($sheet1data as $key => $data) {
            $row = $key + 2;
            $objWorkSheet
                ->setCellValue('A' . $row, $data['last_access'])
                ->setCellValue('B' . $row, $data['firstname'])
                ->setCellValue('C' . $row, $data['lastname'])
                ->setCellValue('D' . $row, $data['email']);
        }
    }
} catch (PHPExcel_Exception $e) {
    die($e->getMessage());
}

/*
 * Sheet 2.
 */

try {
    $objWorkSheet = $objPHPExcel->createSheet(2);
    $objWorkSheet->setTitle($LanguageInstance->get('Result Waiting Room') . ' ' . $today)
        ->setCellValue('A1', $LanguageInstance->get('email'))
        ->setCellValue('B1', $LanguageInstance->get('fullname'))
        ->setCellValue('C1', $LanguageInstance->get('Result'))
        ->setCellValue('D1', $LanguageInstance->get('day'))
        ->setCellValue('E1', $LanguageInstance->get('month'))
        ->setCellValue('F1', $LanguageInstance->get('year'));
    if (!empty($sheet2data)) {
        foreach ($sheet2data as $key => $data) {
            $row = $key + 2;
            $objWorkSheet
                ->setCellValue('A' . $row, $data['email'])
                ->setCellValue('B' . $row, $data['fullname'])
                ->setCellValue('C' . $row, $data['Result'])
                ->setCellValue('D' . $row, $data['day'])
                ->setCellValue('E' . $row, $data['month'])
                ->setCellValue('F' . $row, $data['year']);
        }
    }
} catch (PHPExcel_Exception $e) {
    die($e->getMessage());
}

/*
 * Prepare excel to be downloaded
 */

try {
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="tandemMOOC_Report_.' . date('YmdHis') . '.xls"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');
    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
} catch (PHPExcel_Exception $e) {
    die($e->getMessage());
}
