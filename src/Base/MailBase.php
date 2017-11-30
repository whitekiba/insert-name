<?php namespace InsertName\Base;

class MailBase extends TemplateBase {
	protected $template_prefix = "email_", $email, $name = false, $attachment = false, $subject = 'Ihre neue Rechnung';
	protected $_mailer, $_db, $_config;
	protected $sender_email = "net@rout0r.org", $sender_name = false;

	/**
	 * MailBase constructor.
	 */
	function __construct() {
	    parent::__construct();
	    
		//$transport = Swift_SendmailTransport::newInstance("/usr/sbin/sendmail -t"); #transport. wir nutzen sendmail weil ssmtp
        $smtpIP = Config::getInstance()->get("smtpIP");
        $smtpPort = Config::getInstance()->get("smtpPort");
        $transport = new Swift_SmtpTransport($smtpIP, $smtpPort);

        $this->_mailer = new Swift_Mailer($transport); # der mailer
        $this->_db = DB::getInstance();
        $this->_config = Config::getInstance();
	}

	/**
	 * Set emailaddress
	 * @param $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setSender($email, $name = false) {
		$this->sender_email = $email;
		$this->sender_name = $name;
	}

	/**
	 * set full name of receiver
	 * @param $name
	 */
	public function setFullName($name) {
		$this->name = $name;
	}

	public function setAttachment($attachment) {
		$this->attachment = $attachment;
	}

	/**
	 * Send!
	 * @return int
	 */
	public function send() {
		if ($this->fillTemplate()) {
			#Email senden.
			$message = new Swift_Message($this->subject);
            $message->setFrom(array($this->sender_email => $this->sender_name))
				->setTo(array($this->email => $this->name))
				->setBody($this->text);
			;

			if ($this->attachment) {
				$message->attach(Swift_Attachment::fromPath($this->attachment));
			}

			if ($this->_mailer->send($message) > 0) {
                return true;
            }
		}
		return false;
	}
}