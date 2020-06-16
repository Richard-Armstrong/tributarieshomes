<?php

if (!function_exists('notify_account')) {
	function notify_account($user, $subject, $message) {
		$instance =& get_instance();
		$email_sent = TRUE;
		if ($user->notify_email) {
			$instance->load->library('email');

			$config['protocol'] = 'SMTP';
			$config['smtp_host'] = 'smtp.sparkpostmail.com';
			$config['smtp_user'] = 'SMTP_Injection';
			$config['smtp_pass'] = '3c72f548-d579-4c56-a04c-5126990ae253';
			$config['smtp_crypto'] = 'tls';
			$config['smtp_port'] = '587';
			$instance->email->initialize($config);
			$instance->email->set_newline("\r\n");

			$instance->email->from('noreply@bluwaveforms.com', 'BluWave Forms');
			$instance->email->to($user->email);
			$instance->email->subject($subject);
			$instance->email->message($message);

			$email_sent = $instance->email->send();
		}

		if ($user->notify_sms) {
			$instance->load->library('twilio');

			$from = '+' . SMS_SENDER; // twilio number
			$to = '+1' . $user->phone; // sms recipient number
			// Attempt to send the SMS and return the error if one occurs
			$response = $instance->twilio->sms($from, $to, $message);
			if (isset($response->IsError))
				return $response->ErrorMessage;
		}
		// Print email errors if they are present
		if (!$email_sent)
			return $instance->email->print_debugger();
		// Return TRUE if no errors occurred
		return TRUE;
	}
}
