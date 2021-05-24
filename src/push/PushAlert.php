<?php


namespace tandemPush;

require_once __DIR__ . '/../classes/lang.php';
require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../config.inc.php';


class PushAlert implements PushNotication {


	public function sendNotificationToSubscriber( \GestorBD $gestorBD, $user_id, $tandem_id, $title, $message, $url, $icon ) {

		$subscriptions = $gestorBD->get_push_subscription_by_user_id( $user_id );
		return $this->sendToSubscribers($subscriptions, $gestorBD, $title, $message, $url, $icon, $user_id, $tandem_id);
	}

	private function sendToSubscribers($subscriptions, $gestorBD, $title, $message, $url, $icon, $user_id, $tandem_id = -1) {
		$subscribers   = array();
		foreach ( $subscriptions as $subscription ) {
			$subscriber_id = $subscription['subscriber_id'];
			array_push( $subscribers, $subscriber_id );

		}
		$external_notification_id = -1;

		if ( count( $subscribers ) > 0 ) {
			$extra_post_vars = array( 'subscribers' => json_encode($subscribers) );
			$curlUrl = "https://api.pushalert.co/rest/v1/send";

			$external_notification_id = $this->send( $gestorBD, $curlUrl, $title, $message, $url, $icon, $user_id, $tandem_id, $extra_post_vars );
		}
		return $external_notification_id;


	}

	public function sendNotificationToLang( \GestorBD $gestorBD, $user_id, $language, $course_id, $title, $message, $url, $icon ) {
		$subscriptions = $gestorBD->get_push_subscription_by_lang ($course_id, $language);
		return $this->sendToSubscribers($subscriptions, $gestorBD, $title, $message, $url, $icon, $user_id);
	}


	public function sendNotificationToSegment( \GestorBD $gestorBD, $user_id, $language, $course_id, $title, $message, $url, $icon ) {
		global $PUSH_SEGMENTS_ID;
		$key        = substr( $language, 0, 2 ) . '.course' . $course_id;
		$external_notification_id = -1;
		$segment_id = isset( $PUSH_SEGMENTS_ID[ $key ] ) ? $PUSH_SEGMENTS_ID[ $key ] : false;
		if ( $segment_id ) {
			$curlUrl = "https://api.pushalert.co/rest/v1/segment/".$segment_id."/send";
			$external_notification_id = $this->send( $gestorBD, $curlUrl, $title, $message, $url, $icon, $user_id );
		}
		return $external_notification_id;
	}

	private function send( $gestorBD, $curlUrl, $title, $message, $url, $icon, $user_id, $tandem_id = -1, $extra_post_vars = array() ) {

		$apiKey = PUSH_ALERT_KEY;

		//POST variables
		$post_vars = array_merge( array(
			"icon"        => $icon,
			"title"       => $title,
			"message"     => $message,
			"url"         => $url,
			"expire_time" => 300 //In seconds 5 minutes
		), $extra_post_vars );

		$headers   = Array();
		$headers[] = "Authorization: api_key=" . $apiKey;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $curlUrl );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $post_vars ) );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		$result = curl_exec( $ch );

		$external_notification_id = - 1;
		$output                   = json_decode( $result, true );
		if ( $output["success"] ) {
			$external_notification_id = $output["id"]; //Sent Notification ID
		} else {
			//Others like bad request
		}
		$gestorBD->log_push_notification( $user_id, $tandem_id, $title, $message, $url, serialize( $extra_post_vars ),
			$external_notification_id );
		return $external_notification_id;

	}

}
