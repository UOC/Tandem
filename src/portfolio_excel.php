<?php
require_once dirname( __FILE__ ) . '/classes/lang.php';
require_once dirname( __FILE__ ) . '/classes/constants.php';
require_once dirname( __FILE__ ) . '/classes/gestorBD.php';
require_once 'IMSBasicLTI/uoc-blti/lti_utils.php';
require_once( dirname( __FILE__ ) . '/classes/phpexcel-1.8.0/PHPExcel.php' );

$user_obj  = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$course_id = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : false;
//$portfolio = isset($_SESSION[PORTFOLIO]) ? $_SESSION[PORTFOLIO] : false;
//si no existeix objecte usuari o no existeix curs redireccionem cap a l'index....preguntar Antoni cap a on redirigir...

$gestorBD = new GestorBD();
if ( ! isset( $user_obj ) || ! isset( $course_id ) || ! $user_obj->instructor ) {
	//Tornem a l'index
	header( 'Location: index.php' );
	die();
} else {

	include_once __DIR__ . '/tandemLog.php';
	$dateStart = ! empty( $_POST['dateStart'] ) ? $_POST['dateStart'] : '';
	$dateEnd   = ! empty( $_POST['dateEnd'] ) ? $_POST['dateEnd'] : '';


	$finishedTandem = - 1;
	if ( ! empty( $_POST['finishedTandem'] ) ) {
		$finishedTandem = $_POST['finishedTandem'];
	}

	$showFeedback = - 1;
	if ( ! empty( $_POST['showFeedback'] ) ) {
		$showFeedback = $_POST['showFeedback'];
	}
	//lets see if we have a cookie for the selected user
	if ( ! empty( $_COOKIE['selecteduser'] ) ) {
		$selectedUser = $_COOKIE['selecteduser'];
	}
	if ( ! empty( $_POST['selectUser'] ) ) {
		$selectedUser = (int) $_POST['selectUser'];
	}
	$feedbacks = array();
	//the instructor wants to view some userfeedback
	if ( ! empty( $selectedUser ) ) {
		$feedbacks = $gestorBD->getAllUserFeedbacks( $selectedUser, $course_id, $showFeedback, $finishedTandem,
			$dateStart, $dateEnd, $_SESSION[ USE_WAITING_ROOM_NO_TEAMS ] );
	}

	//$xls = new Excel_XML('UTF-8', false, $LanguageInstance->get('Users Assigned'));
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator( "Tandem MOOC" )
	            ->setLastModifiedBy( "Tandem MOOC" )
	            ->setTitle( "Portfolio Export" )
	            ->setSubject( "Portfolio" );
	$objWorkSheet = $objPHPExcel->createSheet( 0 );


	if ( $_SESSION[ USE_WAITING_ROOM_NO_TEAMS ] ) {

		$objWorkSheet->setCellValue( 'A1', $LanguageInstance->get( 'Portfolio' ) );
		$objWorkSheet->setCellValue( 'A2', $LanguageInstance->get( 'Name' ) )
		             ->setCellValue( 'B2', $LanguageInstance->get( 'your_partners_name' ) )
		             ->setCellValue( 'C2', $LanguageInstance->get( 'Language' ) )
			/*->setCellValue('D2', $LanguageInstance->get('Grammatical Resource'))
			->setCellValue('E2', $LanguageInstance->get('Lexical Resource'))
			->setCellValue('F2', $LanguageInstance->get('Discourse Mangement'))
			->setCellValue('G2', $LanguageInstance->get('Pronunciation'))
			->setCellValue('H2', $LanguageInstance->get('Interactive Communication'))*/
			         ->setCellValue( 'D2', $LanguageInstance->get( 'exercise' ) )
		             ->setCellValue( 'E2', $LanguageInstance->get( 'Created' ) )
		             ->setCellValue( 'F2', $LanguageInstance->get( 'Total Duration' ) )
		             ->setCellValue( 'G2', $LanguageInstance->get( 'Duration per task' ) )
		             ->setCellValue( 'H2', $LanguageInstance->get( 'Partner\'s rate' ) )
		             ->setCellValue( 'I2', $LanguageInstance->get( 'Comments' ) )
		             ->setCellValue( 'J2', $LanguageInstance->get( 'Type' ) );
		if ( $_SESSION[ USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE ] ) {
			$objWorkSheet->setCellValue( 'K2', $LanguageInstance->get( 'Pair language' ) );
		}


	} else {

		$objWorkSheet->setCellValue( 'A1', $LanguageInstance->get( 'Portfolio' ) );
		$objWorkSheet->setCellValue( 'A2', $LanguageInstance->get( 'Name' ) )
		             ->setCellValue( 'B2', $LanguageInstance->get( 'your_partners_name' ) )
		             ->setCellValue( 'C2', $LanguageInstance->get( 'Language' ) )
			/*->setCellValue('D2', $LanguageInstance->get('Overall rating'))
			->setCellValue('E2', $LanguageInstance->get('Fluency'))
			->setCellValue('F2', $LanguageInstance->get('Accuracy'))*/
			         ->setCellValue( 'D2', $LanguageInstance->get( 'exercise' ) )
		             ->setCellValue( 'E2', $LanguageInstance->get( 'Created' ) )
		             ->setCellValue( 'F2', $LanguageInstance->get( 'Total Duration' ) )
		             ->setCellValue( 'G2', $LanguageInstance->get( 'Duration per task' ) )
			/*->setCellValue('2', $LanguageInstance->get('Pronunciation'))
			->setCellValue('L2', $LanguageInstance->get('Vocabulary'))
			->setCellValue('M2', $LanguageInstance->get('Grammar'))
			->setCellValue('N2', $LanguageInstance->get('Other Observations'))*/
			         ->setCellValue( 'H2', $LanguageInstance->get( 'Partner\'s rate' ) )
		             ->setCellValue( 'I2', $LanguageInstance->get( 'Comments' ) )
		             ->setCellValue( 'J2', $LanguageInstance->get( 'Type' ) );
		if ( $_SESSION[ USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE ] ) {
			$objWorkSheet->setCellValue( 'K2', $LanguageInstance->get( 'Pair language' ) );
		}

		if ( $_SESSION[ USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE ] ) {
			$letter = 'K';
		} else {
			$letter = 'J';
		}


		$objWorkSheet->setCellValue( ++ $letter . '2', $LanguageInstance->get( 'Video' ) );
		$objWorkSheet->setCellValue( ++ $letter . '2',
			$LanguageInstance->get( '1. One or more things your partner did well.' ) );
		$objWorkSheet->setCellValue( ++ $letter . '2',
			$LanguageInstance->get( '2. Three language errors your partner made.' ) );
		$objWorkSheet->setCellValue( ++ $letter . '2', $LanguageInstance->get( '3. Other comments.' ) );
		$objWorkSheet->setCellValue( ++ $letter . '2', $LanguageInstance->get( '1. One or more things I did well.' ) );
		$objWorkSheet->setCellValue( ++ $letter . '2', $LanguageInstance->get( '2. Three language errors I made.' ) );
		$objWorkSheet->setCellValue( ++ $letter . '2', $LanguageInstance->get( '3. Other comments.' ) );

		$objWorkSheet->setCellValue( ++ $letter . '2', $LanguageInstance->get( 'Anxometers' ) );
		for ( $number = 1; $number < 8; $number ++ ) {
			$objWorkSheet->setCellValue( ++ $letter . '2', $LanguageInstance->getTag( 'Question number %d', $number ) );
		}
	}
	//$objWorkSheet->getStyle('A1')->getFont()->setName('Candara');
	$objWorkSheet->getStyle( 'A1' )->getFont()->setSize( 15 );
	$objWorkSheet->getStyle( 'A1' )->getFont()->setBold( true );
	/*$objWorkSheet->getColumnDimension('A')->setWidth(30);
	$objWorkSheet->getColumnDimension('B')->setWidth(30);
	$objWorkSheet->getColumnDimension('C')->setWidth(30);
	$objWorkSheet->getColumnDimension('C')->setWidth(30);
*/
	//$objWorkSheet->getStyle('A1')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
	$objWorkSheet->getStyle( 'A2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'B2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'C2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'D2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'E2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'F2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'G2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'H2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'I2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'J2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'K2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'L2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'M2' )->getFont()->setBold( true );
	$objWorkSheet->getStyle( 'N2' )->getFont()->setBold( true );
	if ( ! $_SESSION[ USE_WAITING_ROOM_NO_TEAMS ] ) {
		$objWorkSheet->getStyle( 'O2' )->getFont()->setBold( true );
		$objWorkSheet->getStyle( 'P2' )->getFont()->setBold( true );
	}
	$row = 3;
	foreach ( $feedbacks as $f ) {
		//we have all the tasks total_time in an array, but we need the T1=00:00 format.
		$tt = array();
		foreach ( $f['total_time_tasks'] as $key => $val ) {
			$tt[] = "T" . ++ $key . " = " . $val;
		}
		$task_valorations = $gestorBD->get_task_valoration( $f['id_tandem'], $f['id_user'] );

		$feedbackRating  = $gestorBD->getPartnerFeedbackRatingDetails( $f['id'], $f['id_partner'], $f['id_tandem'] );
		$feedbackDetails = $gestorBD->getFeedbackDetails( $f['id'] );
		if ( $_SESSION[ USE_WAITING_ROOM_NO_TEAMS ] ) {
			$objWorkSheet->setCellValue( 'A' . $row, $f['fullname'] )
			             ->setCellValue( 'B' . $row, $gestorBD->getPartnerName( $f['id'] ) )
			             ->setCellValue( 'C' . $row, $f['language'] )
				/*->setCellValue('D'.$row, $f['grammaticalresource'])
				->setCellValue('E'.$row, $f['lexicalresource'])
				->setCellValue('F'.$row, $f['discoursemangement'])
				->setCellValue('G'.$row, $f['pronunciation'])
				->setCellValue('H'.$row, $f['interactivecommunication'])*/
				         ->setCellValue( 'D' . $row, $f['exercise'] )
			             ->setCellValue( 'E' . $row, $f['created'] )
			             ->setCellValue( 'F' . $row, format_time_excel( $f['total_time'] ) )
			             ->setCellValue( 'G' . $row, implode( " - ", $tt ) )
			             ->setCellValue( 'H' . $row,
				             ( ! empty( $feedbackRating->rating_partner_feedback_form->partner_rate ) ) ? $feedbackRating->rating_partner_feedback_form->partner_rate : '' )
			             ->setCellValue( 'I' . $row,
				             ( ! empty( $feedbackRating->rating_partner_feedback_form->partner_comment ) ) ? $feedbackRating->rating_partner_feedback_form->partner_comment : '' )
			             ->setCellValue( 'J' . $row, $f['TandemResult'] );
			if ( $_SESSION[ USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE ] ) {
				$objWorkSheet->setCellValue( 'K' . $row,
					( $f['language'] == $f['other_language'] ? $LanguageInstance->get( 'Same' ) : $LanguageInstance->get( 'Distinct' ) ) );
			}

		} else {
			$objWorkSheet->setCellValue( 'A' . $row, $f['fullname'] )
			             ->setCellValue( 'B' . $row, $gestorBD->getPartnerName( $f['id'] ) )
			             ->setCellValue( 'C' . $row, $f['language'] )
				/*->setCellValue('D'.$row, getSkillsLevel($f['overall_grade'], $LanguageInstance))
				->setCellValue('E'.$row, $f['fluency'])
				->setCellValue('F'.$row, $f['accuracy'])*/
				         ->setCellValue( 'D' . $row, $f['exercise'] )
			             ->setCellValue( 'E' . $row, $f['created'] )
			             ->setCellValue( 'F' . $row, format_time_excel( $f['total_time'] ) )
			             ->setCellValue( 'G' . $row, implode( " - ", $tt ) )
				/*->setCellValue('K'.$row, $f['pronunciation'])
				->setCellValue('L'.$row, $f['vocabulary'])
				->setCellValue('M'.$row, $f['grammar'])
				->setCellValue('N'.$row, $f['other_observations'])*/
				         ->setCellValue( 'H' . $row,
					( ! empty( $feedbackRating->rating_partner_feedback_form->partner_rate ) ) ? $feedbackRating->rating_partner_feedback_form->partner_rate : '' )
			             ->setCellValue( 'I' . $row,
				             ( ! empty( $feedbackRating->rating_partner_feedback_form->partner_comment ) ) ? $feedbackRating->rating_partner_feedback_form->partner_comment : '' )
			             ->setCellValue( 'J' . $row, $f['TandemResult'] );
			if ( $_SESSION[ USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE ] ) {
				$objWorkSheet->setCellValue( 'K' . $row,
					( $f['language'] == $f['other_language'] ? $LanguageInstance->get( 'Same' ) : $LanguageInstance->get( 'Distinct' ) ) );
			}

		}
		if ( $_SESSION[ USE_FALLBACK_WAITING_ROOM_AVOID_LANGUAGE ] ) {
			$letter = 'K';
		} else {
			$letter = 'J';
		}


		$objWorkSheet->setCellValue( ++ $letter . $row, $feedbackDetails->external_video_url );

		$feedback_form = false;
		if ( $feedbackDetails->feedback_form ) { //if it is false can edit
			$feedback_form = $feedbackDetails->feedback_form;
		}
		$achievement_txt = $feedback_form ? excel_get_feedback_details( 'achievements',
			$feedback_form->selfReflection ) : '';
		$error_txt       = $feedback_form ? excel_get_feedback_details( 'errors', $feedback_form->selfReflection ) : '';
		$comments        = $feedback_form ? excel_get_feedback_details( 'comments',
			$feedback_form->selfReflection ) : '';

		$achievement_txt_peer = $feedback_form ? excel_get_feedback_details( 'achievements',
			$feedback_form->peerFeedback ) : '';
		$error_txt_peer       = $feedback_form ? excel_get_feedback_details( 'errors',
			$feedback_form->peerFeedback ) : '';
		$comments_peer        = $feedback_form ? excel_get_feedback_details( 'comments',
			$feedback_form->peerFeedback ) : '';


		$objWorkSheet->setCellValue( ++ $letter . $row, $achievement_txt_peer );
		$objWorkSheet->setCellValue( ++ $letter . $row, $error_txt_peer );
		$objWorkSheet->setCellValue( ++ $letter . $row, $comments_peer );
		$objWorkSheet->setCellValue( ++ $letter . $row, $achievement_txt );
		$objWorkSheet->setCellValue( ++ $letter . $row, $error_txt );
		$objWorkSheet->setCellValue( ++ $letter . $row, $comments );

		$objWorkSheet->setCellValue( ++ $letter . $row, '' );


		foreach ( $task_valorations as $task_valoration ) {
			$letter_temp = $letter;
			for ( $i = 0; $i < intval( $task_valoration['task_number'] ); $i ++ ) {
				++ $letter_temp;
			}
			$objWorkSheet->setCellValue( $letter_temp . $row,
				$task_valoration['task_valoration'] );
		}
		$row ++;
	}

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex( 0 );
	header( 'Content-Type: application/vnd.ms-excel' );
	header( 'Content-Disposition: attachment;filename="tandemMOOC.' . date( 'Ymd' ) . '.xls"' );
	header( 'Cache-Control: max-age=0' );
	// If you're serving to IE 9, then the following may be needed
	header( 'Cache-Control: max-age=1' );

	// If you're serving to IE over SSL, then the following may be needed
	header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); // Date in the past
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); // always modified
	header( 'Cache-Control: cache, must-revalidate' ); // HTTP/1.1
	header( 'Pragma: public' ); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel5' );
	$objWriter->save( 'php://output' );
	exit;
}

function excel_get_feedback_details( $field, $array ) {

	$details = '';
	if ( ! empty( $array[ $field ] ) ) {
		foreach ( $array[ $field ] as $item ) {
			$details .= ( strlen( $details ) == 0 ? '' : '\r\n' ) . $item;
		}
	}

	return $details;
}

function format_time_excel( $time ) {
	if ( substr_count( $time, ':' ) == 1 ) {
		$time = '00:' . $time;
	}

	return $time;
}