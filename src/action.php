<?php
function findAttribute( $object, $attribute ) {
	$return = false;
	if ( $object ) {
		foreach ( $object->attributes() as $a => $b ) {
			if ( $a == $attribute ) {
				$return = $b;
			}
			if ( $return ) {
				return $return;
			}
		}
	} else {
		error_log( "findAttribute: is not an object " . serialize( $object ) );
	}
}

function editXMLConfirm( $room, $user, $number, $nextSample ) {
	$xml    = simplexml_load_file( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
	$number = (int) $number;
	//No encuentra ese action
	if ( ! $xml ) {
		return false;
	}
	if ( ! $xml->actions
	     || ! $xml->actions[ $nextSample ]
	     || ! $xml->actions[ $nextSample ]->action
	     || $xml->actions[ $nextSample ]->action[ $number ] == null ) {
		if ( $number == 0 ) {
			$xml->addChild( 'actions' );
			$xml->actions[ $nextSample ]->addAttribute( 'sample', $nextSample );
		}
		$action = $xml->actions[ $nextSample ]->addChild( 'action', $nextSample );
		$action->addAttribute( 'firstUser', $user );
	} else {
		//MODIFIED - 20120927 - abertran to avoid error  Use of undefined constant firstUser - assumed 'firstUser' in
		if ( $xml->actions[ $nextSample ]->action[ $number ] != "" && $found_user = findAttribute( $xml->actions[ $nextSample ]->action[ $number ],
				'firstUser' ) ) {
			//encuentra y existe confirmacion primer usuario
			if ( $found_user != $user ) {
				$xml->actions[ $nextSample ]->action[ $number ]->addAttribute( 'secondUser', $user );
			}
			//encuentra y no existe confirmacion primer usuario
		} else {
			$xml->actions[ $nextSample ]->action[ $number ]->addAttribute( 'firstUser', $user );
		}
	}
	$xml->asXML( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
}


function preTimer( $room, $user, $nextSample ) {
	$xml    = simplexml_load_file( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
	$number = 0;
	if ( $xml->actions[ $nextSample ] == null ) {
		$xml->addChild( 'actions' );
		$xml->actions[ $nextSample ]->addAttribute( 'sample', $nextSample );
		$xml->actions[ $nextSample ]->addAttribute( 'firstUser', $user );
		$xml->actions[ $nextSample ]->addChild( 'action', '0' );
	} else {
		if ( findAttribute( $xml->actions[ $nextSample ], 'firstUser' ) ) {
			//encuentra y existe confirmacion primer usuario
			$xml->actions[ $nextSample ]->addAttribute( 'secondUser', $user );
		}
	}
	$xml->asXML( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
}


function editShowNextQuestion( $room, $user, $nextSample ) {
	$xml = simplexml_load_file( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
	if ( findAttribute( $xml->actions[ $nextSample ], 'firstUserEnd' ) ) {
		$xml->actions[ $nextSample ]->addAttribute( 'secondUserEnd', $user );
	} else {
		$xml->actions[ $nextSample ]->addAttribute( 'firstUserEnd', $user );
	}
	$xml->asXML( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
}

/*
function editShowNextQuestion( $room, $user, $nextSample ) {
	$xml = simplexml_load_file( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
	if ( $found_user = findAttribute( $xml->actions[ $nextSample ], 'firstUserEnd' ) ) {
		if ( $found_user != $user ) {
			$xml->actions[ $nextSample ]->addAttribute( 'secondUserEnd', $user );
		} else {
			error_log( "ACTION Error can not find $found_user != $user" );
		}
	} else {
		if ( $xml->actions[ $nextSample ] ) {
			$xml->actions[ $nextSample ]->addAttribute( 'firstUserEnd', $user );
		} else {
			error_log( "ACTION Error can not find $nextSample" );
		}
	}
	$xml->asXML( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
}*/

function editXMLMap( $room, $user, $number, $nextSample ) {
	$xml    = simplexml_load_file( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
	$number = (int) $number;
	if ( $xml->actions[ $nextSample ]->action[0] == null ) {
		$xml->addChild( 'actions' );
		$xml->actions[ $nextSample ]->addAttribute( 'sample', $nextSample );
	}

	$all      = $xml->xpath( "//actions[@sample='" . $nextSample . "']/action" );
	$position = count( $all ) - 1;
	$action   = $xml->actions[ $nextSample ]->action[ $position ];

	if ( $action != $number || $action == null ) {
		$action = $xml->actions[ $nextSample ]->addChild( 'action', $number );
	}
	if ( $action != "" && findAttribute( $action, firstUser ) ) {
		$action->addAttribute( 'secondUser', $user );

	} else {
		$action->addAttribute( 'firstUser', $user );
	}

	$xml->asXML( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $room . ".xml" );
}


function thruTimer( $nextSample, $numBtn ) {
	for ( $i = 0; $i < $numBtn; $i ++ ) {
		editXMLConfirm( $_GET["room"], $_GET["user"], $i, $nextSample );
	}
}

$is_final = false;
include_once( dirname( __FILE__ ) . '/classes/register_action_user.php' );
$nextSample = isset( $_GET["nextSample"] ) ? $_GET["nextSample"] - 1 : 0;
$numBtn     = isset( $_GET["numBtn"] ) ? $_GET["numBtn"] : 0;

if ( $_GET["user"] != "" && $_GET["room"] != "" ) {
	if ( is_file( PROTECTED_FOLDER . DIRECTORY_SEPARATOR . $_GET["room"] . ".xml" ) ) {
		switch ( $_GET["tipo"] ) {
			case 'confirm':
				editXMLConfirm( $_GET["room"], $_GET["user"], $_GET["number"], $nextSample, "" );
				break;

			case 'confirmTimer':
				thruTimer( $nextSample, $numBtn );
				break;

			case 'confirmPreTimer':
				preTimer( $_GET["room"], $_GET["user"], $nextSample );
				break;

			case 'SetNextQuestion':
				editShowNextQuestion( $_GET["room"], $_GET["user"], $nextSample );
				break;

			case 'map':
				editXMLMap( $_GET["room"], $_GET["user"], $_GET["number"], $nextSample );
				break;
			case 'register_action_user_next_task':
				//nothing to do because  is in previous lines
				//$is_final = false;
				//include_once(dirname(__FILE__).'/classes/register_action_user.php');

				break;
			default:
				break;
		}
	}
}

