# ************************************************************
# Sequel Pro SQL dump
# Versión 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: speakapps.cjzns9zuckel.eu-west-1.rds.amazonaws.com (MySQL 5.6.10)
# Base de datos: tandem_speakapps
# Tiempo de Generación: 2021-07-10 06:56:37 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla course
# ------------------------------------------------------------

DROP TABLE IF EXISTS `course`;

CREATE TABLE `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `courseKey` varchar(70) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `startDateRanking` date DEFAULT NULL,
  `endDateRanking` date DEFAULT NULL,
  `startHourRanking` decimal(2,0) DEFAULT NULL,
  `endHourRanking` decimal(2,0) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courseKey` (`courseKey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla course_exercise
# ------------------------------------------------------------

DROP TABLE IF EXISTS `course_exercise`;

CREATE TABLE `course_exercise` (
  `id_course` int(11) NOT NULL DEFAULT '0',
  `id_exercise` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `created_user_id` int(11) DEFAULT NULL,
  `week` int(11) DEFAULT NULL,
  `lang` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_course`,`id_exercise`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla exercise
# ------------------------------------------------------------

DROP TABLE IF EXISTS `exercise`;

CREATE TABLE `exercise` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `name_xml_file` varchar(60) DEFAULT NULL,
  `enabled` decimal(1,0) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `created_user_id` int(11) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `modified_user_id` int(11) DEFAULT NULL,
  `relative_path` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla feedback_rubric
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feedback_rubric`;

CREATE TABLE `feedback_rubric` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lang` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(250) DEFAULT NULL,
  `description` text,
  `field_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla feedback_rubric_course_def
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feedback_rubric_course_def`;

CREATE TABLE `feedback_rubric_course_def` (
  `id_feedback_definition` int(11) NOT NULL,
  `id_course` int(11) NOT NULL,
  PRIMARY KEY (`id_feedback_definition`,`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;



# Volcado de tabla feedback_rubric_def_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feedback_rubric_def_items`;

CREATE TABLE `feedback_rubric_def_items` (
  `def_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  PRIMARY KEY (`def_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;



# Volcado de tabla feedback_tandem
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feedback_tandem`;

CREATE TABLE `feedback_tandem` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_tandem` int(11) DEFAULT NULL,
  `id_external_tool` int(11) DEFAULT NULL,
  `end_external_service` varchar(255) DEFAULT NULL,
  `external_video_url` varchar(255) DEFAULT NULL,
  `external_video_url2` varchar(255) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `language` varchar(10) DEFAULT NULL,
  `id_partner` int(11) DEFAULT NULL,
  `partner_language` varchar(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `accepted_audio_datetime` datetime DEFAULT NULL,
  `accepted_video_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla feedback_tandem_form
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feedback_tandem_form`;

CREATE TABLE `feedback_tandem_form` (
  `id_feedback_tandem` bigint(20) NOT NULL DEFAULT '0',
  `feedback_form` longtext,
  `rating_partner_feedback_form` longtext,
  PRIMARY KEY (`id_feedback_tandem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla lti_application
# ------------------------------------------------------------

DROP TABLE IF EXISTS `lti_application`;

CREATE TABLE `lti_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `toolurl` varchar(150) COLLATE utf8_bin NOT NULL,
  `name` varchar(150) COLLATE utf8_bin NOT NULL,
  `description` mediumtext COLLATE utf8_bin NOT NULL,
  `resourcekey` varchar(150) COLLATE utf8_bin NOT NULL,
  `password` varchar(150) COLLATE utf8_bin NOT NULL,
  `preferheight` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `sendname` decimal(1,0) NOT NULL DEFAULT '0',
  `sendemailaddr` decimal(1,0) NOT NULL DEFAULT '0',
  `acceptgrades` decimal(1,0) NOT NULL DEFAULT '0',
  `allowroster` decimal(1,0) NOT NULL DEFAULT '0',
  `allowsetting` decimal(1,0) NOT NULL DEFAULT '0',
  `customparameters` text COLLATE utf8_bin,
  `allowinstructorcustom` decimal(1,0) NOT NULL DEFAULT '0',
  `organizationid` varchar(150) COLLATE utf8_bin NOT NULL,
  `organizationurl` varchar(150) COLLATE utf8_bin NOT NULL,
  `launchinpopup` decimal(1,0) DEFAULT '0',
  `debugmode` decimal(1,0) NOT NULL DEFAULT '0',
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Volcado de tabla lti_disabled_application_context
# ------------------------------------------------------------

DROP TABLE IF EXISTS `lti_disabled_application_context`;

CREATE TABLE `lti_disabled_application_context` (
  `id_tool` int(11) NOT NULL,
  `id_context` varchar(24) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_tool`,`id_context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Volcado de tabla mailing_queue
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mailing_queue`;

CREATE TABLE `mailing_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('queued','processing','done','failed') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'queued',
  `error_text` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `body` text COLLATE utf8mb4_spanish_ci NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;



# Volcado de tabla notification_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `notification_log`;

CREATE TABLE `notification_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `message` varchar(512) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `extra_params` mediumtext,
  `external_notification_id` int(11) unsigned DEFAULT NULL,
  `tandem_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla notification_registration
# ------------------------------------------------------------

DROP TABLE IF EXISTS `notification_registration`;

CREATE TABLE `notification_registration` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subscriber_id` varchar(50) NOT NULL DEFAULT '',
  `user_id` int(11) unsigned NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla questionnaire_certificate
# ------------------------------------------------------------

DROP TABLE IF EXISTS `questionnaire_certificate`;

CREATE TABLE `questionnaire_certificate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `useremail` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `courseKey` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `presurvey` tinyint(1) NOT NULL DEFAULT '0',
  `presurvey_date` datetime DEFAULT NULL,
  `postsurvey` tinyint(1) NOT NULL DEFAULT '0',
  `postsurvey_date` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



# Volcado de tabla questionnaire_certificate_old
# ------------------------------------------------------------

DROP TABLE IF EXISTS `questionnaire_certificate_old`;

CREATE TABLE `questionnaire_certificate_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `useremail` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `courseKey` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



# Volcado de tabla recording_info
# ------------------------------------------------------------

DROP TABLE IF EXISTS `recording_info`;

CREATE TABLE `recording_info` (
  `recordingId` varchar(255) NOT NULL DEFAULT '',
  `tandemId` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isPublished` decimal(1,0) DEFAULT NULL,
  `state` varchar(90) DEFAULT NULL,
  `startTime` decimal(15,0) DEFAULT NULL,
  `endTime` decimal(15,0) DEFAULT NULL,
  `playbackType` varchar(90) DEFAULT NULL,
  `playbackUrl` varchar(255) DEFAULT NULL,
  `presentationUrl` varchar(255) DEFAULT NULL,
  `podcastUrl` varchar(255) DEFAULT NULL,
  `statisticsUrl` varchar(255) DEFAULT NULL,
  `playbackLength` decimal(8,0) DEFAULT NULL,
  `metas` mediumtext,
  `created` datetime DEFAULT NULL,
  `udpated` datetime DEFAULT NULL,
  PRIMARY KEY (`recordingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla remote_application
# ------------------------------------------------------------

DROP TABLE IF EXISTS `remote_application`;

CREATE TABLE `remote_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `toolurl` varchar(250) COLLATE utf8_bin NOT NULL,
  `name` varchar(150) COLLATE utf8_bin NOT NULL,
  `description` mediumtext COLLATE utf8_bin NOT NULL,
  `launchinpopup` decimal(1,0) DEFAULT '0',
  `debugmode` decimal(1,0) NOT NULL DEFAULT '0',
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Volcado de tabla remote_disabled_application_context
# ------------------------------------------------------------

DROP TABLE IF EXISTS `remote_disabled_application_context`;

CREATE TABLE `remote_disabled_application_context` (
  `id_tool` int(11) NOT NULL,
  `id_context` varchar(24) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_tool`,`id_context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Volcado de tabla session
# ------------------------------------------------------------

DROP TABLE IF EXISTS `session`;

CREATE TABLE `session` (
  `id_tandem` int(10) NOT NULL,
  `status` int(10) DEFAULT '0' COMMENT '0, is not set, 1 started video sessio, 2 session available',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id_tandem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla session_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `session_user`;

CREATE TABLE `session_user` (
  `tandem_id` int(10) NOT NULL DEFAULT '0',
  `user_id` int(10) NOT NULL DEFAULT '0',
  `sent_email` tinyint(1) DEFAULT '0',
  `url_sent` text COLLATE utf8_unicode_ci,
  `select_room` tinyint(1) DEFAULT NULL,
  `open_tool_id` int(10) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`tandem_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Volcado de tabla tandem
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tandem`;

CREATE TABLE `tandem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_exercise` int(11) DEFAULT NULL,
  `id_course` int(11) DEFAULT NULL,
  `id_resource_lti` varchar(100) DEFAULT NULL,
  `id_user_host` int(11) DEFAULT NULL COMMENT 'User who invited to the tandem',
  `id_user_guest` int(11) DEFAULT NULL COMMENT 'User who is invited to the tandem',
  `message` mediumtext COMMENT 'To indicate to the other user to',
  `xml` text COMMENT 'To save the xml to reproduce',
  `is_guest_user_logged` bit(1) DEFAULT NULL,
  `date_guest_user_logged` datetime DEFAULT NULL,
  `user_agent_host` varchar(255) NOT NULL,
  `user_agent_guest` varchar(255) NOT NULL,
  `is_finished` bit(1) DEFAULT NULL,
  `finalized` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla tandem_deleted
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tandem_deleted`;

CREATE TABLE `tandem_deleted` (
  `id` int(11) NOT NULL,
  `id_exercise` int(11) DEFAULT NULL,
  `id_course` int(11) DEFAULT NULL,
  `id_resource_lti` varchar(100) DEFAULT NULL,
  `id_user_host` int(11) DEFAULT NULL COMMENT 'User who invited to the tandem',
  `id_user_guest` int(11) DEFAULT NULL COMMENT 'User who is invited to the tandem',
  `message` mediumtext COMMENT 'To indicate to the other user to',
  `xml` text COMMENT 'To save the xml to reproduce',
  `is_guest_user_logged` bit(1) DEFAULT NULL,
  `date_guest_user_logged` datetime DEFAULT NULL,
  `user_agent_host` varchar(255) NOT NULL,
  `user_agent_guest` varchar(255) NOT NULL,
  `is_finished` bit(1) DEFAULT NULL,
  `finalized` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla tandem_logs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tandem_logs`;

CREATE TABLE `tandem_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_course` int(11) DEFAULT NULL,
  `id_tandem` varchar(100) DEFAULT NULL,
  `page` varchar(100) DEFAULT NULL,
  `params` mediumtext,
  `ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla tandem_quiz
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tandem_quiz`;

CREATE TABLE `tandem_quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT NULL,
  `language` varchar(10) DEFAULT NULL,
  `category` enum('vocabulary','grammar','culture','tandemstrategies') DEFAULT 'vocabulary',
  `question` text,
  `correctAnswer` enum('a','b','c','d','e') DEFAULT NULL,
  `correctAnswerText` text,
  `falseAnswerText` text,
  `active` decimal(1,0) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla tandem_quiz_answer
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tandem_quiz_answer`;

CREATE TABLE `tandem_quiz_answer` (
  `quiz_id` int(11) NOT NULL,
  `answer` enum('a','b','c','d','e') NOT NULL DEFAULT 'a',
  `answerText` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`quiz_id`,`answer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(70) DEFAULT NULL,
  `firstname` varchar(50) NOT NULL,
  `surname` varchar(75) NOT NULL,
  `fullname` varchar(150) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `image` varchar(150) DEFAULT NULL,
  `last_session` datetime DEFAULT NULL,
  `icq` varchar(70) DEFAULT NULL,
  `skype` varchar(70) DEFAULT NULL,
  `msn` varchar(70) DEFAULT NULL,
  `yahoo` varchar(70) DEFAULT NULL,
  `last_user_agent` varchar(255) DEFAULT NULL,
  `blocked` bit(1) DEFAULT b'0',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla user_course
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_course`;

CREATE TABLE `user_course` (
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  `is_instructor` bit(1) DEFAULT NULL,
  `lis_result_sourceid` varchar(255) NOT NULL,
  `inTandem` tinyint(1) NOT NULL DEFAULT '0',
  `lastAccessTandem` datetime NOT NULL,
  `language` varchar(100) DEFAULT NULL,
  `lastActionTime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`,`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla user_portfolio_profile
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_portfolio_profile`;

CREATE TABLE `user_portfolio_profile` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `data` longtext COLLATE utf8_bin,
  `type` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Volcado de tabla user_ranking
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_ranking`;

CREATE TABLE `user_ranking` (
  `user_id` int(10) NOT NULL,
  `points` int(10) DEFAULT '0',
  `course_id` int(10) NOT NULL,
  `lang` varchar(50) DEFAULT NULL,
  `total_time` decimal(10,2) DEFAULT NULL,
  `number_of_tandems` decimal(10,2) DEFAULT NULL,
  `fluency` decimal(10,2) DEFAULT NULL,
  `accuracy` decimal(10,2) DEFAULT NULL,
  `overall_grade` decimal(10,2) DEFAULT NULL,
  `badge_level` decimal(1,0) DEFAULT NULL,
  `badge_feedback_expert` decimal(1,0) DEFAULT NULL,
  `badge_loyalty` decimal(1,0) DEFAULT NULL,
  `badge_social` decimal(1,0) DEFAULT NULL,
  `badge_week_ranking` decimal(1,0) DEFAULT NULL,
  `badge_forms` decimal(1,0) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla user_tandem
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_tandem`;

CREATE TABLE `user_tandem` (
  `id_tandem` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0' COMMENT 'User who start the tandem',
  `total_time` decimal(10,2) DEFAULT NULL COMMENT 'Time in seconds',
  `points` decimal(10,2) DEFAULT NULL,
  `user_mood` int(11) DEFAULT '0' COMMENT '1 = smile , 2 = neutral, 3 = sad',
  `is_finished` bit(1) DEFAULT NULL,
  `finalized` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id_tandem`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla user_tandem_tandem_videoconnection
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_tandem_tandem_videoconnection`;

CREATE TABLE `user_tandem_tandem_videoconnection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_tandem` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0' COMMENT 'User who start the tandem',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



# Volcado de tabla user_tandem_task
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_tandem_task`;

CREATE TABLE `user_tandem_task` (
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_tandem` int(11) NOT NULL DEFAULT '0',
  `task_number` decimal(4,0) NOT NULL DEFAULT '0',
  `total_time` decimal(10,2) DEFAULT NULL COMMENT 'Time in seconds',
  `points` decimal(10,2) DEFAULT NULL,
  `task_enjoyed` int(11) DEFAULT '0',
  `task_nervous` int(11) DEFAULT '0',
  `task_valoration` int(11) DEFAULT '0',
  `task_comment` mediumtext,
  `is_finished` bit(1) DEFAULT NULL,
  `finalized` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`,`id_tandem`,`task_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`speakapps`@`%` */ /*!50003 TRIGGER `updateTandemTime` AFTER UPDATE ON `user_tandem_task` FOR EACH ROW BEGIN
	update user_tandem set total_time = (select sum(total_time) from user_tandem_task where id_tandem = NEW.id_tandem and id_user=NEW.id_user) where id_tandem = NEW.id_tandem and id_user=NEW.id_user;
	
END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Volcado de tabla user_tandem_task_question
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_tandem_task_question`;

CREATE TABLE `user_tandem_task_question` (
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_tandem` int(11) NOT NULL DEFAULT '0',
  `task_number` decimal(4,0) NOT NULL DEFAULT '0',
  `question_number` decimal(4,0) NOT NULL DEFAULT '0',
  `total_time` decimal(10,2) DEFAULT NULL COMMENT 'Time in seconds',
  `points` decimal(10,2) DEFAULT NULL,
  `is_finished` bit(1) DEFAULT NULL,
  `finalized` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`,`id_tandem`,`task_number`,`question_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla vc_videoanalytics
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vc_videoanalytics`;

CREATE TABLE `vc_videoanalytics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `meeting_room_id` int(11) unsigned NOT NULL,
  `player` varchar(50) NOT NULL COMMENT 'Indicates newplayer or player',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla vc_videoanalytics_actions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `vc_videoanalytics_actions`;

CREATE TABLE `vc_videoanalytics_actions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_videoplayerhistory` int(11) unsigned NOT NULL,
  `action` varchar(50) DEFAULT NULL COMMENT 'Indicates if user has played, pause, rewind, seek, etc...',
  `extra_param` varchar(255) DEFAULT NULL COMMENT 'Indicates the time',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Volcado de tabla waiting_room
# ------------------------------------------------------------

DROP TABLE IF EXISTS `waiting_room`;

CREATE TABLE `waiting_room` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language` varchar(10) COLLATE utf8_bin NOT NULL,
  `id_course` int(11) NOT NULL,
  `id_exercise` int(11) NOT NULL,
  `number_user_waiting` decimal(6,0) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Volcado de tabla waiting_room_history
# ------------------------------------------------------------

DROP TABLE IF EXISTS `waiting_room_history`;

CREATE TABLE `waiting_room_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_waiting_room` bigint(20) NOT NULL,
  `language` varchar(10) COLLATE utf8_bin NOT NULL,
  `id_course` int(11) NOT NULL,
  `id_exercise` int(11) NOT NULL,
  `number_user_waiting` decimal(6,0) NOT NULL,
  `created` datetime NOT NULL,
  `created_history` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Volcado de tabla waiting_room_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `waiting_room_user`;

CREATE TABLE `waiting_room_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_waiting_room` bigint(20) NOT NULL,
  `id_user` bigint(11) NOT NULL,
  `created` datetime NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Volcado de tabla waiting_room_user_history
# ------------------------------------------------------------

DROP TABLE IF EXISTS `waiting_room_user_history`;

CREATE TABLE `waiting_room_user_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_waiting_room` bigint(20) NOT NULL,
  `id_user` bigint(11) NOT NULL,
  `status` enum('waiting','assigned','lapsed','give_up') COLLATE utf8_bin NOT NULL,
  `id_tandem` int(11) DEFAULT NULL COMMENT 'only when user has partner and start tandem',
  `created` datetime NOT NULL,
  `created_history` datetime NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_waiting_room` (`id_waiting_room`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
