<?php
/**
 * Created by PhpStorm.
 * User: antonibertranbellido
 * Date: 1/5/18
 * Time: 11:07
 */

require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../classes/gestorBD.php';

define('CLI', PHP_SAPI === 'cli');

// Should we allow to use this script from the browser? If no config in config.inc.php defaults to false.
$allowBrowser = defined('COURSE_ID_CRON_UPDATE_RANKING_ALLOW_BROWSER') ? COURSE_ID_CRON_UPDATE_RANKING_ALLOW_BROWSER : false;
// Should we allow the script to output messages? If no config in config.inc.php defaults to false.
$allowOutput = defined('COURSE_ID_CRON_UPDATE_RANKING_ALLOW_OUTPUT') ? COURSE_ID_CRON_UPDATE_RANKING_ALLOW_OUTPUT : false;

if (!$allowBrowser && !CLI) {
    exit('This script can only be used from the command line.');
}

if ($allowOutput) {
    $start = microtime(true);
    $br = CLI ? PHP_EOL : '<br/>';
    echo 'Running update ranking process...' . $br;
}
if (defined('COURSE_ID_CRON_UPDATE_RANKING')) {
    $gestorBD = new GestorBD();
    $gestorBD->updateAllUsersRankingPoints(COURSE_ID_CRON_UPDATE_RANKING);
    if ($allowOutput) {
        $time_elapsed_secs = round(microtime(true) - $start, 3);
        echo 'Done!' . $br
            . 'Course_id : ' . COURSE_ID_CRON_UPDATE_RANKING . $br
            . 'Execution took ' . $time_elapsed_secs . ' seconds' . $br;
    }
}
elseif ($allowOutput) {
    echo 'The service is disabled';
}
exit(0);
