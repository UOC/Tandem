<?php

/**
 * FIFO Mailing queue
 * Class MailingQueue
 */
class MailingQueue {
    protected $db;

    /**
     * @param GestorBD $dbManager
     */
    public function __construct($dbManager) {
        $this->db = $dbManager;
    }

    /**
     * @param string $recipientEmail
     * @param string $recipientFullname
     * @param string $subject
     * @param string $body
     * @return bool True if mail successfully added to queue, false otherwise.
     */
    public function addMailToQueue($recipientEmail, $recipientFullname, $subject, $body) {
        $now = time();
        $sql = 'INSERT INTO mailing_queue (email, fullname, subject, body, created_at, updated_at)
                VALUES (' . $this->db->escapeString($recipientEmail) . ',
                ' . $this->db->escapeString($recipientFullname) . ',
                ' . $this->db->escapeString($subject) . ',
                ' . $this->db->escapeString($body) . ',
                ' . $now . ',
                ' . $now . ')';

        ob_start();
        $queryresult = $this->db->consulta($sql);
        $errors = ob_get_clean();

        return ($queryresult && empty($errors));
    }

    /**
     * @param int $limit
     * @return array|false
     */
    public function getNextMailsFromQueue($limit) {
        $sqlLimit = $limit === 0 ? '' : 'LIMIT ' . $limit;
        $item = $this->db->consulta('SELECT * FROM mailing_queue WHERE status = "queued" ORDER BY created_at ASC ' . $sqlLimit);

        if ($this->db->numResultats($item) > 0) {
            return $this->db->obteComArray($item);
        }

        return false;
    }
}
