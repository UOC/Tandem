<?php

require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../classes/mailingClient.php';
require_once __DIR__ . '/../classes/mailingQueue.php';
require_once __DIR__ . '/../classes/mailingWorker.php';

define('CLI', PHP_SAPI === 'cli');

// How many mails do you want to process per execution? If no config in config.inc.php defaults to 10.
$batchSize = defined('MAILING_WORKER_MAILS_PER_EXECUTION') ? MAILING_WORKER_MAILS_PER_EXECUTION : 10;
// Should we allow to use this script from the browser? If no config in config.inc.php defaults to false.
$allowBrowser = defined('MAILING_WORKER_ALLOW_BROWSER') ? MAILING_WORKER_ALLOW_BROWSER : false;
// Should we allow the script to output messages? If no config in config.inc.php defaults to false.
$allowOutput = defined('MAILING_WORKER_ALLOW_OUTPUT') ? MAILING_WORKER_ALLOW_OUTPUT : false;

if (!$allowBrowser && !CLI) {
    exit('This script can only be used from the command line.');
}
if ($allowOutput) {
    $start = microtime(true);
    $br = CLI ? PHP_EOL : '<br/>';
    echo 'Running mailing process...' . $br;
}

$db = new GestorBD();
$queue = new MailingQueue($db);
$mailing = new MailingClient(null, null, $batchSize !== 1);
$worker = new MailingWorker($queue, $db, $mailing);
$result = $worker->process($batchSize);

if ($allowOutput) {
    $time_elapsed_secs = round(microtime(true) - $start, 3);
    echo 'Done!' . $br
        . 'Mails processed: ' . $result['total'] . $br
        . 'Correctly sent: ' . $result['success'] . $br
        . 'Sending errors: ' . $result['error'] . $br
        . 'Execution took ' . $time_elapsed_secs . ' seconds' . $br;
}

exit(0);
