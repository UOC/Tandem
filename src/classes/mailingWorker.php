<?php

class MailingWorker {
    private $db;
    private $queue;
    private $mailing;

    /**
     * @param MailingQueue $queue
     * @param GestorBD $db
     * @param MailingClient $mailing
     */
    public function __construct($queue, $db, $mailing) {
        $this->queue = $queue;
        $this->db = $db;
        $this->mailing = $mailing;
    }

    /**
     * @param int $batchSize
     * @return int[]
     */
    public function process($batchSize) {
        $mails = $this->queue->getNextMailsFromQueue($batchSize);

        $total = !empty($mails) ? count($mails) : 0;
        $success = 0;
        $error = 0;

        if (!empty($mails)) {
            // Mark them all as in progress before starting to send them
            // to prevent next cron execution from fetching them.
            $this->bulkMarkMailAsBeingProcessed(array_map(function($n){ return $n['id']; }, $mails));

            // Now try to send them one by one.
            foreach ($mails as $mail) {
                try {
                    if ($this->mailing->sendEmail($mail['email'], $mail['fullname'], $mail['subject'], $mail['body'])) {
                        $this->markMailAsDone($mail);
                        ++$success;
                    } else {
                        $this->markMailAsFailed($mail, 'Send email process failed.');
                        ++$error;
                    }
                } catch (Exception $e) {
                    $errorMessageText = $e->getMessage();
                    $this->markMailAsFailed($mail, $errorMessageText);
                    ++$error;
                }
            }
        }

        return ['total' => $total, 'success' => $success, 'error' => $error];
    }

    public function markMailAsBeingProcessed($item) {
        $this->updateMailStatus($item['id'], 'processing');
    }

    public function bulkMarkMailAsBeingProcessed($itemIds) {
        $this->bulkUpdateMailStatus($itemIds, 'processing');
    }

    public function markMailAsDone($item) {
        $this->updateMailStatus($item['id'], 'done');
    }

    public function markMailAsFailed($item, $errorMessageText) {
        $this->updateMailStatus($item['id'], 'failed', $errorMessageText);
    }

    /**
     * @param int|string $itemId
     * @param string $itemStatus
     * @param null|string $errorMessageText
     */
    protected function updateMailStatus($itemId, $itemStatus, $errorMessageText = null) {
        $now = time();
        $sql = 'UPDATE mailing_queue
                   SET `status` = ' . $this->db->escapeString($itemStatus) . ',
                       `error_text` = ' . $this->db->escapeString($errorMessageText) . ',
                       `updated_at` = ' . $now . '
                 WHERE `id` = ' . $this->db->escapeString($itemId) . ' ';

        $this->db->consulta($sql);
    }

    /**
     * @param string[]|int[] $itemIdsList
     * @param string $itemStatus
     * @param null|string $errorMessageText
     */
    protected function bulkUpdateMailStatus($itemIdsList, $itemStatus, $errorMessageText = null) {
        $now = time();
        $sql = 'UPDATE mailing_queue
                   SET `status` = ' . $this->db->escapeString($itemStatus) . ',
                       `error_text` = ' . $this->db->escapeString($errorMessageText) . ',
                       `updated_at` = ' . $now . '
                 WHERE `id` IN (' . implode(',', $itemIdsList) . ') ';

        $this->db->consulta($sql);
    }
}
