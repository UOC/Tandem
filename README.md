Tandem
======

Tandem is a content management system for synchronous oral tasks for language learners.This tool will administer complementary contents to students working on a task together in a synchronous medium. The tool will retrieve the user information from the environment (e.g. Moodle classroom, Mahara group) and connect a pair or a group of students to carry out a language learning task. It will assign each student a role (i.e. student A, student B) and hand out to each student different contents belonging to same task in order to prompt authentic goal-oriented communication characteristic of fill-in-the-gap tasks.


## Tandem Installation Steps
### Install Apache
### Install MySQL
### Import attached data base structure file (tandem_2.sql)

### Modify config.inc.php file:

``` php
define('BD_HOST', 'localhost'); 
define('BD_NAME', 'tandem_2'); 
define('BD_USERNAME', 'XXX'); 
define('BD_PASSWORD', 'XXX'); 
define('PROTECTED_FOLDER', dirname(__FILE__).'/xml');
define('GOOGLE_ANALYTICS_ID', 'XXX'); 
```

* Debugging params
``` php
// Multilanguage
define('DEBUG_MULTILANGUAGE_ENABLED', true); // DO NOT USE IN PRODUCTION, it creates additional .mo files!
define('DEBUG_FORCED_LOCALE', 'en_US'); // en_US, es_ES, ca_ES...
// Tandem audio-video testing
define('DEBUG_DISABLE_AUDIO_VIDEO_TEST', true); // Disables audo and webcam testing before joining the waiting room
```

* Define the minimum conditions to obtain a certificate
``` php
define('CERTIFICATION_MINIMUM_TANDEMS', 12);
define('CERTIFICATION_MINIMUM_HOURS', 3);
define('CERTIFICATION_MINIMUM_FEEDBACKS', 6);
```

* Define the Tandem activity points scheme
``` php
define('POINTS_PER_TANDEM_DONE', 10); // Currently not used
define('POINTS_PER_GIVEN_FEEDBACK', 10);
define('POINTS_PER_SPEAKING_MINUTE', 1);
define('POINTS_PER_RATED_PARTNER_FEEDBACK', 5);
define('POINTS_PER_FEEDBACK_STAR_RECEIVED', 2);
define('POINTS_PER_SURVEY_COMPLETED', 50);
define('POINTS_PER_SECOND_SURVEY_COMPLETED', 100);
```

* Define feedback form validation
```
// Minimum possitive feedback items that user must add
define('FEEDBACK_VALIDATION_MIN_WELL_ITEMS', 1);
// Minimum negative feedback items that user must add
define('FEEDBACK_VALIDATION_MIN_ERROR_ITEMS', 1);
// Minimum characters amount that user must write in each item
define('FEEDBACK_VALIDATION_MIN_CHARS', 3);
// Comments field required?
define('FEEDBACK_VALIDATION_COMMENTS_REQUIRED', false);
``` 

* Modify configuration_oki.cfg if you have enable OKI OSIDS:
```
Indicate the location for each configuration OkiBusWebAppClient...
```

### Copy files from deliverable to web server's root folder.
### Configure Interoperability model
* IMS LTI http://imsglobal.org/lti/ 
#### LTI Configuration:
*  Consumer's enabled are located at: `IMSBasicLTI/configuration/authorizedConsumersKey.cfg`
* Parameters:
```php
consumer_key."name_consumer".enabled=1
consumer_key."name_consumer".secret=secret
```

For example to add new with resource key “test” and password “testpasswd”
```
consumer_key.test.enabled=1
consumer_key.test.secret=testpasswd 
```

* LTI provider's URL is as shown: `http://YOUR_DOMAIN/tandem/integration_tool.php`

## LTI consumer: you can configure the LTI providers in the lti_application indicanting the following parameters:
* toolurl: the url to launch
* name: the name of the tool
* resourcekey: the consumer key
* password: the secret
* sendname: set preference to send name or not
* sendemail: set preference to send email or not
* acceptgrades: set preference to accept grades or not
* acceptroster: set preference to accept roster or not
* acceptsetting: set preference to accept setting service or not
* customparameters: separated by new line you can specify each custom parameter. You can set some variables like:

To launch an lti you have to go: http://yourlocaltandem/ltiConsumer.php?id=%ID_TANDEM%
url_notify_started_recording=%URL_TANDEM%/api.php?id=%ID_TANDEM%&started=1
url_notify_ended_recording=%URL_TANDEM%/api.php?id=%ID_TANDEM%&ended=1<br>
** %ID_TANDEM% that represents the current id.<br>
** %URL_TANDEM% that represents the url of.

*Custom parameters*

|Name|Description|
|---|---|
|certificate|redirect user to certificate view on login|
|disable_profile_form|set to 1 to disable user-profile edit form in profile view, it also enables new feedback info well in the same view|
|enable_task_evaluation|if set to 1 enables the evaluation form after each tandem task|
|exercise_number_forced|forces the use of this exercise id (id from the table exercise). see also force_exercise|
|feedback_selfreflection_form|set 1 to enable the new feedback form with self-assessment feedbacks and feedback input as items list|
|force_exercise|enables forced exercise mode. see also exercise_number_forced|
|icq|set user icq account|
|is_multiple|if set to 1 information about the lti resource is added to the course name and key|
|open_tool_id|id (from the table lti_application) of the lti to be used|
|portfolio|redirect user to portfolio view on login|
|msn|set user msn account|
|previous_week|use the previous week instead of the current one. see also week|
|ranking|redirect user to ranking view on login|
|select_room|set to 1 allow to select the exercise|
|show_user_status|set to 1 to enable a mood form that will be shown within a modal before starting the tandem exercises.|
|skype|set user skype account|
|waiting_room|set to 1 allow to use a waiting room. By default the users are splitted in teams by language|
|fallback_waiting_room_avoid_language|if set to 1 students try to get pair from other languager and if there are not user pair with same language|
|waiting_room_no_teams|if set to 1 students go to same team and can do tandem with everything|
|week|force the week number. see also previous_week|
|yahoo|set user yahoo account|

## Cron jobs
There are 2 cronjobs

### Update ranking

You have to define the constant with the course id to update

````
define('COURSE_ID_CRON_UPDATE_RANKING', 374);
// Should we allow to use this script from the browser? If no config in config.inc.php defaults to false.
define('COURSE_ID_CRON_UPDATE_RANKING_ALLOW_BROWSER', false);
// Should we allow the script to output messages? If no config in config.inc.php defaults to false.
define('COURSE_ID_CRON_UPDATE_RANKING_ALLOW_OUTPUT', false);
````

The cronjob should be something like this:

````*/15 * * * * /usr/bin/php /var/www/tandem/cli/update-user-ranking.php >/dev/null````

### Mailer options

It is possible to define this 3 settings, but it is optional (they default to other values if not defined). See `cli/mailing-worker.php`.

```
define('MAILING_WORKER_MAILS_PER_EXECUTION', 0); // Number of mails to be sent per execution. If 0 it will try to send ALL mails queued for sending.
define('MAILING_WORKER_ALLOW_BROWSER', true); // Allow execution from browser (for debugging purposes)
define('MAILING_WORKER_ALLOW_OUTPUT', true); // Allow running status output (for debugging purposes)
```

The cronjob should be something like this:

````*/10 * * * * /usr/bin/php /var/www/tandem/cli/mailing-worker.php >/dev/null````

## Managing and Upload Exercises to Tandem
To download exercises you can get from http://oer.speakapps.org/xwiki/bin/view/Main/
Follow the instructions from:
### Tandem
http://langblog.speakapps.org/speakappsinfo/category/teacher/tandem-teacher/how-to-use-tandem-tandem-teacher/
### OER
http://langblog.speakapps.org/speakappsinfo/category/teacher/oer/introduction/?f=OER
### Upload Tandem Activities
http://langblog.speakapps.org/speakappsinfo/category/teacher/oer/uploading-tandem-activities-to-tandem-tool/?f=OER.

## More Information
Speak Apps Project has been funded with support from the Lifelong Learning Programme of the European Commission. This document reflects only the views of the authors, and the European Commission cannot be held responsible for any use which may be made of the information contained therein. 
![EU Logo](http://www.speakapps.eu/wp-content/themes/speakapps/images/EU_flag.jpg)
