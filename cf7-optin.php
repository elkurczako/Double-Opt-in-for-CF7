<?php
/*
 * 	Plugin Name: Double opt-in for CF7
	Plugin URI: https://sirta.pl
	Description: Additional validation and double opt-in functionality for Contact Form 7 plugin.
	Version: 1.0.2
	Author: Krzysztof Busłowicz
	Text Domain: cf7-optin
	Domain Path: /languages/
	License:     GPL
	License URI: https://www.gnu.org/licenses/gpl.html
 */

defined( 'ABSPATH' ) or die( 'Cheating? 	No script kiddies please!' );

/* Plugin init */
define( 'cf7optin_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'cf7optin_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once cf7optin_PLUGIN_PATH . '/admin/cf7optin-admin.php'; 
require_once cf7optin_PLUGIN_PATH . '/inc/optin-submission-class.php';
require_once cf7optin_PLUGIN_PATH . '/inc/optin-settings-class.php';

/***
*Multilanguage support
***/
add_action('plugins_loaded', 'cf7optin_load_textdomain');
function cf7optin_load_textdomain() {
	load_plugin_textdomain( 'cf7-optin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}


/* Check if CF7 and Flamingo installed and active
*/

function cf7optin_is_cf7_active() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
        add_action( 'admin_notices', 'cf7optin_cf7plugin_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}
add_action( 'admin_init', 'cf7optin_is_cf7_active' );
function cf7optin_cf7plugin_notice(){
    ?><div class="error"><p><?php printf(esc_html_x('Sorry, but Double opt-in for CF7 Add-on requires the %1$s plugin to be installed and active.', 'plugin repository link', 'cf7-optin'), '<a href="https://wordpress.org/plugins/contact-form-7/">Contact Form 7</a>' ); ?></p></div><?php
}

function cf7optin_is_cf7db_active() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  (!is_plugin_active( 'flamingo/flamingo.php' ) && !is_plugin_active( 'contact-form-cfdb7/contact-form-cfdb-7.php'))) {
        add_action( 'admin_notices', 'cf7optin_database_notice' );
    }
}
add_action( 'admin_init', 'cf7optin_is_cf7db_active' );
function cf7optin_database_notice(){
    ?><div class="error"><p><?php printf(esc_html_x('Sorry, but Double opt-in for CF7 Add-on requires the %1$s or %2$s plugin to be installed and active. Install one of those plugins before setting up double-opt in forms.', 'Famingo and CFDB7 links', 'cf7-optin'), '<a href="https://wordpress.org/plugins/flamingo/">Flamingo</a>', '<a href="https://wordpress.org/plugins/contact-form-cfdb7/">Contact Form 7 Database Add-on – CFDB7</a>'); ?></p></div><?php
}


/* Registering frontend scripts and styles */
function cf7optin_enqueue() {
	$cf7optin_js_strings = array(
	'DefaultlWarning'		=> esc_html__('Attention! Invalid data in the field above!', 'cf7-optin'),
	'SecondEmailWarning'	=> esc_html__('Attention! This email address is different than the address entered above. Check both fields for valid email.', 'cf7-optin'),
	'FirstEmailWarning'		=> esc_html__('Attention! Email address is different than confirmation address entered below. Check both fields for valid email.', 'cf7-optin'),
	'NotEmailWarning'		=> esc_html__('Attention! Invalid email address!', 'cf7-optin')
	);
			
	wp_register_script( 'cf7optin-js',  cf7optin_PLUGIN_URL . 'inc/js/cf7optin.js', array(), '1.0');
	wp_register_style( 'cf7optin-style',  cf7optin_PLUGIN_URL . 'inc/css/cf7optin.css', array(), '1.0');
	wp_localize_script('cf7optin-js', 'cf7optinWarning', $cf7optin_js_strings);
	wp_enqueue_script('cf7optin-js');
	wp_enqueue_style('cf7optin-style');
	//scripts when settings enabled
	$cf7optin_options = get_option('cf7optin_main_settings');
	$cf7optin_inputs = $cf7optin_options['fileinput'];
	if ($cf7optin_inputs === 'true') {
		wp_register_script( 'cf7optin-input-js',  cf7optin_PLUGIN_URL . 'inc/js/cf7optin-fileinput.js', array(), '1.0');
		$cf7optin_js_fileinput_strings = array(
			'LabelDefaultText'		=> esc_html__('Select file', 'cf7-optin'),
			'SelectedFile'	=> esc_html_x('Selected: ', 'For filename to upload', 'cf7-optin'),
			'SelectedSize'		=> esc_html_x('size: ', 'the size of the file to upload', 'cf7-optin'),
			'RemoveFile'		=> esc_html__('Remove file', 'cf7-optin'),
			'NoFileSelected'		=> esc_html__('No file selected', 'cf7-optin')
		);
		wp_localize_script( 'cf7optin-input-js', 'cf7optinInput', $cf7optin_js_fileinput_strings);
		wp_enqueue_script( 'cf7optin-input-js' );
	}
}
add_action( 'wp_enqueue_scripts', 'cf7optin_enqueue');


/* Create folder for temporary storing submitted files */
$upload_dir   = wp_upload_dir();
if ( ! empty( $upload_dir['basedir'] ) ) {
    $cf7optin_uploads = trailingslashit($upload_dir['basedir'].'/cf7optin_uploads');
    if ( ! file_exists( $cf7optin_uploads ) ) {
        wp_mkdir_p( $cf7optin_uploads );
    }
	 if ( ! file_exists( $cf7optin_uploads . '.htaccess' ) ) {
		$htaccess = fopen( $cf7optin_uploads . '.htaccess', 'a');
		fwrite($htaccess, 'Deny from all');
		fclose($htaccess);
	 }
	define( 'cf7optin_UPLOAD_DIR', $cf7optin_uploads);
}

/* Create shortcode for doble opt-in confirmation page [cf7doubleoptin]
*/
add_shortcode('cf7doubleoptin','cf7optin_handle_opt_in_link');

/* Init END */

/* Email validation
*  Requires [your-email] and [confirm-email] tags in CF7 forms
*/
add_filter( 'wpcf7_validate_email*', 'cf7optin_email_confirmation_validation_filter', 20, 2 );
  
function cf7optin_email_confirmation_validation_filter( $result, $tag ) {
if ( 'confirm-email' == $tag->name ) {
	$your_email = isset( $_POST['your-email'] ) ? sanitize_text_field(trim( $_POST['your-email'] )) : '';
	$confirm_email = isset( $_POST['confirm-email'] ) ? sanitize_text_field(trim( $_POST['confirm-email'] )) : '';
  
    if ( $your_email != $confirm_email ) {
		$result->invalidate( $tag, esc_html__("Email addresses should not differ! Check the confirmation email!", 'cf7-optin') );
		}
  }
  return $result;
}

/* Custom validation messages for CF7 radios and checkboxes
*  
*/

add_filter( 'wpcf7_validate_checkbox', 'cf7optin_checkbox_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_checkbox*', 'cf7optin_checkbox_validation_filter', 10, 2 );

function cf7optin_checkbox_validation_filter( $result, $tag ) {
	$cf7optin_options = get_option('cf7optin_main_settings');
	$cf7optin_msg = $cf7optin_options['customvalidation'];
	if ($cf7optin_msg === 'true') {
		$name = $tag->name;
		$is_required = $tag->is_required() ;
		$value = isset( $_POST[$name] ) ? (array) sanitize_text_field($_POST[$name]) : array();

		if ( $is_required and empty( $value ) ) {
			//filter to customize error message 
			$message = apply_filters('cf7optin_checkbox_error_msg',  esc_html__( 'Choose at least one option.', 'cf7-optin' ));
			$result->invalidate( $tag,$message);
		}
	}
	return $result;
}
add_filter( 'wpcf7_validate_radio', 'cf7optin_radio_validation_filter', 10, 2 );

function cf7optin_radio_validation_filter( $result, $tag ) {
	$cf7optin_options = get_option('cf7optin_main_settings');
	$cf7optin_msg = $cf7optin_options['customvalidation'];
	if ($cf7optin_msg === 'true') {
		$name = $tag->name;
		$is_required = $tag->is_required() || 'radio' == $tag->type;
		$value = isset( $_POST[$name] ) ? (array) sanitize_text_field($_POST[$name]) : array();

		if ( $is_required and empty( $value ) ) {
			//filter to customize error message 
			$message = apply_filters('cf7optin_radio_error_msg', esc_html__( 'Chose one of the options.', 'cf7-optin' ) );
			$result->invalidate( $tag, $message );
		}
	}
	return $result;
}

/*	Encryption and decryption
*	Used to create safe confirmation links
* 	First parameter is string to handle. 
*	Second parameter is action to perform: if true - encryption, if false (default) - decryption
*/
function cf7optin_le_chiffre($inputStr = "",$action = false){
     $outputStr = null;
	 $template_opts = get_option('cf7optin_main_settings'); // ile godzin ustawiono ważności
	 $secret_key = $template_opts['enc_key'];
	 $secret_iv = $template_opts['enc_iv'];
     $key = hash('sha256',$secret_key);
     $iv = substr(hash('sha256',$secret_iv),0,16);
     if($action === true){
        $outputStr = base64_encode(openssl_encrypt($inputStr,"AES-256-CBC",$key,0,$iv));
     }else if($action == false){
        $outputStr = openssl_decrypt(base64_decode($inputStr),"AES-256-CBC",$key,0,$iv);
     }
     return $outputStr;
}

/* 	Checks if there is shortcode on page and 
*	if true searches for valid parameters.
*	If no params redirects to 404
*/
function cf7optin_check_opt_in_params() {
	if (!is_page('opt-in') || is_admin()) return; //making sure the following code runs only on confirmation page
	global $post;
	if (!empty($post->post_content)) {
		$regex = get_shortcode_regex();
		preg_match_all('/'.$regex.'/',$post->post_content,$matches);
		if (!empty($matches[2]) && in_array('cf7doubleoptin',$matches[2])) {
			// if no submission ID or submitter email in URL params - redirect to 404
			$url_params = cf7optin_get_url_params();
			if (!$url_params) { 
				$notfoundurl = site_url('/404/');
				wp_redirect($notfoundurl);
				exit;
			}
		} 
	}
}
add_action('template_redirect', 'cf7optin_check_opt_in_params', 1);

/* Checks for proper opt-in params in current url
*/
function cf7optin_get_url_params() {
	$aid = '';
	$aem = '';
	if (isset($_GET['aid'])) $aid = sanitize_text_field($_GET['aid']);
	if (isset($_GET['aem'])) $aem = sanitize_text_field($_GET['aem']);
	if ($aid === '' || $aem === '') {
		return false;
	}
	$params = array($aid, $aem);
	return $params;
}

/*	Main confirmation used by shortcode
	Used on page with mandatory slug "opt-in"
*/
function cf7optin_handle_opt_in_link() {
	if (is_admin()) return; // cause we're redirecting to 404 in case of not valid url params
	$url_params = cf7optin_get_url_params();
	if (!$url_params) { 
	// if no submission ID or submitter email in URL params - display error
	// Should never happen because we're checking this before displaying the page but I'm paranoid
		$message = array('alert', esc_html__('Error. Something went really wrong! No required parameters found!', 'cf7-optin'));
	} else { // If proper URL params found - decrypting and checking if there is submission in flamingo posts
		//action hook fired when url params look ok and we are displaying opt-in page 
		do_action('cf7optin_after_proper_url_params');
		$realid = cf7optin_le_chiffre($url_params[0]);
		$realem = cf7optin_le_chiffre($url_params[1]);
		$flag = false; //false means no document found
		$cf7optin_options = get_option('cf7optin_main_settings');
		$db_plugin = isset(($cf7optin_options['db_plugin'])) ? esc_attr($cf7optin_options['db_plugin']) : 'flamingo';
		if ($db_plugin === 'flamingo') { //flamingo
			$optincf7_args = array(
				'post_type'	=>	'flamingo_inbound',
				'meta_key'	=>	'_from_email',
				'meta_value' =>	$realem ,
				'compare'	=>	'=',
				'fields' => 'ids'
				);
			$optincf7query = new WP_Query($optincf7_args);
			$found = $optincf7query->posts;
			
		} else { //  cfdb7
			global $wpdb;
			$cfdb       = apply_filters( 'cfdb7_database', $wpdb );
			$table_name = $cfdb->prefix.'db7_forms';
			$found 		= $cfdb->get_results( $cfdb->prepare("SELECT * FROM $table_name WHERE `form_id` = %d", intval($realid)), OBJECT );
		}			
		foreach ($found as $submission) { // $submission - db plugin post id 
		// creating submission objects and compare with parameters 
		
			$cf7sub = new CF7OPTIN_Submission($db_plugin, $submission);	
			
			if (intval($cf7sub->get_id()) === intval($realid) && $cf7sub->get_sender() === $realem) {				
				$now_date = current_time( 'timestamp' ); //checking if not expired
				$expires = intval($cf7optin_options['expires']);// hours to expire from settings
				$extime = $expires * 3600;
				if ($now_date - $cf7sub->get_sub_date() > $extime) {
					$message = array('alert', sprintf(esc_html__('Error. This document has expired and can not be approved. More than %d hours from submission passed.', 'cf7-optin'), $expires));
				} else {
					if ($cf7sub->get_status() === '1') {
						$message = array( 'alert', esc_html__('Error. This document has been already approved.', 'cf7-optin'));
					} else {
						$message = array( '', sprintf( esc_html__('Document %1$s found.', 'cf7-optin'), $cf7sub->get_subject() ));
						//action hook fired when submission is found  
						do_action('cf7optin_before_final_email');
						
						cf7optin_handle_final_email($cf7sub); 
					}
				}
				$flag = true;
			}
		} //if parameters not found in flamingo (ie. 30 dys passed)
		if (!$flag) $message = array('alert', esc_html__('Error. There is no document to approve.', 'cf7-optin'));
	}
	$role = ''; // some ARIA tweaks for screenreader users 
	if ($message[0] !== '') $role = ' role="' . $message[0] . '"';
	$messagetodisplay ='<p' . $role . '>' . $message[1] . '</p>';
	//action hook fired when submission is processed (valid or not) just before displaying shortcode  
	do_action('cf7optin_after_submit_processing');
	return $messagetodisplay;
}

	
/* 	Encrypting tags in mail body between double curly braces - "{{tagname}}"
*	Handles $components['body'] and returns $components to CF7 mail function
*/
function cf7optin_mail_components( $components, $number ) {
	$message_body = $components[ 'body' ];
	//in case cfdb7 plugin used and no flamingo - get serial fom cfdb7 table
	$message_body = cf7optin_replace_cfdb7_serial($message_body);
		
	$starttag = strpos($message_body,'{{');
	if ($starttag) { //is this "{{tagname}}"?
		$tags = array();
		$szt = substr_count($message_body,'{{');
		$offset = 0;
		for ($i = 0 ; $i < $szt ; $i++) {
			// gets string between double curly braces and encrypts
		$firstchunk = strstr(substr($message_body,$offset),'}}',true);
			$tag = strval(substr(strstr($firstchunk,'{{'),2)); 
			$tags[$i] = array($tag => cf7optin_le_chiffre($tag, true)); 
			$offset = $offset + strlen($firstchunk) + 2;
		}
		foreach ($tags as $key => $value) {
			// replaces tags in mail body to encrypted strings
			foreach ($value as $k => $v){
				$message_body = str_replace('{{' . $k . '}}', $v, $message_body);
			}
			unset($k,$v);
		}
		unset($key, $value);
		$components['body'] = $message_body;
	}
	return $components;
};
// Hooks to CF7 mail create and adds confirmation link
add_filter( 'wpcf7_mail_components', 'cf7optin_mail_components', 10, 2 );

/*	Checks if cfdb7 plugin used and if [_serial_number] found in message body
	replaces it with last form_id value from db_foms table
*/
function cf7optin_replace_cfdb7_serial($message_body){
	
	$cf7optin_options = get_option('cf7optin_main_settings');
	$db_plugin = isset(($cf7optin_options['db_plugin'])) ? esc_attr($cf7optin_options['db_plugin']) : 'flamingo';
	
	if ($db_plugin === 'cfdb7') {
		global $wpdb; //prepare next cfdb7 serial 
		$cfdb       = apply_filters( 'cfdb7_database', $wpdb );
		$table_name = $cfdb->prefix.'db7_forms';
		$found_last	= $cfdb->get_results( "SELECT * FROM $table_name ORDER BY form_id DESC LIMIT 1", OBJECT );
		$cfdb7_serial = $found_last[0]->form_id;
		
		$serial = strpos($message_body,'[_serial_number]'); //checking case when flamingo not installed
		if ($serial) {
			$message_body = str_replace('[_serial_number]',$cfdb7_serial,$message_body);
			
		} else { //when flamingo installed and [_serial_number] has value
		
			$tag_start = strpos($message_body,'aid={{') + 6;
			$tag_end = strpos($message_body,'}}',$tag_start);
			$flamingo_serial = substr($message_body, $tag_start, $tag_end - $tag_start);
			$message_body = str_replace('aid={{'. $flamingo_serial .'}}','aid={{'. $cfdb7_serial .'}}',$message_body);
		}
	}
	return $message_body;
}


/* 	Sending final email when submission validated on opt-in page
* 	Parameter is flamingo post ID
*/
function cf7optin_handle_final_email($cf7sub) {
	
	$message = '';
	$cf7optin_options = get_option('cf7optin_main_settings');
	$form_data = $cf7sub->get_form_data(); // data from db plugin to insert in mail templates 
	$sub_id = $cf7sub->get_db_plugin_post_id();
	// selecting template assigned to form
	if ($cf7sub->get_db_plugin() === 'flamingo') {
		$settings_id = cf7optin_find_flamingo_settings($sub_id);
	} else {
		$settings_id = cf7optin_get_settings(intval($cf7sub->get_cf7_id()));
	} 
	
	if ($settings_id !== false) {
		$settings = new CF7OPTIN_Settings($settings_id); 
		$mail_subject = $settings->get_optin_mail_subject();
		$mail_body = $settings->get_optin_mail_body();
		$headers_type = $settings->get_optin_headers_type();
		$add_csv = $settings->get_optin_add_csv();
		$attachments = $settings->get_optin_attachments();
		
		// mail components - replacing tags with stored values
		$mail_subject = cf7optin_mail_tags_replace($form_data, $mail_subject);
		$mail_body = cf7optin_mail_tags_replace($form_data, $mail_body);
		$headers = ($headers_type === 'html') ? array('Content-Type: text/html; charset=UTF-8') : ''; // HTML or plain text email
		$attachments = cf7optin_get_mail_attachments($form_data, $attachments, $cf7sub->get_db_plugin());//get array of attachment file paths
		
		// add csv attachment if set in settings
		if ($add_csv === 'true') {
			$delimiter = (isset($cf7optin_options['flamingo_csv']) && $cf7optin_options['flamingo_csv'] === 'true') ? ';' : ',';
			$csv_data = cf7optin_csv_prepare($form_data);
			$csv_file_patch = cf7optin_make_csv($sub_id, $csv_data, $delimiter); 
			$attachments[] = $csv_file_patch;
		}
		if ( count($attachments) > 0 ) { //if attachments - adding info on page and in email
			$message .= sprintf(esc_html__('Your document has %d attachment files. ', 'cf7-optin'), count($attachments));
			$filenames = array();
			foreach ($attachments as $path_str) {
				$filenames[] = basename($path_str);
			}
			$filelist = implode(", ", $filenames);
			$mail_body .= '<p>'. sprintf(_n('This document has %d attachment: %s', 'This document has %d attachments: %s', count($attachments) , 'cf7-optin'), count($attachments), $filelist) . '</p>';
		}
		if ($attachments === false) {//if there should be some attachments but files not found
			$attachments = array();
			$message .= esc_html__('There was a problem with sending submitted files! Submission is valid but could not be approved. Please contact the site administrator.', 'cf7-optin');
			echo '<p>'. esc_html($message) .'</p>';
			return;
		}
		$mail_sent = wp_mail(
			//$mail_to,
			$settings->get_optin_mail_to(),
			$mail_subject,
			$mail_body,
			$headers,
			$attachments
			); //Final email
		
		if ($mail_sent) {
			$message .= esc_html__('Thank you. Your submission has been approved and sent properly.', 'cf7-optin');
			if ($cf7sub->get_db_plugin() === 'flamingo') {
				$update = update_post_meta($sub_id, '_field_accepted' , '1');
			} else {
				$update = $cf7sub->set_cfdb7_status_accepted();
			} 
			if ($update === false) { 
				$message .= esc_html__('Your submission has been approved but there was an error while processing data. Please contact the site administrator.', 'cf7-optin');
			} else {
				//confirmation email to submitter
				$mail_to = $form_data['your-email'];
				$con_subject = $settings->get_optin_con_subject();
				$con_body = $settings->get_optin_con_body();
				$con_subject = cf7optin_mail_tags_replace($form_data, $con_subject);
				$con_body = cf7optin_mail_tags_replace($form_data, $con_body);
				$con_attachment = $settings->get_optin_con_attachment();
				$attached_file = array();
				if (!empty($con_attachment)) {
					$attached_file[] = $con_attachment['file'];	
				}
				$mail_sent = wp_mail(
					$mail_to,
					$con_subject,
					$con_body,
					$headers,
					$attached_file
					); //Confirmation email
					
				if ($mail_sent === false) {
					$message .= esc_html__('Your submission has been approved but there was an error while sending confirmation message. Please contact the site administrator.', 'cf7-optin');
				} else { //All went OK - deleting attachment files if needed
					if ($cf7sub->get_db_plugin() === 'flamingo') { //TODO - setting if removing file
						foreach ($attachments as $attachment) {
							$att_dir = pathinfo($attachment);
							$att_dirname = $att_dir['dirname'];
							unlink($attachment);
							rmdir($att_dirname);  //only if flamingo db plugin
						}
					}
				}
			}
		} else {
			$message .= esc_html__('There was an error sending final email with your data. Please contact the site administrator.', 'cf7-optin');
		}
	} else { //
		$message .= esc_html__('There was an error while processing your data. Form settings could not be found. Please contact the site administrator.', 'cf7-optin');
	}
	echo '<p>'. esc_html($message) .'</p>';
}


/* 
*  Replaces flamingo post fields with tags in HTML template 
*/
function cf7optin_mail_tags_replace($form_keys, $mail_component) {
	
	foreach ($form_keys as $key => $value) {
		$pattern = '['. $key . ']';
		$mail_component = str_replace($pattern,strval($value),$mail_component);
	}
	return $mail_component;
}

/* Searching for attachment files listed in flamingo  
*  Returns paths array or false if files listed but not found 
*/
function cf7optin_get_mail_attachments($form_data, $attachments, $plugin) {
	
	$file_fields = array_filter( explode( ',' , $attachments) );
	$attachment_array = array(); 
	if (count($file_fields) > 0) {
		foreach ($file_fields as $file_field) {
			$file_field = trim($file_field);
			foreach ($form_data as $key => $value) {
				if ($value !== "") { 
					if ('flamingo' === $plugin) { //case flamigo
						if ($key === trim($file_field, '[]')) {
							$attachment_path = trailingslashit(cf7optin_UPLOAD_DIR . $value);
							$attachment_files = array_diff(scandir($attachment_path), array('..', '.'));
							if ($attachment_files) {
								$attachment_array[] = $attachment_path . $attachment_files[2];
							} else {
								return false;
							}
						}
					} else { //case cfdb7
						if (strpos($key, trim($file_field, '[]')) === 0) {
							$upload_dir    = wp_upload_dir();
							$attachment_path = $upload_dir['basedir'].'/cfdb7_uploads/';
							if (file_exists($attachment_path . $value)) {
								$attachment_array[] = $attachment_path . $value;
							} else {
								return false;
							}
						}
					}
				} 
			}
		}
	}	
	return $attachment_array;
}

/*
	*  Searching for CF7 form ID wchich was used to store flamingo post
	*  
	*  Returns post ID or false 
	*/
function cf7optin_find_flamingo_settings($sub_id) { 
	global $post;
	$optcf7_args = array(
			'post_type'	    =>	'wpcf7_contact_form',
			'posts_per_page'=>	-1
			);
	$cf7_forms = get_posts( $optcf7_args );
	$post_flamingo = get_post($sub_id);
	$flamingo_title = $post_flamingo->post_title;
	foreach ($cf7_forms as $form) {
		$form_ad_settings = get_post_meta($form->ID,'_additional_settings',true);
		$lines = preg_split("/\r\n|\n|\r/", $form_ad_settings);
		foreach ($lines as $line){
			if (strpos($line,'flamingo_subject:') !== false) {
				$flamingo_subject = trim(substr($line,17),'" ');
				if ($flamingo_subject === $flamingo_title) {
					$cf7form_id = $form->ID; //CF7 post ID
				}
			}
		}
	}
	if (isset($cf7form_id)) {	
		$setting_id = cf7optin_get_settings($cf7form_id);
		return $setting_id;
	}
	return false; 
}

/*	
	Searching for opt-in post connected to this CF7 form
	Returns settings post ID for given CF7 form 
*/
function cf7optin_get_settings($cf7form_id) {
	$settings_args = array(
			'post_type'	    =>	'cf7optin_settings',
			'posts_per_page'=>	-1
			);
	$setting_posts = get_posts( $settings_args );
	foreach ($setting_posts as $setting) {
		$settings_cf7 = get_post_meta($setting->ID, '_cf7_form', true);
		if (intval($settings_cf7) === $cf7form_id) {
			$setting_id = $setting->ID; //Opt-in post ID
			return $setting_id;
		}
	}
	return false;
}

/* Gets original form attachmen files and stores them in temporary folder
*  Identifies files by string stored later in flamingo post 
*/ 
add_action('wpcf7_before_send_mail', 'cf7optin_get_cf7_files' );
 
function cf7optin_get_cf7_files($wpcf7) {
    $submission = WPCF7_Submission::get_instance();
    $files = $submission->uploaded_files();
	$form_data = $submission->get_posted_data();
	foreach ($files as $file=>$filearray){
		$filekey = $form_data[$file];//the misterious string...
		if (! empty($filearray)) {
			$file_dir = trailingslashit(cf7optin_UPLOAD_DIR . $filekey);
			$createdir = wp_mkdir_p($file_dir);
			$copyattachment = copy($filearray[0], $file_dir.basename($filearray[0]));
		}
	}
}


/*	Gets form data array and prepares it for csv writing
*/
function cf7optin_csv_prepare ($form_data) {
	$csv_header_row = array();
	$csv_data_row = array();
	foreach ($form_data as $key => $value) {
		$csv_header_row[] = $key;
		$csv_data_row[] = $value;
	}
	$csv = array($csv_header_row, $csv_data_row);
	return $csv;
}

/*	Creates csv file in temp dir and returns file path
*	$name - name of the file and directory
*	$csv - prepared form data
*/
function cf7optin_make_csv ($name, $csv, $delimiter) {
	$file_dir = trailingslashit(cf7optin_UPLOAD_DIR . $name);
	$createdir = wp_mkdir_p($file_dir);
	$the_file = $file_dir . $name . ".csv";
	$csv_file = fopen( $the_file, "w" );
	fputs( $csv_file, ( chr(0xEF) . chr(0xBB) . chr(0xBF) ) ); //UTF8-BOM
	foreach ( $csv as $row) {
		fputcsv($csv_file, $row, $delimiter);
	}
	fclose($csv_file);
	return $the_file;
}	

//DEBUG 
//			ob_start();
//			var_dump($k);
//			$msg = ob_get_contents();
//			ob_get_clean();
//		    write_log($msg);

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}