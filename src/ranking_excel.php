<?php
require_once dirname(__FILE__) . '/classes/lang.php';
require_once dirname(__FILE__) . '/classes/constants.php';
require_once dirname(__FILE__) . '/classes/gestorBD.php';
require_once dirname(__FILE__) . '/classes/utils.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';
require_once(dirname(__FILE__) . '/classes/phpexcel-1.8.0/PHPExcel.php');



$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

if (!$user_obj || $user_obj->instructor!=1) {
//Tornem a l'index
	header('Location: index.php');
	die();
} 
$gestorBD = new GestorBD();  	
$usersRanking = $gestorBD->getUsersRanking($course_id);	
//$xls = new Excel_XML('UTF-8', false, $LanguageInstance->get('Users Assigned'));
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Tandem MOOC")
							 ->setLastModifiedBy("Tandem MOOC")
							 ->setTitle("Ranking Export")
							 ->setSubject("Ranking");
$objWorkSheet = $objPHPExcel->createSheet(0); 

$objWorkSheet->setTitle($LanguageInstance->get('Ranking for learners of English'));	            
if(!empty($usersRanking['en'])){
	$objWorkSheet->setCellValue('A1', $LanguageInstance->get('Ranking for learners of English'));
	$objWorkSheet->setCellValue('A2', $LanguageInstance->get('User'))
					->setCellValue('B2', $LanguageInstance->get('Email'))
					->setCellValue('C2', $LanguageInstance->get('Points'))
		            ->setCellValue('D2', $LanguageInstance->get('Total time'))
		            ->setCellValue('E2', $LanguageInstance->get('Number of Tandems'))
		            ->setCellValue('F2', $LanguageInstance->get('Accuracy'))
		            ->setCellValue('G2', $LanguageInstance->get('Fluency'))
		            ->setCellValue('H2', $LanguageInstance->get('Overall Grade'));
		$cont = 3;   
	$objWorkSheet->getStyle('A1')->getFont()->setSize(15);
	$objWorkSheet->getStyle('A1')->getFont()->setBold(true);
    $objWorkSheet->getStyle('A2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('B2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('C2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('D2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('E2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('F2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('G2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('H2')->getFont()->setBold(true);

	  	foreach($usersRanking['en'] as $user_id => $f){
		  		
	  		$obj = secondsToTime($f['total_time']);$time = '';
            if ($obj['h']>0) {
                $time .= ($obj['h']<10?'0':'').$obj['h'].':';
            }
            $time .= ($obj['m']<10?'0':'').$obj['m'].':';
            $time .= ($obj['s']<10?'0':'').$obj['s'];
	 		  	
			$objWorkSheet->setCellValue('A'.$cont, (isset($f['user'])?$f['user']:''))
							->setCellValue('B'.$cont, $gestorBD->getUserEmail($user_id))
							->setCellValue('C'.$cont, (isset($f['points'])?$f['points']:''))
				            ->setCellValue('D'.$cont, $time)
				            ->setCellValue('E'.$cont, intval($f['number_of_tandems']))
				            ->setCellValue('F'.$cont, $f['accuracy'])
				            ->setCellValue('G'.$cont, $f['fluency'])
				            ->setCellValue('H'.$cont, getSkillsLevel(getOverallAsIdentifier($f['overall_grade']), $LanguageInstance));
			  	$cont++;			
	  	}
	  }

$objWorkSheet = $objPHPExcel->createSheet(1); 

$objWorkSheet->setTitle($LanguageInstance->get('Ranking for learners of Spanish'));	            
if(!empty($usersRanking['es'])){
	$objWorkSheet->setCellValue('A1', $LanguageInstance->get('Ranking for learners of Spanish'));
	$objWorkSheet->setCellValue('A2', $LanguageInstance->get('User'))
					->setCellValue('B2', $LanguageInstance->get('Email'))
					->setCellValue('C2', $LanguageInstance->get('Points'))
		            ->setCellValue('D2', $LanguageInstance->get('Total time'))
		            ->setCellValue('E2', $LanguageInstance->get('Number of Tandems'))
		            ->setCellValue('F2', $LanguageInstance->get('Accuracy'))
		            ->setCellValue('G2', $LanguageInstance->get('Fluency'))
		            ->setCellValue('H2', $LanguageInstance->get('Overall Grade'));
	$objWorkSheet->getStyle('A1')->getFont()->setSize(15);
	$objWorkSheet->getStyle('A1')->getFont()->setBold(true);
    $objWorkSheet->getStyle('A2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('B2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('C2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('D2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('E2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('F2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('G2')->getFont()->setBold(true);
	$objWorkSheet->getStyle('H2')->getFont()->setBold(true);
		$cont = 3;            
	  	foreach($usersRanking['es'] as $user_id => $f){
		  		
	  		$obj = secondsToTime($f['total_time']);$time = '';
            if ($obj['h']>0) {
                $time .= ($obj['h']<10?'0':'').$obj['h'].':';
            }
            $time .= ($obj['m']<10?'0':'').$obj['m'].':';
            $time .= ($obj['s']<10?'0':'').$obj['s'];
	 		  	
			$objWorkSheet->setCellValue('A'.$cont, (isset($f['user'])?$f['user']:''))
							->setCellValue('B'.$cont, $gestorBD->getUserEmail($user_id))
							->setCellValue('C'.$cont, (isset($f['points'])?$f['points']:''))
				            ->setCellValue('D'.$cont, $time)
				            ->setCellValue('E'.$cont, intval($f['number_of_tandems']))
				            ->setCellValue('F'.$cont, $f['accuracy'])
				            ->setCellValue('G'.$cont, $f['fluency'])
				            ->setCellValue('H'.$cont, getSkillsLevel(getOverallAsIdentifier($f['overall_grade']), $LanguageInstance));
			  	$cont++;			
	  	}
	  }	  
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="tandemMOOC_Ranking_.'.date('Ymd').'.xls"');
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