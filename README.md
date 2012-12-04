SilverStripe Email Helpers
==========================

Contains replacement Mailer object that utilizes PHPMailer to send e-mail via SMTP instead of php's mail() function.
Also includes a drop-in replacement for the Email class called StyledHtmlEmail. If used with HTML emails it allows
you to include a <style> section at the top of the email which will then be inlined as style="" attributes on the
actual html tags to promote better compatibility across email clients.

## Requirements
Silverstripe 2.4+ or 3.0+

## Installation
Download this module into a folder in the root of your project. Does not require /dev/build.

## Usage
To use the SMTP mailer at the following code to your _config.php:

```php
$mailer = new SmtpMailer('yourserver.com', 'username', password');
Email::set_mailer($mailer);
```

To use the styled email, just literally use the StyledHtmlEmail class where you'd normally use the Email class
and add a single <style></style> block in the body of the email. The <style> block will be removed.

