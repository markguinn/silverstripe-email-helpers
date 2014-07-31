<?php
/**
 * Sends a test email to verify that they're going through.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 02.24.2014
 * @package apluswhs.com
 * @subpackage tasks
 */
class TestEmailTask extends BuildTask
{
	protected $title = "Send Test a Email";
	protected $description = "Sends a test email to verify that they're going through.";

	public function run($request) {
		if (php_sapi_name() === 'cli' || Permission::check('ADMIN')) {
			$from = Config::inst()->get('Email', 'admin_email');
			$to = isset($_GET['to']) ? $_GET['to'] : Config::inst()->get('Email', 'admin_email');
			$email = new StyledHtmlEmail($from, $to, 'Test Email Task', '<style>.red { color:red }</style><p>This is a test to see if emails are being sent.</p><p class="red">This should be red.</p>');
			$email->send();
			echo "done. sent from $from to $to.\n\n";
		} else {
			echo "must be admin\n\n";
		}
	}
}