<?php

/*  Double Opt-in for CF7 
*	Class for storing Double opt-in enabled forms settings
*/



/* Main double opt-in enabled form settings class
*/ 
class $optin_form {
	public $name;
	public $form;
	public $mail_format = 'html';
	public $mail_to;
	public $mail_subject = '[your_subject]';
	public $attachments;
	public $mail_body = CF7OPTIN_DEFAULT_BODY;
	public $confirm_subject = sprintf(__('Your form with subject %s has beeen submitted', 'cf7-optin'), '[your-subject]');
	public $confirmation_body = CF7OPTIN_DEFAULT_CONFIRMATION;

/* Default body of final email after succesful opt-in
*/
	const CF7OPTIN_DEFAULT_BODY = __('Sender: ', 'cf7-optin') . '[your-name] <[your-email]>
' . __('Subject: ', 'cf7-optin') . '[your-subject]

' . __('Message:', 'cf7-optin') . '
[your-message]';

/*Default body of final confirmation email after succesful opt-in
*/
	const CF7OPTIN_DEFAULT_CONFIRMATION = sprintf(__('This email confirms that we have received message with subject %1$s sent by %2$s from email %3$s', 'cf7-optin') , '<strong>[your-subject]</strong>' , '<strong>[your-name]</strong>', '<strong>[your-email]</strong>');

	public function get_optin_name() {
		return $this->name;
	}
	public function get_optin_form() {
		return $this->form;
	}
	public function get_optin_mail_format() {
		return $this->mail_format;
	}
	public function get_optin_mail_to() {
		return $this->mail_to;
	}
	public function get_optin_mail_subject() {
		return $this->mail_subject;
	}
	public function get_optin_attachments() {
		return $this->attachments;
	}
	public function get_optin_mail_body() {
		return $this->mail_body;
	}
	public function get_optin_mail_confirm_subject() {
		return $this->confirm_subject;
	}
	public function get_optin_confirmation_body() {
		return $this->confirmation_body;
	}
	
	
	public function set_optin_name($name); {
		$this->name = esc_attr($name);
	}
	public function set_optin_form($form); {
		$this->form = intval($form);
	}
	public function set_optin_mail_format($mail_format); {
		$this->mail_format = esc_attr($mail_format);
	}
	public function set_optin_mail_to($mail_to); {
		$this->mail_to = esc_html($mail_to);
	}
	public function set_optin_mail_subject($mail_subject); {
		$this->mail_subject = esc_html($mail_subject);
	}
	public function set_optin_attachments($attachments); {
		$this->attachments = esc_html($attachments);
	}
	public function set_optin_mail_body($mail_body); {
		$this->mail_body = esc_html($mail_body);
	}
	public function set_optin_confirm_subject($confirm_subject); {
		$this->confirm_subject = esc_html($confirm_subject);
	}
	public function set_optin_confirmation_body($confirmation_body); {
		$this->confirmation_body = esc_html($confirmation_body);
	}
	
	public function __construct() {
		
}