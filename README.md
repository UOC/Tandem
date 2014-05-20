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
define('GOOGLE_ANALYTICS_ID', 'XXX'); 
```
### Copy files from deliverable to web server's root folder.
### Configure Interoperability model
* LTI http://imsglobal.org/lti/ - LTI Configuration:
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

