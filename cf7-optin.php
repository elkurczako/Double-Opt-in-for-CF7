<?php
/*
 * 	Plugin Name: Double opt-in for CF7
	Plugin URI: https://sirta.pl
	Description: Additional validation and functionality to Contact Form 7 plugin. Extended validation and double opt-in. Adds custom CSS to CF7 forms.
	Version: 0.1.2
	Author: Krzysztof Busłowicz
	Author URI: https://sirta.pl
	Text Domain: cf7-optin
	Domain Path: /languages/
	License:     GPL
	License URI: https://www.gnu.org/licenses/gpl.html
 */

defined( 'ABSPATH' ) or die( 'Cheating? 	No script kiddies please!' );

/* Plugin init */
define( 'cf7optin_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'cf7optin_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once cf7optin_PLUGIN_PATH . '/admin/cf7optin-admin.php'; //settings

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
    ?><div class="error"><p><?php _e('Sorry, but Double opt-in for CF7 Addon requires the <strong>Conatct Form 7</strong> plugin to be installed and active.', 'cf7-optin'); ?></p></div><?php
}

function cf7optin_is_flamingo_active() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'flamingo/flamingo.php' ) ) {
        add_action( 'admin_notices', 'cf7optin_flamingoplugin_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}
add_action( 'admin_init', 'cf7optin_is_flamingo_active' );
function cf7optin_flamingoplugin_notice(){
    ?><div class="error"><p><?php _e('Sorry, but Double opt-in for CF7 Addon requires the <strong>Flamingo</strong> plugin to be installed and active.', 'cf7-optin'); ?></p></div><?php
}


/* Registering frontend scripts and styles */
function cf7optin_enqueue() {
	$cf7optin_js_strings = array(
	'DefaultlWarning'		=> __('Attention! Invalid data in the field above!', 'cf7-optin'),
	'SecondEmailWarning'	=> _x('Attention! Email address is different than <a href="#your-email">address set above</a>. Check both fields for valid email.', 'Do not translate "#your-email"', 'cf7-optin'),
	'FirstEmailWarning'		=> _x('Attention! Email address is different than <a href="#confirm-email">confirmation address set below</a>. Check both fields for valid email.', 'Do not translate "#confirm-email"', 'cf7-optin'),
	'NotEmailWarning'		=> __('Attention! Invalid email address!', 'cf7-optin')
	);
		
	wp_register_script( 'cf7optin-js',  cf7optin_PLUGIN_URL . 'assets/js/cf7optin.js', array(), '1.0');
	wp_register_style( 'cf7optin-style',  cf7optin_PLUGIN_URL . 'assets/css/cf7optin.css', array(), '1.0');
	wp_localize_script('cf7optin-js', 'cf7optinWarning', $cf7optin_js_strings);
	wp_enqueue_script('cf7optin-js');
	wp_enqueue_style('cf7optin-style');
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
	$your_email = isset( $_POST['your-email'] ) ? trim( $_POST['your-email'] ) : '';
	$confirm_email = isset( $_POST['confirm-email'] ) ? trim( $_POST['confirm-email'] ) : '';
  
    if ( $your_email != $confirm_email ) {
		$result->invalidate( $tag, __("Email addresses should not differ! Check the confirmation email!", 'cf7-optin') );
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
	$name = $tag->name;
	$is_required = $tag->is_required() || 'radio' == $tag->type;
	$value = isset( $_POST[$name] ) ? (array) $_POST[$name] : array();

	if ( $is_required and empty( $value ) ) {
		$result->invalidate( $tag, __( 'Choose at least one option.', 'cf7-optin' ) );
	}

	return $result;
}
add_filter( 'wpcf7_validate_radio', 'cf7optin_radio_validation_filter', 10, 2 );

function cf7optin_radio_validation_filter( $result, $tag ) {
	$name = $tag->name;
	$is_required = $tag->is_required() || 'radio' == $tag->type;
	$value = isset( $_POST[$name] ) ? (array) $_POST[$name] : array();

	if ( $is_required and empty( $value ) ) {
		$result->invalidate( $tag, __( 'Chose one of the options.', 'cf7-optin' ) );
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
	 $template_opts = get_option('cf7optin_mail_templates'); // ile godzin ustawiono ważności
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
	if (isset($_GET['aid'])) $aid = $_GET['aid'];
	if (isset($_GET['aem'])) $aem = $_GET['aem'];
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
	if (is_admin()) return; // cause we're redirecting to 404 i case of not valid url params
	$url_params = cf7optin_get_url_params();
	if (!$url_params) { 
	// if no submission ID or submitter email in URL params - display error
	//Should never happen because we're checking this before displaying page
		$message = array('alert', __('Error. Something went really wrong! No required parameters found!', 'cf7-optin'));
	} else { // If found proper URL params - decrypting and checking if there is submission in flamingo posts
		$realid = cf7optin_le_chiffre($url_params[0]);
		$realem = cf7optin_le_chiffre($url_params[1]);
		$flag = false;
		$optincf7_args = array(
			'post_type'	=>	'flamingo_inbound',
			'meta_key'	=>	'_from_email',
			'meta_value' =>	$realem ,
			'compare'	=>	'=',
			'fields' => 'ids'
			);
		$optincf7query = new WP_Query($optincf7_args);
		$found = $optincf7query->posts;
		foreach ($found as $submission) { // $submission - flamingo post ID
			$smeta = get_post_meta($submission, '_meta', false);
			$sserial = $smeta[0]["serial_number"];
			if ($sserial === intval($realid)) {
				$now_date = current_time( 'timestamp' ); //checking if not expired
				$sub_date = get_the_date('U',$submission);
				$template_opts = get_option('cf7optin_mail_templates'); // hours to expire from settings
				$expires = intval($template_opts['expires']);
				$extime = $expires * 3600;
				if ($now_date - $sub_date > $extime) {
					$message = array('alert', sprintf(__('Error. This document has expired and can not be approved. More than %d hours from submission passed.', 'cf7-optin'), $expires));
				} else {
					$status = get_post_meta($submission, '_accepted', true); //checking if already confirmed
					if ($status === '1') {
						$message = array( 'alert', __('Error. This document has been already approved.', 'cf7-optin'));
					} else {
						$subj = get_post_meta($submission, '_subject', true);
						$message = array( '', sprintf( __('Document %1$s found.', 'cf7-optin'), $subj ));
						cf7optin_handle_final_email($submission);
					}
				}
				$flag = true;
			}
		} //if parameters not found in flamingo (ie. 30 dys passed)
		if (!$flag) $message = array('alert', __('Error. There is no document to approve.', 'cf7-optin'));
	}
	$role = '';
	if ($message[0] !== '') $role = ' role="' . $message[0] . '"';
	$messagetodisplay ='<p' . $role . '>' . $message[1] . '</p>';
	return $messagetodisplay;
}

	
/* 	Encrypting tags in mail body between double curly braces - "{{tagname}}"
*	Handles $components['body'] and returns $components to CF7 mail function
*/
function cf7optin_mail_components( $components, $number ) {
	$message_body = $components[ 'body' ];
	$starttag = strpos($message_body,'{{');
	if ($starttag) { //is this "{{tagname}}"?
		$tags = array();
		$szt = substr_count($message_body,'{{');
		$offset = 0;
		for ($i = 0 ; $i < $szt ; $i++) {
			// gets string between braces and encrypts
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
// Hooks to CF7 mail create
add_filter( 'wpcf7_mail_components', 'cf7optin_mail_components', 10, 2 );

/* 	Sending final email when submission validated on opt-in page
* 	Parameter is flamingo post ID
*/
function cf7optin_handle_final_email($sub_id) {
	$form = get_post_meta($sub_id); // get flamingo post meta
	$form_subject = $form['_subject'][0];
	$form_data = array(); // data to insert in mail templates
	foreach (array_slice($form,5) as $meta_field => $meta_val){
		if ($meta_field === "_fields") break;
		$field_name = substr($meta_field,7);
		foreach ($meta_val as $key){
			$key_value = $key;
			$valarray = @unserialize($key_value);// Disabling PHP Notice
			if ($valarray !== false) {
				$key_value = implode(', ', $valarray);
			} else {
				$key_value = htmlspecialchars($key_value);
			}
			$form_data[$field_name] = $key_value ; 
		}
	}
	// selecting template assigned to form
	$settings_id = cf7optin_find_settings($sub_id); 
	// sending email - getting templates
	$mail_to = get_post_meta($settings_id, '_reg_email',true);
	$mail_subject = get_post_meta($settings_id, '_reg_title',true);
	$mail_body = get_post_meta($settings_id, '_reg_template',true);
	$headers_type =  get_post_meta($settings_id, '_reg_headers',true);
	$attachments = get_post_meta($settings_id, '_reg_files',true);
	// mail components - replacing tags with stored values
	$message = '';
	$mail_subject = cf7optin_mail_tags_replace($form_data, $mail_subject);
	$mail_body = cf7optin_mail_tags_replace($form_data, $mail_body);
	$headers = ($headers_type === 'html') ? array('Content-Type: text/html; charset=UTF-8') : ''; // HTML or plain text email 
	$attachments = cf7optin_get_mail_attachments($form_data, $attachments);//get array of attachment file paths
	if ( count($attachments) > 0 ) { //if attachments - adding info on page and in email
		$message .= sprintf(__('Your document has %d attachment files. ', 'cf7-optin'), count($attachments));
		$filenames = array();
		foreach ($attachments as $path_str) {
			$filenames[] = basename($path_str);
		}
		$filelist = implode(", ", $filenames);
		$mail_body .= sprintf(_n('<p>This document has %d attachment: %s</p>', '<p>This document has %d attachments: %s</p>', count($attachments) , 'cf7-optin'), count($attachments), $filelist);
	}
	if ($attachments === false) {//if there should be some attachments but files not found
		$attachments = array();
		$message .= __('There was a problem with sending submitted files! Submission is valid but could not be approved. Please contact the site administrator.', 'cf7-optin');
		echo '<p>'. $message .'</p>';
		return;
	}
	$mail_sent = wp_mail($mail_to,$mail_subject,$mail_body,$headers,$attachments); //Final email
	
	if ($mail_sent) {
		$message .= __('Thank you. Your submission has been approved and sent properly.', 'cf7-optin');
		$update = update_post_meta($sub_id, '_accepted' , '1');
		if ($update === false) { 
			$message .= __('Your submission has been approved but there was an error while processing data. Please contact the site administrator.', 'cf7-optin');
		} else {
			//confirmation email to submitter
			$mail_to = $form_data['your-email'];
			$mail_subject = get_post_meta($settings_id, '_con_title',true);
			$mail_body = get_post_meta($settings_id, '_con_template',true);
			$mail_subject = cf7optin_mail_tags_replace($form_data, $mail_subject);
			$mail_body = cf7optin_mail_tags_replace($form_data, $mail_body);
			$mail_sent = wp_mail($mail_to,$mail_subject,$mail_body,$headers);
			if ($mail_sent === false) {
				$message .= __('Your submission has been approved but there was an error while sending confirmation message. Please contact the site administrator.', 'cf7-optin');
			} else { //All went OK - deleting attachment files if needed
				foreach ($attachments as $attachment) {
					$att_dir = pathinfo($attachment);
					$att_dirname = $att_dir['dirname'];
					unlink($attachment);
					rmdir($att_dirname);
				}
			}
		}
	} else { //
		$message .= __('There was an error while processing your data. Please contact the site administrator.', 'cf7-optin');
	}
	echo '<p>'. $message .'</p>';
}

/* 
*  Replaces flamingo post fields with tags in HTML template 
*/
function cf7optin_mail_tags_replace($form_keys, $mail_component) {
	foreach ($form_keys as $key => $value) {
		$pattern = '['. $key . ']';
		$mail_component = str_replace($pattern,$value,$mail_component);
	}
	return $mail_component;
}

/* Searching for attachment files listed in flamingo  
*  Returns paths array or false if files listed but not found 
*/
function cf7optin_get_mail_attachments($form_keys, $attachments) {
	$file_fields = array_filter( explode( ',' , $attachments) );
	$attachment_array = array(); 
	if (count($file_fields) > 0) {
		foreach ($file_fields as $file_field) {
			$file_field = trim($file_field);
			foreach ($form_keys as $key => $value) {
				if ($value !== "") {
					if ($key === trim($file_field, '[]')) {
						$attachment_path = trailingslashit(cf7optin_UPLOAD_DIR . $value);
						$attachment_files = array_diff(scandir($attachment_path), array('..', '.'));
						if ($attachment_files) {
							$attachment_array[] = $attachment_path . $attachment_files[2];
						} else {
							return false;
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
	*  Searching for opt-in post connected to this CF7 form
	*  Returns post ID or false 
	*/
function cf7optin_find_settings($myid) {
	global $post;
	$optcf7_args = array(
			'post_type'	    =>	'wpcf7_contact_form',
			'posts_per_page'=>	-1
			);
	$cf7_forms = get_posts( $optcf7_args );
	$post_flamingo = get_post($myid);
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
	}
	return false; 
}
/* Gets form attachmen files and stores them in temporary folder
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


//DEBUG - Remove before publish
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