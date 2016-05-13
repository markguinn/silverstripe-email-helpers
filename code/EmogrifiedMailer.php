<?php
/**
 * This is a simple extension of the built in SS email class
 * that uses the PHPMailer library to send emails via mail() and Emogifier to inline CSS.
 *
 * Usage: (in mysite/_config.php)
 *
 * @example  $mailer = new EmogrifiedMailer('UTF-8');
 * Email::set_mailer($mailer);
 * @package smtpmailer
 */
class EmogrifiedMailer extends Mailer
{
	/**
     * @var string $charset - charset for mail message
     */
    protected $charset;
	
    /**
     * CSS file containing classes to inline into the email's HTML
     *
     * @var string $cssfile path to css file from project root
     */
    protected $cssfile;

    /**
     * creates and configures the mailer
     */
    public function __construct($charset=false, $cssfile=false)
    {
        if ($charset === false) {
            $charset = Config::inst()->get('SmtpMailer', 'charset');
        }
        $this->setCharset($charset);

        if ($cssfile === false) {
            $cssfile = Config::inst()->get('EmogrifiedMailer', 'cssfile');
        }
        $this->setCSSfile($cssfile);
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
     * creates a new phpmailer object
     */
	protected function initMailer()
    {
        $mail = new PHPMailer();
        $mail->isMail();

        if ($this->charset) {
            $mail->CharSet = $this->charset;
        }
        
        return $mail;
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
        
        $headers["X-Mailer"]    = X_MAILER;
        if (!isset($customheaders["X-Priority"])) {
            $headers["X-Priority"]    = 3;
        }
    
        $headers = array_merge((array)$headers, (array)$customheaders);

        foreach ($headers as $k => $v) {
            $mail->AddCustomHeader("$k: $v");
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
     * Send a plain-text email.
     *
     * @param string     $to
     * @param string     $from
     * @param string     $subject
     * @param string     $plainContent
     * @param array|bool $attachedFiles
     * @param array|bool $customheaders
     *
     * @return bool
     */
    public function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = false, $customheaders = false)
    {
        $mail = $this->initEmail($to, $from, $subject, $attachedFiles, $customheaders);
        
        // set up the body
        $mail->Body = $plainContent;
        
        // send and return
        if ($mail->Send()) {
            return array($to,$subject,$plainContent,$customheaders);
        } else {
            return false;
        }
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

        // send and return
        if ($mail->Send()) {
            return array($to, $subject, $mail->Body, $customheaders);
        } else {
            return false;
        }
    }
}

