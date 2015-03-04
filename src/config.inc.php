<?php 
/**
 * File: Configuration
 * 	Stores your Tandem information. 
 *
 * License and Copyright:
 * 	See the included NOTICE.md file for more information.
 *
 */
/**
 * 
 * Define la bd
 */
define('BD_HOST', 'localhost');
define('BD_NAME', 'YOUR_TANDEM_DB_NAME');
define('BD_USERNAME', 'YOUR_TANDEM_DB_USERNAME');
define('BD_PASSWORD', 'YOUR_TANDEM_DB_PWD');
define('PROTECTED_FOLDER', 'PATH_TO_PROCTECTED_FOLDER'); //For example xml
define('GOOGLE_ANALYTICS_ID', 'YOUR UA TRACK ID');
define('FULL_URL_TO_SITE', 'http://localhost/tandem');

/*Params to Wainting ROOM*/
define('WAITING_TIME',960);
define('TANDEM_TIME',960);
define('SHOW_RANKING',1);
define('MAX_TANDEM_USERS',25);
define('MAX_TANDEM_WAITING',60);

//SMTP Config used only in Waiting Room
define('SMTP_HOST','YOUR SMTP HOST'); // Specify main and backup SMTP servers
define('SMTP_USER','YOUR SMTP USERNAME'); // SMTP username
define('SMTP_KEY','YOUR SMTP PWD');  // SMTP password
define('SMTP_SMTPSECURE','tls'); // Enable TLS encryption, `ssl` also accepted
define('MAIL_FROM','MAIL FROM ADDRESS'); 
define('MAIL_FROM_NAME','MAIL FROM NAME');

// User can edit skin?
define('EDITSKIN', '0');