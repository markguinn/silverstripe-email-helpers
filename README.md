SilverStripe Email Helpers
==========================

Contains replacement Mailer object that utilizes PHPMailer to send
e-mail via SMTP instead of php's mail() function.  Optionally, TLS can
be enabled for secure communication with the SMTP server and a charset
for the e-mail encoding can be specified.

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

This module now uses composer to install PHPMailer and Emogrifier. If installing
manually, you will need to install those two dependencies and make sure they are
autoloaded or required somehow (composer does this automatically). You can find
them on github here:

 - https://github.com/PHPMailer/PHPMailer
 - https://github.com/jjriv/emogrifier

(but really you should learn how to use composer)

## Usage
To use the SMTP mailer at the following code to your _config.php:

```php
$tls = true;        // use tls authentication if true
$charset = 'UTF-8'; // use specified charset if set
// you can specify a port as in 'yourserver.com:587'
$mailer = new SmtpMailer('yourserver.com', 'username', 'password', $tls, $charset);
Email::set_mailer($mailer);
```

Alternatively, any of these can be set using the config system like so:

```
SmtpMailer:
  host: yourserver.com
  user: username
  password: password
  tls: true
  charset: UTF-8
```

And then:

```
Email::set_mailer( new SmtpMailer() );
```

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

