<?php

use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\DeleteRecordingsParameters;
use BigBlueButton\Parameters\EndMeetingParameters;

require_once dirname( __FILE__ ) . '/../config.inc.php';
require_once dirname( __FILE__ ) . '/../classes/gestorBD.php';
require_once dirname( __FILE__ ) . '/vendor/autoload.php';
require_once dirname( __FILE__ ) . '/TandemRecord.php';

/**
 * Class BBBIntegration
 *
 * // change it on
 * // defaultWelcomeMessage=Welcome to <b>%%CONFNAME%%</b>!<br><br>For help on using BigBlueButton see these (short) <a href="event:http://www.bigbluebutton.org/html5"><u>tutorial videos</u></a>.<br><br>To join the audio bridge click the phone button.  Use a headset to avoid causing background noise for others.
 * // defaultWelcomeMessageFooter=This server is running <a href="http://docs.bigbluebutton.org/" target="_blank"><u>BigBlueButton</u></a>.
 * // posible issues on websocket vi /etc/bigbluebutton/nginx/sip.nginx then check ip
 * Add custom css vi /usr/share/meteor/bundle/programs/web.browser/head.html
 * <link rel="stylesheet" type="text/css" href="https://tandem.speakapps.org/bbb/style.css" charset="utf-8"/>
 */
class BBBIntegration {

	const SUCCESS_STATUS = 'SUCCESS';

	private static function setEnvConfig() {
		// Should be Once you have them, create two environment variables.
		if (php_sapi_name() !== 'cli') {
// For Apache2 you can use the SetEnv directive or the fastcgi_param for nginx.
// For Apache2, we advise putting the variables in the /etc/apache2/envvars to keep themaway from your source code repository.
			apache_setenv( 'BBB_SECRET', BBB_SECRET );
			apache_setenv( 'BBB_SERVER_BASE_URL', BBB_SERVER_BASE_URL );
		}
	}

	/**
	 * @param $meetingId
	 * @param $meetingName
	 * @param $userId
	 * @param $fullname
	 * @param $baseUrl
	 * @param $welcomeMessage
	 * @param int $maxParticipants
	 * @param int $duration_minutes Max duration in minutes
	 * @param int $bbb_client_title Specifies a string to set as the HTML5 client title
	 *
	 * @return string
	 */
	public static function generateBBBURL(
		$meetingId,
		$meetingName,
		$userId,
		$fullname,
		$baseUrl,
		$welcomeMessage = "Welcome to Tandem Videochat!",
		$maxParticipants = 0,
		$duration_minutes = 180,
        $bbb_client_title = "Tandem"
	) {
		self::setEnvConfig();

		$passwords = array(
			'moderator' => 'mPass',
			'attendee'  => 'aPass'
		);

// Init BigBlueButton API
		$bbb = new BigBlueButton();

// Create the meeting
		$createParams = new CreateMeetingParameters( $meetingId, $meetingName );
		$createParams = $createParams->setModeratorPassword( $passwords['moderator'] )
		                             ->setAttendeePassword( $passwords['attendee'] )
		                             ->setAutoStartRecording( true )
		                             ->setRecord( true )
			//  ->setEndCallbackUrl( $baseUrl . "/bbb/endCallback.php?id=" . $meetingId )
			                         ->setLogoutUrl( $baseUrl . '/bbb/end.php?action=logout&id=' . $meetingId )
			// If the presentation is invalid doesn't show it!
			// ->addPresentation( $baseUrl . '/bbb/presentation.pdf', null, 'presentation.pdf' )
			                        ->addPresentation( $baseUrl . '/bbb/invalidpresentation.pdf', null,
				'invalidpresentation.pdf' )
		                             ->setLogo( $baseUrl . "/img/logo_Tandem.png" )
		                             ->setWelcomeMessage( $welcomeMessage )
		                             ->setAllowStartStopRecording( false )
									 ->setDuration( $duration_minutes)
		                             ->setFreeJoin( false );

		if ( $maxParticipants > 0 ) {
			$createParams->setMaxParticipants( $maxParticipants );
		}

// Get info to check if session exist or not
		$response = $bbb->getMeetingInfo( $createParams );

		if ( $response->getReturnCode() == 'FAILED' ) {
			// meeting not found or already closed
			$message_key = $response->getMessageKey();
			if ( $message_key === 'notFound' ) {
				$bbb->createMeeting( $createParams );
			} else {
				die( "This meeting is ended!" );
			}
		}

// Send a join meeting request
		$joinParams = new JoinMeetingParameters( $meetingId, $fullname,
			$passwords['attendee'] );

		$joinParams->setUserId( $userId );

		$joinParams->setJoinViaHtml5( true );

		/* settings.xml review params
		https://github.com/bigbluebutton/bigbluebutton/blob/master/bigbluebutton-html5/private/config/settings.yml*/

		/* Application parameters https://docs.bigbluebutton.org/2.2/customize.html#application-parameters */

		// If set to true, the client will display the ask for feedback screen on logout
		$joinParams->addUserData('bbb_ask_for_feedback_on_logout', 'false');
		// If set to true, the client will start the process of joining the audio bridge automatically upon loading the client
		$joinParams->addUserData('bbb_auto_join_audio', 'true');
		// Specifies a string to set as the HTML5 client title
		$joinParams->addUserData('bbb_client_title', $bbb_client_title);
        // If set to true, the user will be not be able to join with a microphone as an option
		$joinParams->addUserData('bbb_force_listen_only', 'false');
		// If set to false, the user will not be able to join the audio part of the meeting without a microphone (disables listen-only mode)
		$joinParams->addUserData('bbb_listen_only_mode', 'true');
		// If set to true, the user will not see the “echo test” prompt on login
		$joinParams->addUserData('bbb_skip_check_audio', 'false');


		/* Branding parameters https://docs.bigbluebutton.org/2.2/customize.html#branding-parameters */

        // If set to true, the client will display the branding area in the upper left hand corner
        $joinParams->addUserData('bbb_display_branding_area', 'true');

        /* Shortcut parameters https://docs.bigbluebutton.org/2.2/customize.html#shortcut-parameters */
        // The value passed has to be URL encoded. For example if you would like to disable shortcuts, pass %5B%5D which is the encoded version of the empty array [] see settings.yml
        //  disabled L => Leave audio
        $joinParams->addUserData('bbb_shortcuts', '%5BL%5D');

        /* kurento parameters https://docs.bigbluebutton.org/2.2/customize.html#kurento-parameters */
        // If set to true, the client will start the process of sharing webcam (if any) automatically upon loading the client
        $joinParams->addUserData('bbb_auto_share_webcam', 'true');
        // Specifies a preferred camera profile to use out of those defined in the settings.yml
        // $joinParams->addUserData('bbb_preferred_camera_profile', '');
        // 	If set to false, the client will display the screen sharing button if they are the current presenter
        $joinParams->addUserData('bbb_enable_screen_sharing', 'false');
        // If set to false, the client will display the webcam sharing button (in effect disabling/enabling webcams)
        // $joinParams->addUserData('bbb_enable_video', 'false');
        // If set to true, the client will display connection statistics for the user
        // $joinParams->addUserData('bbb_enable_video_stats', 'false');
        // If set to true, the client will not see a preview of their webcam before sharing it
        $joinParams->addUserData('bbb_skip_video_preview', 'false');
        // If set to true, the client will see a mirrored version of their webcam. Doesn’t affect the incoming video stream for other users.
        $joinParams->addUserData('bbb_mirror_own_webcam', 'false');

        /* Presentation parameters https://docs.bigbluebutton.org/2.2/customize.html#presentation-parameters */
        // If set to true, only the pen tool will be available to non-participants when multi-user whiteboard is enabled
        // $joinParams->addUserData('bbb_multi_user_pen_only', 'false');
        // Pass in an array of permitted tools from settings.yml
        // $joinParams->addUserData('bbb_presenter_tools', 'false');
        // Pass in an array of permitted tools for non-presenters from settings.yml
        // $joinParams->addUserData('bbb_multi_user_tools', 'false');

        /* Styling parameters https://docs.bigbluebutton.org/2.2/customize.html#themeing--styling-parameters  *(
        // This parameter acts the same way as userdata-bbb_custom_style except that the CSS content comes from a hosted file*/
		$joinParams->addUserData('bbb_custom_style_url', 'https://tandem.speakapps.org/bbb/style.css?v=2');
        // $joinParams->addUserData('bbb_custom_style', '%3Aroot%7B--loader-bg%3A%23000%3B%7D.overlay--1aTlbi%7Bbackground-color%3A%23000!important%3B%7Dbody%7Bbackground-color%3A%23000!important%3B%7D');

        /* Layout parameters https://docs.bigbluebutton.org/2.2/customize.html#layout-parameters */
        // If set to true, the presentation area will be minimized when a user joins a meeting.
        // $joinParams->addUserData('bbb_auto_swap_layout', 'true');
        // If set to true, the presentation area will be minimized until opened
        // $joinParams->addUserData('bbb_hide_presentation', 'true');
        // If set to false, the participants panel will not be displayed until opened.
		// $joinParams->addUserData('bbb_show_participants_on_login', 'false');
		// If set to false, the chat panel will not be visible on page load until opened. Not the same as disabling chat.
		// $joinParams->addUserData('bbb_show_public_chat_on_login', 'true');

// Ask for immediate redirection
		$joinParams->setRedirect( true );

		return $bbb->getJoinMeetingURL( $joinParams );

	}

	/**
	 * This method will check several options that Videochat check before, we will move from several api calls to one
	 * url_notify_started_recording=%URL_TANDEM%/api/startSession.php?id=%ID_TANDEM%
	 * url_notify_session_available=%URL_TANDEM%/api/sessionAvailable.php?id=%ID_TANDEM%
	 *
	 *
	 * url_notify_user_accepted_connection=%URL_TANDEM%/api/userAcceptVideo.php?id=%ID_TANDEM%&user_id=%ID_USER%
	 *
	 * @param $tandem_id
	 * @param $gestorBD
	 * @param int $min_users
	 * @param $requires_webcam_to_start
	 *
	 * @return \BigBlueButton\Responses\GetMeetingInfoResponse
	 */
	public static function checkSessionInfo( $tandem_id, $gestorBD, $min_users = 2, $requires_webcam_to_start = true ) {
		self::setEnvConfig();
		$bbb          = new BigBlueButton();
		$createParams = new GetMeetingInfoParameters( $tandem_id, '' );
		$response     = $bbb->getMeetingInfo( $createParams );

		if ( $response->getReturnCode() === self::SUCCESS_STATUS ) {
			$tandem = $gestorBD ? $gestorBD->obteTandem( $tandem_id ) : false;

			if ( $tandem != null || $gestorBD != false ) {
				$attendees            = $response->getMeeting()->getAttendees();
				$total_users          = 0;
				$total_users_accepted = 0;
				foreach ( $attendees as $attendee ) {
					$user_id     = $attendee->getUserId();
					$joinedVoice = $attendee->hasJoinedVoice();
					$joinVideo   = $attendee->hasVideo();
					if ($gestorBD) {
						$gestorBD->updateAcceptedVideochat( $tandem_id, $user_id, $joinedVoice, $joinVideo);
					}
					if ( $gestorBD && (!$requires_webcam_to_start || $joinVideo) && $joinedVoice ) {
						if ( ! $gestorBD->userAcceptedVideoConnection( $tandem_id, $user_id ) ) {
							$gestorBD->insertUserAcceptedConnection( $tandem_id, $user_id );
						}
						$total_users_accepted ++;
					}
					$total_users ++;
				}
				if ( $gestorBD && $total_users >= $min_users && $total_users_accepted === $total_users ) {

					//Save the return id if it is set, if not get as id tandem
					$id_external_tool     = isset( $_GET['return_id'] ) ? $_GET['return_id'] : $tandem_id;
					$end_external_service = isset( $_GET['end_external_service'] ) ? $_GET['end_external_service'] : '';

					$gestorBD->updateExternalToolFeedbackTandemByTandemId( $tandem_id, $id_external_tool,
						$end_external_service );

					//start Tandem
					//we will only update the id_external-tool if its 0 or null
					$ccc = $gestorBD->checkExternalToolField( $tandem_id );
					if ( empty( $ccc ) ) {
						$gestorBD->setCreatedTandemToNow( $tandem_id );
					}

					$gestorBD->startTandemSession( $tandem_id );


				}
			}
		}

		return $response;
	}

	/**
	 * @param $tandem_id
	 * @param $debug
	 *
	 * @return \BigBlueButton\Responses\GetRecordingsResponse
	 */
	public static function getRecordings( $tandem_id, $debug = false ) {
		self::setEnvConfig();
		$bbb             = new BigBlueButton();
		$recordingParams = new \BigBlueButton\Parameters\GetRecordingsParameters();
		$recordingParams->setMeetingId( $tandem_id );
		$response = $bbb->getRecordings( $recordingParams );
		if ( $debug ) {
			return $response;
		}
		$recordings = [];
		if ( $response->getReturnCode() == self::SUCCESS_STATUS ) {
			// This method doesn't work because return statistics

			//$recordings = $response->getRecords();

			foreach ( $response->getRawXml()->recordings->children() as $recording ) {
				$recordings[] = new TandemRecord( $recording );
			}
		}

		return $recordings;

	}

	/**
	 *
	 * @return \BigBlueButton\Responses\GetMeetingsResponse
	 */
	public static function getMeetings() {
		self::setEnvConfig();
		$bbb      = new BigBlueButton();
		$response = $bbb->getMeetings();

		return $response;

	}

	public static function uploadBBBSessionToS3( $url ) {
		$parts    = parse_url( $url );
		$scheme   = $parts['scheme'];
		$hostname = $parts['host'];
		parse_str( $parts['query'], $query );
		$meetingId = $query['meetingId'];

		$videoUrl  = $scheme . '://' . $hostname . '/presentation/' . $meetingId . '/video/webcams.webm';
		$ok_video1 = self::uploadItToS3( $videoUrl, 'bbb_' . $meetingId . '.webm' );
		$videoUrl  = $scheme . '://' . $hostname . '/presentation/' . $meetingId . '/video/webcams.mp4';
		$ok_video2 = self::uploadItToS3( $videoUrl, 'bbb_' . $meetingId . '.mp4' );
		$videoUrl  = $scheme . '://' . $hostname . '/presentation/' . $meetingId . '/audio/audio.ogg';
		$ok_audio1 = self::uploadItToS3( $videoUrl, 'bbb_' . $meetingId . '_audio.ogg' );
		$videoUrl  = $scheme . '://' . $hostname . '/presentation/' . $meetingId . '/audio/audio.webm';
		$ok_audio2 = self::uploadItToS3( $videoUrl, 'bbb_' . $meetingId . '_audio.webm' );

		$other_resources = array(
			'metadata.xml',
			'shapes.svg',
			'panzooms.xml',
			'cursor.xml',
			'deskshare.xml',
			'presentation_text.json',
			'captions.json',
			'slides_new.xml'
		);
		foreach ( $other_resources as $resource ) {
			$resoruceUrl = $scheme . '://' . $hostname . '/presentation/' . $meetingId . '/' . $resource;
			self::uploadItToS3( $resoruceUrl, 'bbb_' . $meetingId . '_' . $resource );
		}
		$new_url = false;
		if ( $ok_video1 || $ok_video2 || $ok_audio1 || $ok_audio2 ) {
			$new_url = 'https://' . AWS_S3_BUCKET . '.s3.amazonaws.com/' . AWS_S3_FOLDER . '/player/tandem.html?meetingId=' . $meetingId;
		}

		return $new_url;
	}

	private static function uploadItToS3( $url, $name ) {

		$upload = false;
		if ( is_url_exist( $url ) ) {
			$upload = uploadVideoToS3( $url, $name );
		}
		return $upload;
	}

	public static function uploadRecordingToS3( TandemRecord $recording, $gestorBD, $url_video, $id_tandem ) {
		$playback_url = $recording->getPlaybackUrl() != null ? $recording->getPlaybackUrl() : $recording->getPresentationUrl();
		if ( empty( $url_video ) ) {

			$new_url = false;
			if ( defined( 'BBB_CUSTOM_PLAYER' ) && BBB_CUSTOM_PLAYER ) {
				$playback_url = str_replace( 'playback.html', 'tandem.html', $playback_url );
				$new_url      = BBBIntegration::uploadBBBSessionToS3( $playback_url );
				$recording_data = null;
				if ( $new_url ) {
					$playback_url = $new_url;
				}
			}

			if ($gestorBD) {
				$gestorBD->updateDownloadVideoUrlFeedbackTandemByTandemId( $id_tandem, $playback_url );
			}

			// Not delete Because BBB regenerate and don't make available the space
			/*if ($new_url) {
				self::deleteRecording( $recording->getRecordId() );
			}*/

		}

		return $playback_url;
	}

	/**
	 * @param $recordingId
	 *
	 * @return bool
	 */
	public static function deleteRecording($recordingId) {
		self::setEnvConfig();
		$bbb          = new BigBlueButton();
		$deleteParams = new DeleteRecordingsParameters( $recordingId );
		$response     = $bbb->deleteRecordings( $deleteParams );

		return  $response->getReturnCode() == self::SUCCESS_STATUS;

	}

	public static function endMeeting($tandem_id, $force = false) {
		self::setEnvConfig();
		$ended = false;
		$bbb          = new BigBlueButton();
		$createParams = new GetMeetingInfoParameters( $tandem_id, '' );
		$response     = $bbb->getMeetingInfo( $createParams );

		if ( $response->getReturnCode() === self::SUCCESS_STATUS ) {

			$attendees = $response->getMeeting()->getAttendees();
			if ( $force || (! $attendees || count( $attendees ) == 0 )) {
				// end
				$endParams = new EndMeetingParameters($tandem_id, '');
				$response = $bbb->endMeeting($endParams);
				$ended = $response->getReturnCode() == self::SUCCESS_STATUS;
			}

		}
		return $ended;
	}
}