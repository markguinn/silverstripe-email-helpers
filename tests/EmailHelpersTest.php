<?php

/**
 * PHPUnit Tests for Silverstripe Email Helpers
 *
 * @package Silverstripe Email Helpers
 * @subpackage tests
 */
class EmailHelpersTest extends SapphireTest
{
    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testConfiguration()
    {
        $mailer = new SmtpMailer();
        // test default/unconfigured class
        $this->assertNull($mailer->getHost());
        $this->assertNull($mailer->getEncryption());
        $this->assertNull($mailer->getCharset());
        $this->assertNull($mailer->getPort());

        Config::inst()->update('SmtpMailer', 'host', 'example.com');
        Config::inst()->update('SmtpMailer', 'port', 123);
        Config::inst()->update('SmtpMailer', 'password', 'dummyPass');
        Config::inst()->update('SmtpMailer', 'encryption', 'ssl');
        Config::inst()->update('SmtpMailer', 'charset', 'latin');

        $mailer = new SmtpMailer();
        $this->assertEquals('example.com', $mailer->getHost());
        $this->assertEquals('ssl', $mailer->getEncryption());
        $this->assertEquals(123, $mailer->getPort());
        $this->assertEquals('latin', $mailer->getCharset());

        // Subclass will inherit config
        $mailer = new EmogrifiedSmtpMailer();
        $this->assertEquals('example.com', $mailer->getHost());
        $this->assertEquals('ssl', $mailer->getEncryption());
        $this->assertEquals(123, $mailer->getPort());
        $this->assertEquals('latin', $mailer->getCharset());

        // but subclass can have a separate config as well
        Config::inst()->update('EmogrifiedSmtpMailer', 'port', 444);

        $mailer = new EmogrifiedSmtpMailer();
        $this->assertEquals(444, $mailer->getPort());

        // mixed values from constructor and config
        $mailer = new SmtpMailer('dummy.com', 'user', 'pass', 'tls');
        $this->assertEquals('dummy.com', $mailer->getHost());
        $this->assertEquals('tls', $mailer->getEncryption());
        $this->assertEquals(123, $mailer->getPort());
        $this->assertEquals('latin', $mailer->getCharset());

        Config::inst()->update('SmtpMailer', 'tls', true);
        // this should throw an exception (deprecated notice)
        new SmtpMailer();
    }

    public function testSmtpMailerSetup()
    {
        // PHPMailer setup
        $mailer = new SmtpMailer('yourserver.com:587', 'username', 'password', true, 'UTF-8');
        Injector::inst()->registerService($mailer, 'Mailer');

        $smtpmailer = Email::mailer();
        $this->assertEquals('SmtpMailer', get_class($smtpmailer), "SmtpMailer class is used for sending emails");
        $this->assertEquals('tls', $smtpmailer->getEncryption(), "tls is set to true as set in Injector");
        $this->assertContains('UTF-8', $smtpmailer->getCharset(), "Charset set to UTF-8 as set in Injector");
    }

    public function testEmogrifiedSmtpMailerSetup()
    {
        // PHPMailer setup
        $mailer = new EmogrifiedSmtpMailer('yourserver.com:587', 'username', 'password', true, 'UTF-8', 'silvershop/css/order.css', 1, true);
        Injector::inst()->registerService($mailer, 'Mailer');

        $emogrifiedsmtpmailer = Email::mailer();
        $this->assertEquals('EmogrifiedSmtpMailer', get_class($emogrifiedsmtpmailer), "EmogrifiedSmtpMailer class is used for sending emails");
        $this->assertEquals('tls', $emogrifiedsmtpmailer->getEncryption(), "tls is set to true as set in Injector");
        $this->assertSame('UTF-8', $emogrifiedsmtpmailer->getCharset(), "Charset set to UTF-8 as set in Injector");
        $this->assertSame('silvershop/css/order.css', $emogrifiedsmtpmailer->getCSSfile(), 'The CSS file is set to silvershop/css/order.css');
        $this->assertSame(1, $emogrifiedsmtpmailer->getSMTPDebug(), 'SMTPDebug is set to 1');
        $this->assertTrue($emogrifiedsmtpmailer->getLogfailedemail(), "Failed emails to be logged");

    }

    public function testInlineCSS()
    {
        // Get HTML file from Fixtures
        $fileLocation = join(DIRECTORY_SEPARATOR, array(__DIR__, 'fixtures/testhtml.html'));
        $fileHandler = fopen($fileLocation, 'r');
        $htmlContent = fread($fileHandler, filesize($fileLocation));
        fclose($fileHandler);

        // Note reference to external css file
        $inlinedCSS = InlineCSS::convert($htmlContent, 'email-helpers/tests/fixtures/externalcssfile.css');

        $this->assertContains('<body style="font-family: Helvetica,Arial,sans-serif; font-size: 14px; line-height: 1.6em; margin: 0; width: 100% !important; height: 100%;">', $inlinedCSS, 'Body element contains inline styling');
        $this->assertContains('<table id="Content" cellspacing="0" cellpadding="0" summary="Order Information" style="text-align: left; margin: auto; padding-left: 20px;">', $inlinedCSS, 'Table element contains inline styling');
        $this->assertContains('<tr class="itemRow" style="background-color: red;">', $inlinedCSS, 'Table row contains inline styling');
        $this->assertContains('<td class="image" style="border: 1px;"></td>', $inlinedCSS, 'Table cell contains inline styling');
    }
}
