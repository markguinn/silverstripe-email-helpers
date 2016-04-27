SilverStripe Email Helpers
==========================

[![Build Status](https://travis-ci.org/markguinn/silverstripe-email-helpers.svg)](https://travis-ci.org/markguinn/silverstripe-email-helpers)

Contains replacement Mailer object that utilizes PHPMailer to send
e-mail via SMTP instead of php's mail() function.  Optionally, TLS can
be enabled for secure communication with the SMTP server and a charset
for the e-mail encoding can be specified.  In addition, embedded CSS, plus a specified
external CSS file, can be inlined into the email's HTML.

Also includes a drop-in replacement for the Email class called
StyledHtmlEmail.  If used with HTML emails it allows you to include a style
section at the top of the email which will then be inlined as style
attributes on the actual html tags to promote better compatibility across
email clients.

## Requirements
Silverstripe 3.0+

## Installation
Install via composer:

```
composer require markguinn/silverstripe-email-helpers:dev-master
```

This module installs PHPMailer and Emogrifier:
 - https://github.com/PHPMailer/PHPMailer
 - https://github.com/jjriv/emogrifier

## Usage
### SMTP Mailer
To use the SMTP mailer add the following code to your _config.php:

```php
$encryption = 'tls'; // use tls
$charset = 'UTF-8'; // use specified charset if set
// you can specify a port as in 'yourserver.com:587'
$mailer = new SmtpMailer('yourserver.com', 'username', 'password', $encryption, $charset);
Email::set_mailer($mailer);  // or Injector::inst()->registerService($mailer, 'Mailer');
```

Alternatively, any of these can be set using the config system like so:

```
SmtpMailer:
  host: yourserver.com
  user: username
  password: password
  encryption: tls
  charset: UTF-8
```
And then in _config.php:

```php
Email::set_mailer( new SmtpMailer() );
```

### Emogrified Smtp Mailer
If you wish to embed CSS into your email's HTML then use the `EmogrifiedSmtpMailer` class.  Add the following code to your _config.php:

```php
$encryption = 'ssl'; // use ssl
$charset = 'UTF-8';
$externalcssfile = 'themes/{yourtheme}/css/externalcssfile.css';  // specify the path to your css file
$SMTPDebug = 2;  // Levels 0-4 available
$logfailedemail = true;  // Log a notice with PHPMailer's error information
$mailer = new EmogrifiedSmtpMailer('yourserver.com', 'username', 'password', $encryption, $charset, $externalcssfile, $SMTPDebug, $logfailedemail);
Email::set_mailer($mailer);  // or Injector::inst()->registerService($mailer, 'Mailer');
```

Alternatively, any of these can be set using the config system like so:

```
EmogrifiedSmtpMailer:
  host: yourserver.com
  user: username
  password: password
  encryption: ssl
  charset: UTF-8
  cssfile: 'themes/{yourtheme}/css/externalcssfile.css'
  SMTPDedug: 2
  logfailedemail: true
```
And then in _config.php:

```php
Email::set_mailer( new EmogrifiedSmtpMailer() );
```

### Styled Html Email
To use the styled email, just literally use the StyledHtmlEmail class where you'd normally use the Email class
and add a single style tag in the body of the email. For example:

```html
<style type="text/css">
.bigred {
	color: red;
	font-size: 30px;
}
</style>
Hello <span class="bigred">CUSTOMERS</span>.
```

Would be sent as:

```html
Hello <span style="color:red; font-size:30px">CUSTOMERS</span>.
```

