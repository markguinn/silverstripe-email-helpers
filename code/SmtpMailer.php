<?php

/**
 * This is a simple extension of the built in SS email class
 * that uses the PHPMailer library to send emails via SMTP.
 *
 * Usage: (in mysite/_config.php)
 *
 * $mailer = new SmtpMailer('mail.server.com', 'username', 'password');
 * Email::set_mailer($mailer);
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @package sstools
 * @subpackage smtpmailer
 */
class SmtpMailer extends Mailer
{
    /**
     * @var string $host - smtp host
     */
    protected $host;

    /**
     * @var string $user - smtp username
     */
    protected $user;

    /**
     * @var string $pass - smtp password
     */
    protected $pass;

    /**
     * @var string $charset - charset for mail message
     */
    protected $charset;

    /**
     * @var bool $tls - use tls?
     * @deprecated 2.0 - use $encryption instead
     */
    protected $tls;

    /**
     * @var int $port - the smtp server port
     */
    protected $port;

    /**
     * @var mixed $encryption - the encryption to use. Either 'tls', 'ssl', or false
     */
    protected $encryption;

    /**
     * @var int $smtpDebug - Debug param that gets passed to PHPMailer
     */
    protected $smtpDebug;


    /**
     * creates and configures the mailer
     * @param string|boolean $host the SMTP server hostname, or `false` to use the default from the config API.
     * @param string|boolean $user the SMTP username, or `false` to use the default from the config API.
     * @param string|boolean $pass the SMTP password, or `false` to use the default from the config API.
     * @param string|boolean $encryption the SMTP encryption. 'tls' or 'ssl' or false to disable encryption.
     *  Set to 'fallback' to use the default from the config API
     * @param string|boolean $charset the charset to use, or `false` to use the default from the config API.
     * @param int|boolean $port the port to use, or `false` to use the default from the config API.
     */
    public function __construct(
        $host = false,
        $user = false,
        $pass = false,
        $encryption = 'fallback',
        $charset = false,
        $port = false,
        $smtpDebug = 0
    ) {
        $cfg = $this->config();

        if ($host === false) {
            $host = $cfg->host;
        }
        if ($user === false) {
            $user = $cfg->user;
        }
        if ($pass === false) {
            $pass = $cfg->password;
        }

        if ($encryption === true) {
            $encryption = 'tls';
        } elseif ($encryption === 'fallback') {
            if ($cfg->tls) {
                Deprecation::notice(
                    '2.0',
                    'Use SmtpMailer.encryption config setting instead of \'tls\'.',
                    Deprecation::SCOPE_GLOBAL
                );
                $encryption = 'tls';
            } else {
                $encryption = $cfg->encryption;
            }
        }

        if ($charset === false) {
            $charset = $cfg->charset;
        }
        if ($port === false) {
            $port = $cfg->port;
        }
        if ($smtpDebug === 0) {
            $smtpDebug = $cfg->smtpDebug;
        }

        $this->setHost($host);
        $this->setCredentials($user, $pass);
        $this->setEncryption($encryption);
        $this->setCharset($charset);
        $this->setPort($port);
        $this->setSMTPDebug($smtpDebug);
    }

    /**
     * sets the smtp host
     * @param string $host
     * @return $this;
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Get the SMTP host
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * sets the username and password
     * @param string $user
     * @param string $pass
     * @return $this;
     */
    public function setCredentials($user, $pass)
    {
        $this->user = $user;
        $this->pass = $pass;
        return $this;
    }

    /**
     * @param boolean $tls
     * @return $this
     * @deprecated 2.0 use @see setEncryption instead
     */
    public function setTls($tls)
    {
        Deprecation::notice('2.0', 'Use setEncryption("tls") instead.');
        $this->setEncryption($tls ? 'tls' : false);
        return $this;
    }

    /**
     * @return boolean
     * @deprecated 2.0 use @see getEncryption instead
     */
    public function getTls()
    {
        Deprecation::notice('2.0', 'Use getEncryption instead.');
        return $this->getEncryption() === 'tls';
    }

    /**
     * Set the server port
     * @param int $value
     * @return $this
     */
    public function setPort($value)
    {
        $this->port = $value;
        return $this;
    }

    /**
     * Get the server port
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the encryption to use. Either 'ssl', 'tls' or false
     * @param mixed $value
     * @return $this
     */
    public function setEncryption($value)
    {
        $this->encryption = $value;
        return $this;
    }

    /**
     * Get the encryption to use
     * @return mixed
     */
    public function getEncryption()
    {
        return $this->encryption;
    }

    /**
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }


    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param int $smtpDebug
     * @return $this
     */
    public function setSMTPDebug($smtpDebug)
    {
        $this->smtpDebug = $smtpDebug;
        return $this;
    }

    /**
     * creates a new phpmailer object
     */
    protected function initMailer()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $this->host;

        if ($this->user) {
            $mail->SMTPAuth = true; // turn on SMTP authentication
            $mail->Username = $this->user;
            $mail->Password = $this->pass;
        }

        $mail->SMTPSecure = $this->getEncryption();

        if ($this->smtpDebug) {
            $mail->SMTPDebug = $this->smtpDebug;
        }

        if ($this->port) {
            $mail->Port = $this->port;
        }

        if ($this->charset) {
            $mail->CharSet = $this->charset;
        }

        return $mail;
    }


    /**
     * takes an email with or without a name and returns
     * email and name as separate parts
     * @param string $in
     * @return array ($email, $name)
     */
    protected function splitName($in)
    {
        if (preg_match('/^\s*(.+)\s+<(.+)>\s*$/', $in, $m)) {
            return array($m[2], $m[1]);
        } else {
            return array($in, '');
        }
    }


    /**
     * takes a list of emails, splits out the name and calls
     * the given function. meant to be used with AddAddress, AddBcc and AddCc
     */
    protected function explodeList($in, $mail, $func)
    {
        $list = explode(',', $in);
        foreach ($list as $item) {
            list($a, $b) = $this->splitName(trim($item));
            $mail->$func($a, $b);
        }
    }


    /**
     * shared setup for both html and plain
     */
    protected function initEmail($to, $from, $subject, $attachedFiles = false, $customheaders = false)
    {
        $mail = $this->initMailer();

        // set the from
        list($mail->From, $mail->FromName) = $this->splitName($from);

        // set the to
        $this->explodeList($to, $mail, 'AddAddress');

        // set cc and bcc if needed
        if (is_array($customheaders) && isset($customheaders['Cc'])) {
            $this->explodeList($customheaders['Cc'], $mail, 'AddCC');
            unset($customheaders['Cc']);
        }

        if (is_array($customheaders) && isset($customheaders['Bcc'])) {
            $this->explodeList($customheaders['Bcc'], $mail, 'AddBCC');
            unset($customheaders['Bcc']);
        }

        // set up the subject
        $mail->Subject = $subject;

        // add any attachments
        if (is_array($attachedFiles)) {
            // include any specified attachments as additional parts
            foreach ($attachedFiles as $file) {
                if (isset($file['tmp_name']) && isset($file['name'])) {
                    $mail->AddAttachment($file['tmp_name'], $file['name']);
                } elseif (isset($file['contents'])) {
                    $mail->AddStringAttachment($file['contents'], $file['filename']);
                } else {
                    $mail->AddAttachment($file);
                }
            }
        }

        // Messages with the X-SilverStripeMessageID header can be tracked
        if (isset($customheaders["X-SilverStripeMessageID"]) && defined('BOUNCE_EMAIL')) {
            $bounceAddress = BOUNCE_EMAIL;
            // Get the human name from the from address, if there is one
            if (ereg('^([^<>]+)<([^<>])> *$', $from, $parts)) {
                $bounceAddress = "$parts[1]<$bounceAddress>";
            }
        } else {
            $bounceAddress = $from;
        }

        $headers["X-Mailer"] = X_MAILER;
        if (!isset($customheaders["X-Priority"])) {
            $headers["X-Priority"] = 3;
        }

        $headers = array_merge((array)$headers, (array)$customheaders);

        foreach ($headers as $k => $v) {
            $mail->AddCustomHeader("$k: $v");
        }

        return $mail;
    }


    /**
     * Send a plain-text email.
     *
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $plainContent
     * @param array|bool $attachedFiles
     * @param array|bool $customheaders
     *
     * @return bool|array
     */
    public function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = false, $customheaders = false)
    {
        $mail = $this->initEmail($to, $from, $subject, $attachedFiles, $customheaders);

        // set up the body
        $mail->Body = $plainContent;

        if ($mail->Send()) {
            return array($to, $subject, $plainContent, $customheaders);
        } else {
            return $this-checkMailError($mail);
        }
    }

    /**
     * @return bool
     * @throws \Exception if he environment is dev
     */
    public function checkMailError($mail)
    {
        if (Director::isDev()) {
            throw new \Exception(sprintf(
              'PHPMailer failed: %s',
              $mail->ErrorInfo
            ));
        } else {
            return false;
        }
    }


    /**
     * Send a multi-part HTML email.
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
    public function sendHTML(
        $to,
        $from,
        $subject,
        $htmlContent,
        $attachedFiles = false,
        $customheaders = false,
        $plainContent = false,
        $inlineImages = false
    ) {
        $mail = $this->initEmail($to, $from, $subject, $attachedFiles, $customheaders);

        // set up the body
        // @todo inlineimages
        $mail->IsHTML(true);
        $mail->Body = $htmlContent;
        if ($plainContent) {
            $mail->AltBody = $plainContent;
        }

        // send and return
        if ($mail->Send()) {
            return array($to, $subject, $htmlContent, $customheaders);
        } else {
            return $this->checkMailError($mail);
        }
    }
}
