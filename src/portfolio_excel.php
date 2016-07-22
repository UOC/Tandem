<?php
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';
require_once(dirname(__FILE__) . '/classes/phpexcel-1.8.0/PHPExcel.php');

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;
//$portfolio = isset($_SESSION[PORTFOLIO]) ? $_SESSION[PORTFOLIO] : false;
//si no existeix objecte usuari o no existeix curs redireccionem cap a l'index....preguntar Antoni cap a on redirigir...

$gestorBD = new GestorBD();
if (!isset($user_obj) || !isset($course_id) || !$user_obj->instructor) {
	//Tornem a l'index
	header ('Location: index.php');
	die();
} else {

	$dateStart = !empty($_POST['dateStart']) ? $_POST['dateStart'] : '';
	$dateEnd = !empty($_POST['dateEnd']) ? $_POST['dateEnd'] : '';


	$finishedTandem = -1;
	if (!empty($_POST['finishedTandem'])){
		$finishedTandem = $_POST['finishedTandem'];
	}

	$showFeedback = -1;
	if (!empty($_POST['showFeedback'])){
		$showFeedback = $_POST['showFeedback'];
	}
	//lets see if we have a cookie for the selected user
	if(!empty($_COOKIE['selecteduser'])){
		$selectedUser = $_COOKIE['selecteduser'];
	}
	if(!empty($_POST['selectUser'])){
			$selectedUser = (int)$_POST['selectUser'];
	}
	$feedbacks = array();
	//the instructor wants to view some userfeedback
	if(!empty($selectedUser)){
		$feedbacks = $gestorBD->getAllUserFeedbacks($selectedUser,$course_id, $showFeedback, $finishedTandem, $dateStart, $dateEnd, $_SESSION[USE_WAITING_ROOM_NO_TEAMS]);
	}

	//$xls = new Excel_XML('UTF-8', false, $LanguageInstance->get('Users Assigned'));
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Tandem MOOC")
								 ->setLastModifiedBy("Tandem MOOC")
								 ->setTitle("Portfolio Export")
								 ->setSubject("Portfolio");
	$objWorkSheet = $objPHPExcel->createSheet(0);

        if ($_SESSION[USE_WAITING_ROOM_NO_TEAMS]){

            $objWorkSheet->setCellValue('A1', $LanguageInstance->get('Portfolio'));
            $objWorkSheet->setCellValue('A2', $LanguageInstance->get('Name'))
                        ->setCellValue('B2', $LanguageInstance->get('your_partners_name'))
                        ->setCellValue('C2', $LanguageInstance->get('Language'))
                        ->setCellValue('D2', $LanguageInstance->get('Grammatical Resource'))
                        ->setCellValue('E2', $LanguageInstance->get('Lexical Resource'))
                        ->setCellValue('F2', $LanguageInstance->get('Discourse Mangement'))
                        ->setCellValue('G2', $LanguageInstance->get('Pronunciation'))
                        ->setCellValue('H2', $LanguageInstance->get('Interactive Communication'))
                        ->setCellValue('I2', $LanguageInstance->get('exercise'))
                        ->setCellValue('J2', $LanguageInstance->get('Created'))
                        ->setCellValue('K2', $LanguageInstance->get('Total Duration'))
                        ->setCellValue('L2', $LanguageInstance->get('Duration per task'))
                        ->setCellValue('M2', $LanguageInstance->get('Partner\'s rate'))
                        ->setCellValue('N2', $LanguageInstance->get('Comments'));

        }else{

            $objWorkSheet->setCellValue('A1', $LanguageInstance->get('Portfolio'));
            $objWorkSheet->setCellValue('A2', $LanguageInstance->get('Name'))
                        ->setCellValue('B2', $LanguageInstance->get('your_partners_name'))
                        ->setCellValue('C2', $LanguageInstance->get('Language'))
                        ->setCellValue('D2', $LanguageInstance->get('Overall rating'))
                        ->setCellValue('E2', $LanguageInstance->get('Fluency'))
                        ->setCellValue('F2', $LanguageInstance->get('Accuracy'))
                        ->setCellValue('G2', $LanguageInstance->get('exercise'))
                        ->setCellValue('H2', $LanguageInstance->get('Created'))
                        ->setCellValue('I2', $LanguageInstance->get('Total Duration'))
                        ->setCellValue('J2', $LanguageInstance->get('Duration per task'))
                        ->setCellValue('K2', $LanguageInstance->get('Pronunciation'))
                        ->setCellValue('L2', $LanguageInstance->get('Vocabulary'))
                        ->setCellValue('M2', $LanguageInstance->get('Grammar'))
                        ->setCellValue('N2', $LanguageInstance->get('Other Observations'))
                        ->setCellValue('O2', $LanguageInstance->get('Partner\'s rate'))
                        ->setCellValue('P2', $LanguageInstance->get('Comments'));
        }
	//$objWorkSheet->getStyle('A1')->getFont()->setName('Candara');
	$objWorkSheet->getStyle('A1')->getFont()->setSize(15);
	$objWorkSheet->getStyle('A1')->getFont()->setBold(true);
	/*$objWorkSheet->getColumnDimension('A')->setWidth(30);
	$objWorkSheet->getColumnDimension('B')->setWidth(30);
	$objWorkSheet->getColumnDimension('C')->setWidth(30);
	$objWorkSheet->getColumnDimension('C')->setWidth(30);
*/
	//$objWorkSheet->getStyle('A1')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
    $objWorkSheet->getStyle('A2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('B2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('C2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('D2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('E2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('F2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('G2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('H2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('I2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('J2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('K2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('L2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('M2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('N2')->getFont()->setBold(true);
        if (!$_SESSION[USE_WAITING_ROOM_NO_TEAMS]){
            $objWorkSheet->getStyle('O2')->getFont()->setBold(true);
            $objWorkSheet->getStyle('P2')->getFont()->setBold(true);
        }
	$row = 3;
  	foreach($feedbacks as $f){
	  	//we have all the tasks total_time in an array, but we need the T1=00:00 format.
	  	$tt = array();
	  	foreach($f['total_time_tasks'] as $key => $val){
	  		$tt[] = "T".++$key." = ".$val;
	  	}
  		$feedbackRating = $gestorBD->getPartnerFeedbackRatingDetails($f['id'], $f['id_partner'], $f['id_tandem']);
                if ($_SESSION[USE_WAITING_ROOM_NO_TEAMS]){
                    $objWorkSheet->setCellValue('A'.$row, $f['fullname'])
                        ->setCellValue('B'.$row, $gestorBD->getPartnerName($f['id']))
                        ->setCellValue('C'.$row, $f['language'])
                        ->setCellValue('D'.$row, $f['grammaticalresource'])
                        ->setCellValue('E'.$row, $f['lexicalresource'])
                        ->setCellValue('F'.$row, $f['discoursemangement'])
                        ->setCellValue('G'.$row, $f['pronunciation'])
                        ->setCellValue('H'.$row, $f['interactivecommunication'])
                        ->setCellValue('I'.$row, $f['exercise'])
                        ->setCellValue('J'.$row, $f['created'])
                        ->setCellValue('K'.$row, $f['total_time'])
                        ->setCellValue('L'.$row, implode(" - ",$tt))
                        ->setCellValue('M'.$row, (!empty($feedbackRating->rating_partner_feedback_form->partner_rate))?$feedbackRating->rating_partner_feedback_form->partner_rate:''  )
                        ->setCellValue('N'.$row, (!empty($feedbackRating->rating_partner_feedback_form->partner_comment))?$feedbackRating->rating_partner_feedback_form->partner_comment:''  )
                        ;

                }else{
                    $objWorkSheet->setCellValue('A'.$row, $f['fullname'])
                        ->setCellValue('B'.$row, $gestorBD->getPartnerName($f['id']))
                        ->setCellValue('C'.$row, $f['language'])
                        ->setCellValue('D'.$row, getSkillsLevel($f['overall_grade'], $LanguageInstance))
                        ->setCellValue('E'.$row, $f['fluency'])
                        ->setCellValue('F'.$row, $f['accuracy'])
                        ->setCellValue('G'.$row, $f['exercise'])
                        ->setCellValue('H'.$row, $f['created'])
                        ->setCellValue('I'.$row, $f['total_time'])
                        ->setCellValue('J'.$row, implode(" - ",$tt))
                        ->setCellValue('K'.$row, $f['pronunciation'])
                        ->setCellValue('L'.$row, $f['vocabulary'])
                        ->setCellValue('M'.$row, $f['grammar'])
                        ->setCellValue('N'.$row, $f['other_observations'])
                        ->setCellValue('O'.$row, (!empty($feedbackRating->rating_partner_feedback_form->partner_rate))?$feedbackRating->rating_partner_feedback_form->partner_rate:''  )
                        ->setCellValue('P'.$row, (!empty($feedbackRating->rating_partner_feedback_form->partner_comment))?$feedbackRating->rating_partner_feedback_form->partner_comment:''  )
                        ;
                }
        $row++;
	}

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="tandemMOOC.'.date('Ymd').'.xls"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
}
