<?php

/*  Double Opt-in for CF7 
*	Class for storing Double opt-in enabled forms settings
**	version: 1.0.1
*/

defined( 'ABSPATH' ) or die( 'Cheating? 	No script kiddies please!' );

class CF7OPTIN_Settings {
	
	private $mail_to;
	private $mail_subject;
	private $mail_body;
	private $headers_type;
	private $add_csv;
	private $attachments;
	private $con_subject;
	private $con_body;
	private $con_attachment;

	
	public function get_optin_mail_to() {
		return $this->mail_to;
	}
	private function set_optin_mail_to($mail_to) {
		$this->mail_to = $mail_to;
	}
	
	public function get_optin_mail_subject() {
		return $this->mail_subject;
	}
	private function set_optin_mail_subject($mail_subject) {
		$this->mail_subject = $mail_subject;
	}
	
	public function get_optin_mail_body() {
		return $this->mail_body;
	}
	private function set_optin_mail_body($mail_body) {
		$this->mail_body = $mail_body;
	}
	
	public function get_optin_headers_type() {
		return $this->headers_type;
	}
	private function set_optin_headers_type($headers_type) {
		$this->headers_type = $headers_type;
	}
	
	public function get_optin_add_csv() {
		return $this->add_csv;
	}
	private function set_optin_add_csv($add_csv) {
		$this->add_csv = $add_csv;
	}
	
	public function get_optin_attachments() {
		return $this->attachments;
	}
	private function set_optin_attachments($attachments) {
		$this->attachments = $attachments;
	}
	
	public function get_optin_con_subject() {
		return $this->con_subject;
	}
	private function set_optin_con_subject($con_subject) {
		$this->con_subject = $con_subject;
	}
	
	public function get_optin_con_body() {
		return $this->con_body;
	}
	private function set_optin_con_body($con_body) {
		$this->con_body = $con_body;
	}
	
	public function get_optin_con_attachment() {
		return $this->con_attachment;
	}
	private function set_optin_con_attachment($con_attachment) {
		$this->con_attachment = $con_attachment;
	}
		
	public function __construct($settings_id) {
		$mail_to = get_post_meta($settings_id, '_reg_email',true);
		$mail_subject = get_post_meta($settings_id, '_reg_title',true);
		$mail_body = get_post_meta($settings_id, '_reg_template',true);
		$headers_type =  get_post_meta($settings_id, '_reg_headers',true);
		$add_csv =  get_post_meta($settings_id, '_csv',true);
		$attachments = get_post_meta($settings_id, '_reg_files',true);
		$con_subject = get_post_meta($settings_id, '_con_title',true);
		$con_body = get_post_meta($settings_id, '_con_template',true);
		$con_attachment = get_post_meta($settings_id, '_final_attachment',true);
		
		$this->set_optin_mail_to($mail_to);
		$this->set_optin_mail_subject($mail_subject);
		$this->set_optin_mail_body($mail_body);
		$this->set_optin_headers_type($headers_type);
		$this->set_optin_add_csv($add_csv);
		$this->set_optin_attachments($attachments);
		$this->set_optin_con_subject($con_subject);
		$this->set_optin_con_body($con_body);
		$this->set_optin_con_attachment($con_attachment);
	}	
}