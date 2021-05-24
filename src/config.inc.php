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
define('BD_NAME', 'tandem');
define('BD_USERNAME', 'tandem');
define('BD_PASSWORD', 'YOURPASSWORD');
define('PROTECTED_FOLDER', '/var/www/tandem/src/xml');
define('GOOGLE_ANALYTICS_ID', 'CHANGEME');

/*Paràmetros de tiempo para la waiting Tandem Room*/
define('WAITING_TIME',960);
define('TANDEM_TIME',960);

define('SHOW_RANKING',1);

define('VITAM_MAX_ROOM',0); //Set to zero to disable vitam
define('MAX_WAITING_TIME',10);
define('MAX_TANDEM_USERS',25);
define('MAX_TANDEM_WAITING',120);

define('AWS_URL', 'https://s3.amazonaws.com/');
define('AWS_S3_BUCKET', 'YOUR_BUCKET_NAE');
define('AWS_S3_USER', 'AWS_KEY');
define('AWS_S3_SECRET', 'AWS_SECRET');
define('AWS_S3_FOLDER', 'tandem'.date("Y"));
define('TMP_FOLDER', '/var/www/tandem/src/tmpFolder');

//CERTIFICATION LIMITS
define('CERTIFICATION_MINIMUM_TANDEMS', 12);
define('CERTIFICATION_MINIMUM_HOURS', 4);
define('CERTIFICATION_MINIMUM_FEEDBACKS', 12);
// END
// POINTS
define('POINTS_PER_TANDEM_DONE', 0);
define('POINTS_PER_GIVEN_FEEDBACK', 10);
define('POINTS_PER_SPEAKING_MINUTE', 1);
define('POINTS_PER_RATED_PARTNER_FEEDBACK', 5);
define('POINTS_PER_FEEDBACK_STAR_RECEIVED', 2);
define('POINTS_PER_SURVEY_COMPLETED', 100);
define('POINTS_PER_SECOND_SURVEY_COMPLETED', 200);
// END points
//Feedback validation checks
define('FEEDBACK_VALIDATION_MIN_WELL_ITEMS', 1);
define('FEEDBACK_VALIDATION_MIN_ERROR_ITEMS', 1);
define('FEEDBACK_VALIDATION_MIN_CHARS', 3);
define('FEEDBACK_VALIDATION_COMMENTS_REQUIRED', false);
//end


//SMTP
define('SMTP_HOST','smtp.external.org'); // Specify main and backup SMTP servers

define('SMTP_USER','username'); // SMTP username
define('SMTP_KEY','PWD');  // SMTP password
define('SMTP_SMTPSECURE','tls'); // Enable TLS encryption, `ssl` also accepted
define('MAIL_FROM','tandem@local.org');
define('MAIL_FROM_NAME','Tandem');
define('FULL_URL_TO_SITE', 'http://localhost/tande,');

// Form to evaluate the course
define('SHOW_SECOND_FORM', 0);

//Cron job raning
define('COURSE_ID_CRON_UPDATE_RANKING', 396);
// Should we allow to use this script from the browser? If no config in config.inc.php defaults to false.
define('COURSE_ID_CRON_UPDATE_RANKING_ALLOW_BROWSER', false);
// Should we allow the script to output messages? If no config in config.inc.php defaults to false.
define('COURSE_ID_CRON_UPDATE_RANKING_ALLOW_OUTPUT', false);

// BBB integration
define('BBB_SECRET', 'MY_SUPER_SECRET');

define('BBB_SERVER_BASE_URL', 'https://mybbbhost/bigbluebutton/');
define('BBB_CUSTOM_PLAYER', true);
define('BBB_REQUIRES_WEBCAM_TO_START', false);
