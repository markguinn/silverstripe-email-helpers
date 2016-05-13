<?php
/**
 * This is a simple extension of the built in SS email class
 * that uses the PHPMailer library to send emails via SMTP and Emogifier to inline CSS.
 *
 * Usage: (in mysite/_config.php)
 *
 * @example  $mailer = new EmogrifiedSmtpMailer('mail.server.com', 'username', 'password', true, 'UTF-8');
 * Email::set_mailer($mailer);
 * @package smtpmailer
 */
class EmogrifiedSmtpMailer extends SmtpMailer
{
    /**
     * CSS file containing classes to inline into the email's HTML
     *
     * @var string $cssfile path to css file from project root
     */
    protected $cssfile;

    /**
     * PHPMailer SMTPDebug setting
     *
     * `0` No output
     * `1` Commands
     * `2` Data and commands
     * `3` As 2 plus connection status
     * `4` Low-level data output
     * @var integer $SMTPDebug
     */
    protected $SMTPDebug = 0;

    /**
     * Log failed emails
     *
     * @var bool $logfailedemail
     */
    protected $logfailedemail = false;

    /**
     * creates and configures the mailer
     */
    public function __construct(
        $host=false,
        $user=false,
        $pass=false,
        $encryption='fallback',
        $charset=false,
        $cssfile=false,
        $SMTPDebug='fallback',
        $logfailedemail=false
    ) {
        parent::__construct($host, $user, $pass, $encryption, $charset);

        if ($cssfile === false) {
            $cssfile = Config::inst()->get('EmogrifiedSmtpMailer', 'cssfile');
        }
        if ($SMTPDebug === 'fallback') {
            $SMTPDebug = Config::inst()->get('EmogrifiedSmtpMailer', 'SMTPDebug');
        }
        if ($logfailedemail === false) {
            $logfailedemail = Config::inst()->get('EmogrifiedSmtpMailer', 'logfailedemail');
        }
        
        $this->setCSSfile($cssfile);
        if ($SMTPDebug) {
            $this->setSMTPDebug($SMTPDebug);
        }
        $this->setLogfailedemail($logfailedemail);
    }

    /**
     * @return string
     */
    public function getCSSfile()
    {
        return $this->cssfile;
    }

    /**
     * @param integer $cssfile
     * @return $this
     */
    public function setCSSfile($cssfile)
    {
        $this->cssfile = (string)$cssfile;
        return $this;
    }

    /**
     * @return integer
     */
    public function getSMTPDebug()
    {
        return $this->SMTPDebug;
    }

    /**
     * @param integer $input PHPMailer SMTPDebug setting
     * @return $this
     */
    public function setSMTPDebug($input)
    {
        if ($input == 0 || $input == 1 || $input == 2 || $input == 3 || $input == 4) {
            $this->SMTPDebug = (int)$input;
        } else {
            user_error("PHPMailer SMTPDebug setting needs to be an integer from 0-4", E_USER_WARNING);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getLogfailedemail()
    {
        return $this->logfailedemail;
    }

    /**
     * @param integer $logfailedemail
     * @return $this
     */
    public function setLogfailedemail($logfailedemail)
    {
        $this->logfailedemail = (bool)$logfailedemail;
        return $this;
    }

    /**
     * Send a multi-part HTML email with inlined CSS
     *
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $htmlContent
     * @param array|bool $attachedFiles
     * @param array|bool $customheaders
     * @param bool $plainContent
     * @param bool $inlineImages
     *
     * @return bool
     */
    public function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false, $plainContent = false, $inlineImages = false)
    {
        $mail = $this->initEmail($to, $from, $subject, $attachedFiles, $customheaders);

        // set up the body
        // @todo inlineimages
        $mail->Body = InlineCSS::convert($htmlContent, $this->getCSSfile());
        $mail->IsHTML(true);
        if ($plainContent) {
            $mail->AltBody = $plainContent;
        }
        if ($level = $this->getSMTPDebug()) {
            $mail->SMTPDebug = $level;
            $mail->Debugoutput = function ($str, $level)
            {
                SS_Log::log(print_r($str, true), SS_Log::NOTICE);
            };
        }

        // send and return
        if ($mail->Send()) {
            return array($to, $subject, $mail->Body, $customheaders);
        } else {
            if ($this->getLogfailedemail()) {
                SS_Log::log(print_r($mail->ErrorInfo, true), SS_Log::NOTICE);
            }
            return false;
        }
    }
}

