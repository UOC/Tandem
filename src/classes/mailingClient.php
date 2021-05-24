<?php

require_once __DIR__ . '/phpmailer/PHPMailerAutoload.php';

class MailingClient {
    private $db;
    private $lang;
    private $mailer;
    private $bulkMailing;

    /**
     * TandemMailing constructor.
     * @param GestorBD|null $DBManager Provide the db manager if you are planing to use the DB interface during the mailing process.
     * @param Language|null $LanguageManager Provide the lang manager if you are planing to use multilang during the mailing process.
     * @param bool $bulkMailing
     * @throws InvalidArgumentException
     */
    public function __construct($DBManager = null, $LanguageManager = null, $bulkMailing = false) {
        if (null !== $DBManager && !($DBManager instanceof GestorBD)) {
            throw new InvalidArgumentException('Mailing class expects DBManager to be null or an instance of the GestorDB class.');
        }
        if (null !== $LanguageManager && !($LanguageManager instanceof Language)) {
            throw new InvalidArgumentException('Mailing class expects LanguageManager to be null or an instance of the Language class.');
        }

        $this->db = $DBManager;
        $this->lang = $LanguageManager;
        $this->mailer = $this->setupMailer($bulkMailing);
        $this->bulkMailing = $bulkMailing;
    }

    /**
     * @param bool $bulkMailing
     * @return PHPMailer
     */
    private function setupMailer($bulkMailing) {
        $mailer = new PHPMailer;
        $mailer->CharSet = 'UTF-8';
        $mailer->isSMTP(); // Set mailer to use SMTP
        $mailer->Host = SMTP_HOST; // Specify main and backup SMTP servers
        $mailer->SMTPAuth = true; // Enable SMTP authentication
        $mailer->SMTPKeepAlive = (bool) $bulkMailing;
        $mailer->Username = SMTP_USER; // SMTP username
        $mailer->Password = SMTP_KEY; // SMTP password
        $mailer->SMTPSecure = SMTP_SMTPSECURE; // Enable TLS encryption, `ssl` also accepted
        $mailer->Port = 587; // TCP port to connect to
        $mailer->From = MAIL_FROM;
        $mailer->FromName = MAIL_FROM_NAME;
        $mailer->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);
        $mailer->WordWrap = 50; // Set word wrap to 50 character
        $mailer->isHTML(true); // Set email format to HTML
//        $mailer->AltBody = 'To view the message, please use an HTML compatible email viewer!';
//        $mail->SMTPDebug = 3; // Enable verbose debug output

        return $mailer;
    }

    /**
     * Send a notification email to a user that his partner is waiting for him to do the tandem.
     * @param $tandem_id
     * @param $user_id
     * @param $force_select_room
     * @param $open_tool_id
     * @param $sent_url
     * @param $userab
     * @return bool
     */
    public function tandemTimeOutNotificationEmail($tandem_id, $user_id, $force_select_room, $open_tool_id, $sent_url, $userab) {
        // First we need to get the partner user_id
        $sql = 'SELECT * FROM tandem
                WHERE id = ' . $this->db->escapeString($tandem_id) . ' ';
        $result = $this->db->consulta($sql);
        if ($this->db->numResultats($result) < 1) {
            return false;
        }

        $result = $this->db->obteComArray($result);
        if ($result[0]['id_user_host'] == $user_id) {
            $partner_user_id = $result[0]['id_user_guest'];
        } else {
            $partner_user_id = $result[0]['id_user_host'];
        }

        $partner_data = $this->db->getUserData($partner_user_id);
//        error_log("partner_user_id".$partner_user_id);
//        error_log(serialize($partner_data));
        $partner_session_data = $this->db->getSessionUserData($partner_user_id, $tandem_id);
        $user_session_data = $this->db->getSessionUserData($user_id, $tandem_id);

        if (empty($partner_session_data)) {
            // If the partener doesn't have a session data, then we create one for him.
            if ($userab === 'a') {
                $userR = 'user=b';
            } else {
                $userR = 'user=a';
            }
            $sent_url = str_replace('user=' . $userab, $userR, $sent_url);
            $partner_session_data = $this->db->createSessionUser($tandem_id, $partner_user_id, $force_select_room, $open_tool_id, $sent_url);
        }

        if (empty($partner_data) || empty($user_session_data) || empty($partner_session_data) || $user_session_data['sent_email'] != 0) {
            return false;
        }

        $destination_url = FULL_URL_TO_SITE . '/goToTandem.php?tandem_id=' . $tandem_id . '&user_id=' . $partner_user_id . '&token=' . $partner_session_data['token'] . '';
	    $user = $this->db->getUserB($user_id);
        $body = $this->lang->getTagDouble("Your partner %s is waiting for you to do a tandem, please click on the following Link to access the tandem.<br ><br /><a href='%s'>Go to Tandem</a>", $user->fullname, $destination_url);
        $body .= static::addTandemHtmlFooter();
        $subject = $this->lang->getTag('Your partner %s is waiting for you to do a tandem', $user->fullname);
        $recipientEmail = $partner_data['email'];
        $recipientFullname = $partner_data['fullname'];

        if (!$this->sendEmail($recipientEmail, $recipientFullname, $subject, $body)) {
            return false;
        }

        $sql = 'UPDATE `session_user` SET sent_email = 1 
                WHERE tandem_id = ' . $this->db->escapeString($tandem_id) . '
                AND user_id = ' . $this->db->escapeString($user_id) . ' ';
        $this->db->consulta($sql);

        return true;
    }

    /**
     * @param $recipientEmail
     * @param $recipientFullname
     * @param $subject
     * @param $body
     * @return bool True if the email has been correctly sent, false otherwise.
     */
    public function sendEmail($recipientEmail, $recipientFullname, $subject, $body) {
        $this->mailer->addAddress($recipientEmail, $recipientFullname);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $body;

        try {
            $sent = $this->mailer->send();
            if ($this->bulkMailing) {
                // Clear current address in case this method is called within a loop.
                $this->mailer->clearAddresses();
            }
            if (!$sent) {
                return false;
            }
        } catch (phpmailerException $e) {
            if ($this->bulkMailing) {
                $this->mailer->clearAddresses();
            }
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public static function addTandemHtmlFooter() {
        return "<br/><br/><img src='" . FULL_URL_TO_SITE . "/css/images/logo_Tandem.png'/>";
    }
}
