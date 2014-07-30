SilverStripe Email Helpers
==========================

Contains replacement Mailer object that utilizes PHPMailer to send e-mail
via SMTP instead of php's mail() function.  Optionally, TLS can be used for
secure communication with the SMTP server.

Also includes a drop-in replacement for the Email class called
StyledHtmlEmail.  If used with HTML emails it allows you to include a style
section at the top of the email which will then be inlined as style
attributes on the actual html tags to promote better compatibility across
email clients.

## Requirements
Silverstripe 2.4+ or 3.0+

## Installation
Download this module into a folder in the root of your project. Does not require /dev/build.

## Usage
To use the SMTP mailer at the following code to your _config.php:

```php
$tls = true; // use tls authentication if true
             // you can specify a port as in 'yourserver.com:587'
$mailer = new SmtpMailer('yourserver.com', 'username', 'password', $tls);
Email::set_mailer($mailer);
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

