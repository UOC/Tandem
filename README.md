Tandem
======

Tandem is a content management system for synchronous oral tasks for language learners.This tool will administer complementary contents to students working on a task together in a synchronous medium. The tool will retrieve the user information from the environment (e.g. Moodle classroom, Mahara group) and connect a pair or a group of students to carry out a language learning task. It will assign each student a role (i.e. student A, student B) and hand out to each student different contents belonging to same task in order to prompt authentic goal-oriented communication characteristic of fill-in-the-gap tasks.

## Tandem Installation Steps
### Install Apache
### Install MySQL
### Import attached data base structure file (tandem_2.sql)

* Modify config.inc.php file:
``` php
define('BD_HOST', 'localhost'); 
define('BD_NAME', 'tandem_2'); 
define('BD_USERNAME', 'XXX'); 
define('BD_PASSWORD', 'XXX'); 
define('PROTECTED_FOLDER', dirname(__FILE__).'/xml');
define('GOOGLE_ANALYTICS_ID', 'XXX'); 
```
* Modify configuration_oki.cfg if you have enable OKI OSIDS:
```
Indicate the location for each configuration OkiBusWebAppClient
....
```
##
### Copy files from deliverable to web server's root folder.
### Configure Interoperability model
* IMS LTI http://imsglobal.org/lti/ 
#### LTI Configuration:
*  Consumer's enabled are located at:
 IMSBasicLTI/configuration/authorizedConsumersKey.cfg
* Parameters:
```php
consumer_key."name_consumer".enabled=1
consumer_key."name_consumer".secret=secret
for example to add new with resource key “test” and password “testpasswd” 
consumer_key.test.enabled=1
consumer_key.test.secret=testpasswd 
```
* LTI provider's URL is as shown:
http://<ip>/tandem/integration_tool.php

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
** %ID_TANDEM% that represents the current id.
** %URL_TANDEM% that represents the url of.

To launch an lti you have to go: http://yourlocaltandem/ltiConsumer.php?id=ID_NUMBER

