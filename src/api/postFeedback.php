<?php

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/utils.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';

// Session params fetching
$user                         = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;
$courseId                     = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : false;
$feedbackId                   = isset( $_SESSION[ ID_FEEDBACK ] ) ? intval( $_SESSION[ ID_FEEDBACK ], 10 ) : 0;
$feedback_selfreflection_form = isset( $_SESSION[ FEEDBACK_SELFREFLECTION_FORM ] ) ? $_SESSION[ FEEDBACK_SELFREFLECTION_FORM ] : false;
$lang                         = isset( $_SESSION[ LANG ] ) ? $_SESSION[ LANG ] : false;

// Post request params fetching
$selfWellList         = isset( $_POST['selfWellList'] ) ? array_map( 'trim', (array) $_POST['selfWellList'] ) : array();
$selfErrorsList       = isset( $_POST['selfErrorsList'] ) ? array_map( 'trim',
	(array) $_POST['selfErrorsList'] ) : array();
$selfOtherComments    = isset( $_POST['selfOtherComments'] ) ? trim( (string) $_POST['selfOtherComments'] ) : '';
$partnerWellList      = isset( $_POST['partnerWellList'] ) ? array_map( 'trim',
	(array) $_POST['partnerWellList'] ) : array();
$partnerErrorsList    = isset( $_POST['partnerErrorsList'] ) ? array_map( 'trim',
	(array) $_POST['partnerErrorsList'] ) : array();
$partnerOtherComments = isset( $_POST['partnerOtherComments'] ) ? trim( (string) $_POST['partnerOtherComments'] ) : '';

// Post params validation rules
$validationRules = array(
	'minWellItems'     => defined( 'FEEDBACK_VALIDATION_MIN_WELL_ITEMS' ) ? FEEDBACK_VALIDATION_MIN_WELL_ITEMS : 1,
	'minErrorItems'    => defined( 'FEEDBACK_VALIDATION_MIN_ERROR_ITEMS' ) ? FEEDBACK_VALIDATION_MIN_ERROR_ITEMS : 1,
	'itemsMinChars'    => defined( 'FEEDBACK_VALIDATION_MIN_CHARS' ) ? FEEDBACK_VALIDATION_MIN_CHARS : 3,
	'commentsRequired' => defined( 'FEEDBACK_VALIDATION_COMMENTS_REQUIRED' ) ? FEEDBACK_VALIDATION_COMMENTS_REQUIRED : false,
);

// Default (error) response
$return         = new stdClass();
$return->result = 'error';

/**
 * @param array $list
 * @param int $minChars
 *
 * @return bool
 */
function validateItemsMinChars( $list, $minChars ) {
	foreach ( $list as $item ) {
		if ( ! is_string( $item ) || mb_strlen( $item ) < $minChars ) {
			return false;
		}
	}

	return true;
}

// Params validation
if (
	$_SERVER['REQUEST_METHOD'] !== 'POST'
	|| empty( $user->id )
	|| 1 > (int) $user->id
	|| 1 > (int) $courseId
	|| 1 > $feedbackId
	|| ! $feedback_selfreflection_form
	|| empty( $lang )
	|| ( $validationRules['commentsRequired'] && empty( $selfOtherComments ) )
	|| ( $validationRules['commentsRequired'] && empty( $partnerOtherComments ) )
	|| ( $validationRules['minWellItems'] && count( $selfWellList ) < $validationRules['minWellItems'] )
	|| ( $validationRules['minWellItems'] && count( $partnerWellList ) < $validationRules['minWellItems'] )
	|| ( $validationRules['minErrorItems'] && count( $selfErrorsList ) < $validationRules['minErrorItems'] )
	|| ( $validationRules['minErrorItems'] && count( $partnerErrorsList ) < $validationRules['minErrorItems'] )
	|| ( $validationRules['itemsMinChars'] && ! validateItemsMinChars( $selfWellList,
			$validationRules['itemsMinChars'] ) )
	|| ( $validationRules['itemsMinChars'] && ! validateItemsMinChars( $partnerWellList,
			$validationRules['itemsMinChars'] ) )
	|| ( $validationRules['itemsMinChars'] && ! validateItemsMinChars( $selfErrorsList,
			$validationRules['itemsMinChars'] ) )
	|| ( $validationRules['itemsMinChars'] && ! validateItemsMinChars( $partnerErrorsList,
			$validationRules['itemsMinChars'] ) )
) {
	header( 'Content-Type: application/json' );
	echo json_encode( $return );
	exit();
}

try {
	$feedbackData                                 = new stdClass();
	$feedbackData->selfReflection                 = array();
	$feedbackData->selfReflection['achievements'] = $selfWellList;
	$feedbackData->selfReflection['errors']       = $selfErrorsList;
	$feedbackData->selfReflection['comments']     = $selfOtherComments;
	$feedbackData->peerFeedback                   = array();
	$feedbackData->peerFeedback['achievements']   = $partnerWellList;
	$feedbackData->peerFeedback['errors']         = $partnerErrorsList;
	$feedbackData->peerFeedback['comments']       = $partnerOtherComments;
	// Deprecated data (set them to 0 to avoid unwanted effects when calculating points with deprecated methods)
	$feedbackData->lexicalresource          = 0;
	$feedbackData->grammaticalresource      = 0;
	$feedbackData->discoursemangement       = 0;
	$feedbackData->interactivecommunication = 0;
	$feedbackData->pronunciation            = 0;
	$feedbackData->fluency                  = 0;
	$feedbackData->accuracy                 = 0;
	$feedbackData->grade                    = 0;
	$feedbackData->pronunciation            = 0;
	$feedbackData->vocabulary               = 0;
	$feedbackData->grammar                  = 0;
	$feedbackData->other_observations       = '';

	$gestorBD = new GestorBD();

	ob_start();
	if ( $gestorBD->createFeedbackTandemDetail( $feedbackId, serialize( $feedbackData ) ) ) {
		$gestorBD->updateUserRankingPoints( $user->id, $courseId );
		if ( $feedbackDetails = $gestorBD->getFeedbackDetails( $feedbackId ) ) {
			$gestorBD->updateUserRankingPoints( $feedbackDetails->id_partner, $courseId );
		}
		$return->result = 'ok';
	}
	$errorBuffer = ob_get_clean();
} catch ( Exception $e ) {
	error_log( serialize( $e ) );
	$return->result = 'error';
}

header( 'Content-Type: application/json' );
echo json_encode( $return );
