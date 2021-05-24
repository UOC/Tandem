<?php

namespace tandemPush;

interface PushNotication {

	public function sendNotificationToSubscriber(\GestorBD $gestorBD, $user_id, $tandem_id, $title, $message, $url, $icon);
	public function sendNotificationToLang(\GestorBD $gestorBD, $user_id, $language, $course_id, $title, $message, $url, $icon);

}