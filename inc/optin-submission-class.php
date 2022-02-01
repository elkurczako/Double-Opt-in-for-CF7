<?php
/* Submission object class for CF7 Double opt-in plugin 
** version 0.1
*/

defined( 'ABSPATH' ) or die( 'Cheating? 	No script kiddies please!' );

class CF7OPTIN_Submission {
	private $id;
	private $cf7_id;
	private $db_plugin_post_id;
	private $db_plugin;
	private $sender;
	private $sub_date;
	private $status;
	private $subject;
	private $form_data;
	public	$body;
	public	$files;
	
	
	public function get_id() {
		return $this->id;
	}
	private function set_id($id) {
		$this->id = $id;
	}
	
	public function get_cf7_id() {
		return $this->cf7_id;
	}
	private function set_cf7_id($cf7_id) {
		$this->cf7_id = $cf7_id;
	}
	
	public function get_db_plugin_post_id() {
		return $this->db_plugin_post_id;
	}
	private function set_db_plugin_post_id($db_plugin_post_id) {
		$this->db_plugin_post_id = $db_plugin_post_id;
	}
	
	public function get_db_plugin() {
		return $this->db_plugin;
	}
	private function set_db_plugin($db_plugin) {
		$this->db_plugin = $db_plugin;
	}
	
	public function get_sender() {
		return $this->sender;
	}
	private function set_sender($sender) {
		$this->sender = $sender;
	}
	
	public function get_sub_date() {
		return $this->sub_date;
	}
	public function set_sub_date($sub_date) {
		$this->sub_date = $sub_date;
	}
	
	public function get_status() {
		return $this->status;
	}
	public function set_status($status) {
		$this->status = $status;
	}
	
	public function get_subject() {
		return $this->subject;
	}
	public function set_subject($subject) {
		$this->subject = $subject;
	}
	
	public function get_form_data() {
		return $this->form_data;
	}
	public function set_form_data($form_data) {
		$this->form_data = $form_data;
	}
	
	public function get_body() {
		return $this->body;
	}
	public function set_body($body) {
		$this->body = $body;
	}
	
	public function get_files() {
		return $this->files;
	}
	public function set_files($files) {
		$this->files = $files;
	}
	
	public function __construct( $db_plugin , $submission) {
		$this->set_db_plugin($db_plugin);
		
		if ($db_plugin === 'flamingo') {
			$this->set_flamingo_properties($submission);
		} else {
			$this->set_cfdb7_properties($submission);
		}
	}
	
	private function set_flamingo_properties($submission) {
		$smeta = get_post_meta($submission, '_meta', false);
		$sserial = $smeta[0]["serial_number"];
		$semail = get_post_meta($submission, '_from_email', true);
		$sub_date = get_the_date('U',$submission); 
		$status = get_post_meta($submission, '_field_accepted', true); 
		$subj = get_post_meta($submission, '_subject', true);
		$form = get_post_meta($submission); // get flamingo post meta

		$form_data = array(); // data to insert in mail templates
		foreach (array_slice($form,5) as $meta_field => $meta_val){
			if ($meta_field === "_fields") break;
			$field_name = substr($meta_field,7);
			foreach ($meta_val as $key){
				$key_value = $key;
				$valarray = @unserialize($key_value);// Disabling PHP Notice when value is not serialized
				if ($valarray !== false) {
					$key_value = implode(', ', $valarray);
				} else {
					$key_value = htmlspecialchars($key_value);
				}
				$form_data[$field_name] = $key_value ; 
			}
		}
		
		$this->set_id($sserial);
		$this->set_db_plugin_post_id($submission);
		$this->set_sender($semail);
		$this->set_sub_date($sub_date);
		$this->set_status($status);
		$this->set_subject($subj);
		$this->set_form_data($form_data);	
	}
	
	private function set_cfdb7_properties($submission) {
		$sserial = $submission->form_id;
		$form_id = $submission->form_post_id;
		$form_data = unserialize($submission->form_value);
			foreach ($form_data as $key => $value) {
				if(is_array($value)) {
					$strvalue = implode(', ', $value);
					$form_data[$key] = $strvalue;
				}
			}
		$semail = $form_data['your-email'] ;
		$sub_date = strtotime($submission->form_date);
		$status = $form_data['accepted'] ;
		//$subj = $form_data['your-subject'] ;
		global $post;
		$subj = get_post($form_id)->post_title ;
		
		$this->set_id($sserial);
		$this->set_db_plugin_post_id($sserial);
		$this->set_cf7_id($form_id);
		$this->set_sender($semail);
		$this->set_sub_date($sub_date);
		$this->set_status($status);
		$this->set_subject($subj);
		$this->set_form_data($form_data);	
	}
	public function set_cfdb7_status_accepted(){
		$form_data = $this->get_form_data();
		$form_data['accepted'] = '1';
		$this->set_form_data($form_data);
		
		$cfdb7_form_value = serialize($this->get_form_data());
		global $wpdb;
		$cfdb       = apply_filters( 'cfdb7_database', $wpdb );
		$table_name = $cfdb->prefix.'db7_forms';
		$data = ['form_value' => $cfdb7_form_value];
		$where = ['form_id' => $this->get_id()];
		$update		= $cfdb->update($table_name, $data, $where );
		return $update;
	}
}