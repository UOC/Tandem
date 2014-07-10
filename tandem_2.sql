DROP TABLE IF EXISTS `course`;

CREATE TABLE `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `courseKey` varchar(70) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courseKey` (`courseKey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `course_exercise`;

CREATE TABLE `course_exercise` (
  `id_course` int(11) NOT NULL DEFAULT '0',
  `id_exercise` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `created_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_course`,`id_exercise`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



DROP TABLE IF EXISTS `lti_disabled_application_context`;

CREATE TABLE `lti_disabled_application_context` (
  `id_tool` int(11) NOT NULL,
  `id_context` varchar(24) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_tool`,`id_context`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `remote_disabled_application_context`;

CREATE TABLE `remote_disabled_application_context` (
  `id_tool` int(11) NOT NULL,
  `id_context` varchar(24) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_tool`,`id_context`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


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
  `blocked` bit(1) DEFAULT b'0',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `user_course`;

CREATE TABLE `user_course` (
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  `is_instructor` bit(1) DEFAULT NULL,
  `lis_result_sourceid` varchar(255) NOT NULL,
  `inTandem` tinyint(1) NOT NULL DEFAULT '0',
  `lastAccessTandem` datetime NOT NULL,
  PRIMARY KEY (`id_user`,`id_course`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `user_tandem`;

CREATE TABLE `user_tandem` (
  `id_tandem` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0' COMMENT 'User who start the tandem',
  `total_time` decimal(10,2) DEFAULT NULL COMMENT 'Time in seconds',
  `points` decimal(10,2) DEFAULT NULL,
  `is_finished` bit(1) DEFAULT NULL,
  `finalized` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id_tandem`,`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `user_tandem_task`;

CREATE TABLE `user_tandem_task` (
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_tandem` int(11) NOT NULL DEFAULT '0',
  `task_number` decimal(4,0) NOT NULL DEFAULT '0',
  `total_time` decimal(10,2) DEFAULT NULL COMMENT 'Time in seconds',
  `points` decimal(10,2) DEFAULT NULL,
  `is_finished` bit(1) DEFAULT NULL,
  `finalized` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`,`id_tandem`,`task_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
