<?php
/* Admin scripts for Double opt-in for CF7 plugin
** Adds custom post type to store double opt in settings of individual CF7 forms 
*/

/* 	Double opt-in for CF7 Settings - admin menu
 */
	$cf7_optin_page = '';
function cf7optin_add_settings_page() {
  global  $cf7_optin_page;
	$cf7_optin_page  = add_submenu_page( 'wpcf7', __('Double Opt-in Settings', 'cf7-optin'), __('Double Opt-in Settings', 'cf7-optin'), 'manage_options', 'wpcf7-optin', 'cf7optin_render_settings_page' );
	
}
add_action( 'admin_menu', 'cf7optin_add_settings_page' );


/* Register admin scripts and styles */ 

function cf7optin_enqueue_admin_script( $hook ) {
	//var_dump($hook);
	$good_to_go = false;
    $cf7_optin_cpt = 'cf7optin_settings';
	if( in_array($hook, array('post.php', 'post-new.php') ) ){
        $screen = get_current_screen();

        if( is_object( $screen ) && $cf7_optin_cpt == $screen->post_type ){
			$good_to_go = true;
		}
	}
	if (strpos($hook, '_page_wpcf7') !== false)  $good_to_go = true; 
	if ($good_to_go) {
		$cf7optin_admnin_js_strings = array(
			'OptInEnabled'			=> __('This is Double Opt-In ready form. For this feature to work set final emails in "Double Opt-in Forms" menu.', 'cf7-optin'),
			'EnterTitle'			=> __('Enter the form title first!', 'cf7-optin'),
			'FormUpdated'			=> __('Your form has been updated with Double Opt In options!', 'cf7-optin'),
			'ConfirmEmail'		 	=> __('Confirm email address ', 'cf7-optin'),
			'FinalizeSubmission'	=> __('To finalize form submission - visit: ', 'cf7-optin'),
			'KeyNotEmpty'			=> __('Important notice! You are about to replace existing encryption keys with new ones. If there are unfinished submissions pending, they can&apos;t be completed with changed keys!

', 'cf7-optin'),
			'KeysCopied'			=> __('New encryption keys are set. You have to save the settings for the change to take effect.', 'cf7-optin')
			);
		wp_register_style( 'cf7optin-admin-style', cf7optin_PLUGIN_URL . 'assets/css/cf7optin-admin.css', array(), '1.0' );
		wp_register_script( 'cf7optin-admin-js',  cf7optin_PLUGIN_URL . 'assets/js/cf7optin-admin.js', array(), '1.0');
		wp_localize_script('cf7optin-admin-js', 'cf7optinAdminText', $cf7optin_admnin_js_strings);
		wp_enqueue_style( 'cf7optin-admin-style' );
		wp_enqueue_script('cf7optin-admin-js');
	} else {
		return;
	}
}
add_action( 'admin_enqueue_scripts', 'cf7optin_enqueue_admin_script' );

/* 
*  Double opt-in for CF7 settings page
*/
function cf7optin_render_settings_page() {
    ?>
    <h2><?php _e('CF7 Double Opt-in Settings', 'cf7-optin'); ?></h2>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'cf7optin_options' );
        do_settings_sections( 'wpcf7-optin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save settings', 'cf7-optin' ); ?>" />
    </form>
    <?php  do_settings_sections( 'wpcf7-optin-help' ); 
}

// Double opt-in for CF7 plugin main settings
function cf7optin_register_settings() {
    register_setting( 'cf7optin_options', 'cf7optin_mail_templates', 'cf7optin_options_validate' );
	
    add_settings_section( 
		'cf7optin_templates', 
		__('General plugin settings','cf7-optin'), 
		'cf7optinmain_section_text',
		'wpcf7-optin' 
	);

    add_settings_field( 
		'cf7optin_registration_expiration', 
		__('How many hours to confirm with link in email before submission expires?', 'cf7-optin'), 
		'cf7optin_expiration', 
		'wpcf7-optin', 
		'cf7optin_templates' 
	);
	add_settings_field( 
		'cf7optin_encrypt_key', 
		__('Encryption key', 'cf7-optin'), 
		'cf7optin_encryption_key', 
		'wpcf7-optin', 
		'cf7optin_templates' 
	);
	add_settings_field( 
		'cf7optin_encrypt_iv', 
		__('Encryption initialization vector', 'cf7-optin'), 
		'cf7optin_encryption_iv', 
		'wpcf7-optin', 
		'cf7optin_templates' 
	);
	add_settings_section( 
		'cf7optin_options_help', 
		__('Basic Doble Opt In help','cf7-optin'), 
		'cf7optinmain_help',
		'wpcf7-optin-help' 
	);
	
	
}
add_action( 'admin_init', 'cf7optin_register_settings' );

// TODO - validate the settings before saving !!!!!
function cf7optin_options_validate( $input ) {
    $newinput = $input;
    return $newinput;
}


// Options field construct
function cf7optin_expiration() {
	$cf7optin_options = get_option('cf7optin_mail_templates');
	echo '<p>';
	echo '<input type="number" min="0" step="1" id="cf7optin-reg-expire" name="cf7optin_mail_templates[expires]" autocomplete="off" value="' . esc_attr( $cf7optin_options['expires'] ) . '">';
	echo '</p>'; 
}
function cf7optinmain_section_text() {
	$cf7optin_options = get_option('cf7optin_mail_templates');
	$newkey = bin2hex(random_bytes(16));
	$newiv = bin2hex(random_bytes(16));
	echo '<p>' . __('Auto generated Key: ', 'cf7-optin') . cf7optin_elem($newkey) . '</p>';
	echo '<p>' . __('Auto generated Initialization Vector: ', 'cf7-optin') . cf7optin_elem($newiv) . '</p>';
	echo '<p>' . __('Copy those strings to Key and Initialization Vector fields below or enter your own encryption strings.', 'cf7-optin') . '</p>';
	echo '<p><a href="#" id="cf7optin-copy-keys" class="button button-primary">' . __( 'Copy encryption keys', 'cf7-optin') . '</a></p>';
	echo '<strong>' . __('Important! Changing the keys below when there are unaccepted opt-ins will make them unacceptable.', 'cf7-optin') . '</strong>';
}
function cf7optin_encryption_key(){
	$cf7optin_options = get_option('cf7optin_mail_templates');
	echo '<p>';
	echo '<input type="text" size="40" id="cf7optin-enc-key" name="cf7optin_mail_templates[enc_key]" autocomplete="off" value="' . esc_attr( $cf7optin_options['enc_key'] ) . '">';
	echo '</p><p>' . __('Strings used to encrypt double opt in confirmation link','cf7-optin') . '</p>'; 
}
function cf7optin_encryption_iv(){
	$cf7optin_options = get_option('cf7optin_mail_templates');
	echo '<p>';
	echo '<input type="text" size="40" id="cf7optin-enc-iv" name="cf7optin_mail_templates[enc_iv]" autocomplete="off" value="' . esc_attr( $cf7optin_options['enc_iv'] ) . '">';
	echo '</p>'; 
}
function cf7optinmain_help() {
	$subject_str = 'flamingo_subject: "' . __('Enter your double opt-in form name here', 'cf7-optin') . '"';
	echo '<div class="card fullwidth">';
	echo '<h3>'. __('What is double opt in and why use it:', 'cf7-optin') . '</h3>';
	echo '<p>' . __('Double opt-in is the safe way of receiving site visitor&apos;s submitted input. Used with contact forms or online questionnaires or with user registration, it helps to highly <strong>reduce spam submissions</strong>. With <strong>GDPR laws</strong>, double opt-in is a strongly recommended way of getting user data. Because submitters has to confirm their identity from their email address, you can reduce risk of processing personal data without permission. Double - means here that user submitting the data has to give consent by checking checkbox option and additionally confirm their email-address. Do not forget to set acceptance field in your CF7 form if GDPR is in concern!', 'cf7-optin') . '</p>';
	echo '<h3>'. __('How Double opt in form works:', 'cf7-optin') . '</h3>';
	echo '<p>' . __('When someone fills and submits a form with double opt in functionality <strong>the first CF7 email is end back to THEM</strong>. There is confirmation link in that email wchich submitter has to click on or paste it in their web browser. The link has two encrypted parameters: submission serial number and submitter email address. The page with "opt-in" slug validates the parameters with submission stored in flamingo plugin and initiates sending of final emails to recipient set in "Doube Opt In Forms" settings.', 'cf7-optin') ;
	echo '<br />' . __('If validation fails or when submission has expired, no emails are sent. If there are no specified parameters in url, wisitors of the "opt-in" page are redirected to "404" page.', 'cf7-optin') . '</p>'; 
	echo '<h3>'. __('Double Opt-in for CF7 requirements: ','cf7-optin') .'</h3>';
	echo '<p class="point">' . sprintf(__('Double opt in requires %1$s and  %2$s plugins by %3$s to be installed and active.', 'cf7-optin'), '<strong>Contact Form 7</strong>', '<strong>Flamingo</strong>', 'Takayuki Miyoshi' ) . '</p>'; 
	echo '<p class="point">' . __('For proper submitter email validation you should use following tags in your CF7 forms: ','cf7-optin') . cf7optin_elem('[your-email]') . ', ' . cf7optin_elem('[confirm-email]') . __('That is important part because their email will be used for sending confirmation link. Typo in email address means they will not be able to confirm and submit the form.', 'cf7-optin') . '</p>';
	echo '<p class="point">' . sprintf(__('Double opt in requires %s tag to be set as email address of the first CF7 emial recipient.', 'cf7-optin'), cf7optin_elem('[your-email]') ) . '</p>';   
	echo '<p class="point">' . __('For doble opt-in to work correctly insert following confirmation link in first CF7 email: ','cf7-optin'). cf7optin_elem('[_site_url]/opt-in?aid={{[_serial_number]}}&aem={{[your-email]}}') . '<br/>' . __('Tags inside double curly braces will be encrypted.','cf7-optin') .'</p>';
	echo '<p class="point">' . sprintf(__('You have to set following lines on Additional Settings pane: %1$s and %2$s which is required by link validation mechanism.', 'cf7-optin') , cf7optin_elem('flamingo_email: "[your-email]"') , cf7optin_elem($subject_str)) . '</p>';
	echo '</div>';
}
/* 
	END global options
*/

/* 
*	Information about added functionality on CF7 editor pages
*/
function action_wpcf7_admin_notices(  ) { 
	if ((isset($_GET['page'] )&& $_GET['page'] === 'wpcf7-new') || (isset($_GET['page']) && $_GET['page'] === 'wpcf7' && isset($_GET['action']) && $_GET['action'] === 'edit')) {
	echo '<div class="cf7optin-cf7-notice">';
	echo '<p>' . __('You can make this form double opt in ready clicking the button below. This will add necessary shortcodes and settings to current form fields. You can move or wrap form and email shortcodes with HTML afterwards. Check help page for more details', 'cf7-optin') . '</p>';
	echo '<p><button id="cf7optin-maker" class="button button-primary">' . __('Make this Double Opt In form', 'cf7-optin') . '</button></p>';
	echo '</div>';
	}
}; 
add_action( 'wpcf7_admin_notices', 'action_wpcf7_admin_notices', 10, 0 );

/* 
* Custom post type for storing CF7 forms opt-in data
*/
function cf7optin_custom_post_type() {
	$labels = array(
        'name'                => _x( 'Opt-In Forms', 'Post Type General Name', 'cf7-optin' ),
        'singular_name'       => _x( 'Opt-In Form', 'Post Type Singular Name', 'cf7-optin' ),
        'menu_name'           => __( 'Opt-In Forms', 'cf7-optin' ),        
        'all_items'           => __( 'All Opt-In Forms', 'cf7-optin' ),        
        'add_new_item'        => __( 'Add New Opt-In Form', 'cf7-optin' ),
        'add_new'             => __( 'Add New', 'cf7-optin' ),
        'edit_item'           => __( 'Edit Opt-In Form', 'cf7-optin' ),
        'update_item'         => __( 'Update Opt-In Form', 'cf7-optin' ), 
    );
     
    $args = array(
        'label'               => __( 'Opt-In Forms', 'cf7-optin' ),
        'description'         => __( 'Settings for CF7 Forms with double opt-in functionality', 'cf7-optin' ),
        'labels'              => $labels,
        'supports'            => array('title'),
        'hierarchical'        => false,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => 'wpcf7',
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => false,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' 			=> false,
		'publicly_queryable'	=> false,
		'register_meta_box_cb'	=> 'cf7optin_reg_settings_metaboxes',
 
    );
    register_post_type( 'cf7optin_settings', $args );
}
add_action('init', 'cf7optin_custom_post_type');

/* 
*  Callback function creating custom metabox 
*/ 
function cf7optin_reg_settings_metaboxes($post) {
	add_meta_box('cf7-form-select', __('Opt-In Form Settings', 'cf7-optin' ), 'cf7optin_form_metabox', 'cf7optin_settings', 'normal', 'default');
}

/* 
*  Metabox on Double opt-in for CF7 settings page 
*/
function cf7optin_form_metabox() {
	global $post;
	$optincf7_args = array(
			'post_type'	    =>	'wpcf7_contact_form',
			'posts_per_page'=>	-1
			);
	$cf7_forms = get_posts( $optincf7_args );
	$selectedform = get_post_meta($post->ID, '_cf7_form', true); //associated cf7 form
	$selectedcf7 = ($selectedform !== '' && $selectedform !== '0') ? get_the_title(intval($selectedform)) : 'brak powiązania';
	$mail_format = 	get_post_meta($post->ID, '_reg_headers', true);
	$registration_email = get_post_meta($post->ID, '_reg_email', true); //email to send accepted forms to
	$registration_title = get_post_meta($post->ID, '_reg_title', true); 
	$registration_files = get_post_meta($post->ID, '_reg_files', true); 
	$registration_template = get_post_meta($post->ID, '_reg_template', true); 
	$confirmation_title = get_post_meta($post->ID, '_con_title', true); 
	$confirmation_template = get_post_meta($post->ID, '_con_template', true); 
	wp_nonce_field( 'cf7optin_select_cf7form', 'select_cf7form_nonce' );
    	
	?>
	<h1><?php _e('Double Opt-In Form Settings','cf7-optin'); ?> - <?php echo esc_html($selectedcf7); ?></h1>
	<?php
	if ($selectedform !== '' && $selectedform !== '0') { //dispalying cf7 shortcodes
		cf7optin_display_cf7form_fields($selectedform);
	}
	// nice place for displaying debug info - not used now
	
	?>
	<table class="form-table cf7optin-options" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><?php _e('Choose the existing CF7 form','cf7-optin'); ?></th>
				<td>
					<p><select name="cf7forms" id="cf7forms">
						<option value="0" >-- <?php _e('Choose form','cf7-optin'); ?> --</option>
						<?php // option input filled with found cf7 forms
						foreach ($cf7_forms as $cf7_form) {	?>
						  <option value="<?php echo(esc_attr($cf7_form->ID));?>" <?php  if (intval(esc_attr($cf7_form->ID)) === intval($selectedform)) echo 'selected="selected"'; ?> ><?php echo(esc_attr($cf7_form->post_title)); ?></option>
						<?php } ?>  
					</select></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('How to format emails?', 'cf7-optin'); ?>
				<td>
					<p>
						<label><?php _e("HTML format", 'cf7-optin'); ?> 
							<input type="radio" id="email_format" name="email_format" value="html" <?php if ($mail_format === 'html' || $mail_format === '') echo 'checked'; ?>>
						</label>
						<label><?php _e("Plain text format", 'cf7-optin'); ?> 
							<input type="radio" id="email_format" name="email_format" value="text" <?php if ($mail_format === 'text') echo 'checked'; ?>>
						</label>
					</p>
				</td>
			<tr>
				<th scope="row"><?php _e('Final email address', 'cf7-optin'); ?><br/><span style="font-weight:400;"><?php _e('The address your confirmed forms will be sent to','cf7-optin'); ?></span></th>
				<td>
					<p><input type="text" size="200" id="registration_email" name="registration_email" autocomplete="email" value="<?php echo esc_html( $registration_email ) ; ?>"></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Final email subject', 'cf7-optin'); ?></th>
				<td>
					<p><input type="text" size="200" id="registration_title" name="registration_title" autocomplete="on" value="<?php echo esc_html( $registration_title ) ; ?>"></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Final email attachments', 'cf7-optin'); ?><br/><span style="font-weight:400;"><?php _e('Enter comma separated attachments tags if available.', 'cf7-optin'); ?></span></th>
				<td>
					<p><input type="text" size="200" id="registration_files" name="registration_files" autocomplete="on" value="<?php echo esc_html( $registration_files ) ; ?>"></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Final email template', 'cf7-optin'); ?><br/><span style="font-weight:400;"><?php _e('Use available mail tags and format your final email.', 'cf7-optin'); ?></span></th>
				<td>
					<p><textarea id="registration_template" class="large-text code" name="registration_template" cols="100" rows="18" spellcheck="false" data-gramm="false"><?php echo esc_html( $registration_template ) ; ?></textarea></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Confirmation email subject', 'cf7-optin'); ?></th>
				<td>
					<p><input type="text" size="200" id="confirmation_title" name="confirmation_title" autocomplete="on" value="<?php echo esc_html( $confirmation_title ) ; ?>"></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Confirmation email template', 'cf7-optin'); ?><br><span style="font-weight:400;"><?php _e('Sent to email of person who submitted the original form.', 'cf7-optin'); ?></span></th>
				<td>
					<p><textarea id="confirmation_template" class="large-text code" name="confirmation_template" cols="100" rows="18" spellcheck="false" data-gramm="false"><?php echo esc_html( $confirmation_template ) ; ?></textarea></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}

/* 
* Saving settings for cf7 form 
*/
add_action('save_post', 'cf7optin_save_form_meta', 1, 2);

function cf7optin_save_form_meta($post_id, $post) {
    if ( ! isset( $_POST['select_cf7form_nonce'] ) ) {
            return $post_id;
        }
	 $nonce = $_POST['select_cf7form_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'cf7optin_select_cf7form' ) ) {
		return $post_id;
	}
	 /* Nothing to do when autosave occurs      */
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	// Checking user priviledges
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
	}
	 /* Sanitizing and saving*/
	$formselected = sanitize_text_field( $_POST['cf7forms'] );
	update_post_meta( $post_id, '_cf7_form', $formselected );
	
	$email_format = sanitize_text_field( $_POST['email_format'] );
	update_post_meta( $post_id, '_reg_headers', $email_format );
	
	$registration_email = sanitize_text_field( $_POST['registration_email'] );
	update_post_meta( $post_id, '_reg_email', $registration_email );
	
	$registration_title = sanitize_text_field( $_POST['registration_title'] );
	update_post_meta( $post_id, '_reg_title', $registration_title );
	
	$registration_files = sanitize_text_field( $_POST['registration_files'] );
	update_post_meta( $post_id, '_reg_files', $registration_files );
	
	$registration_template = wp_kses_post( $_POST['registration_template'] );
	update_post_meta( $post_id, '_reg_template', $registration_template );
	
	$confirmation_title = sanitize_text_field( $_POST['confirmation_title'] );
	update_post_meta( $post_id, '_con_title', $confirmation_title );
	
	$confirmation_template = wp_kses_post( $_POST['confirmation_template'] );
	update_post_meta( $post_id, '_con_template', $confirmation_template );
}

/* 
*  Displays found CF7 shortcodes on settings page 
*/
function cf7optin_display_cf7form_fields($selectedform){
	$allowed_shortcodes = array(
		'text' ,
		'radio' ,
		'tel' ,
		'email' ,
		'textarea' ,
		'checkbox',
		'acceptance',
		'date',
		'text*' , 
		'tel*' ,
		'email*' ,
		'textarea*' ,
		'checkbox*',
		'date*',
		'select',
		'select*',
		'url',
		'url*',
		'number',
		'number*',
		'range',
		'range*'
		);
	$attachment_shortcodes = array(
		'file',
		'file*'
		);
	$cf7formbody = get_post_meta($selectedform, '_form', true);
	$matches = array();
	$shortcodes_found = array();
	$files_found = array();
	preg_match_all('/(\[(.*?)?\](?:(.+?)?\[\/\])?)/', $cf7formbody, $matches, PREG_SET_ORDER);
	foreach ($matches as $shortcode) {
		$chunks = explode(' ' , $shortcode[0]);
		$chunk_start = (trim($chunks[0] , '[]'));
		$shortcode_name = trim($chunks[1] , '[]');
		if (in_array($chunk_start, $allowed_shortcodes , true)) {
			if (!in_array($shortcode_name, $shortcodes_found)) $shortcodes_found[] = $shortcode_name; //regular shortcodes
		}
		if (in_array($chunk_start, $attachment_shortcodes, true)) {
			if (!in_array($shortcode_name, $files_found)) $files_found[] = $shortcode_name; //attachment shortcodes
		}
	}
	if (count($shortcodes_found) > 0 ) {
		echo '<p style="line-height:2.5em;"><span>' . __('Fields found in CF7 form: ', 'cf7-optin') . '</span>';
		foreach ($shortcodes_found as $found_shortcode) {
			echo cf7optin_elem('[' . $found_shortcode . ']');
		}
		echo '<br/>' . __('You can use those fields in your email subject and template', 'cf7-optin') . '</p>';
	}
	if (count($files_found) > 0 ) {
	echo '<p style="line-height:2.5em;"><span>' . __('File attachment fields found in CF7 form: ', 'cf7-optin') . '</span>';
	foreach ($files_found as $found_file) {
		echo cf7optin_elem('[' . $found_file . ']');
	}
	echo '<br/>' . __('You can use those fields as attachments to your email', 'cf7-optin') . '</p>';
	}
}

/* Check if mandatory confirmation page with slug "opt-in" exists
*/
function cf7optin_page_exists() {
	global $wpdb;
	$optin_page_exist = $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'opt-in'", 'ARRAY_A' );
	// Check if the page already exists
	if(null !== $optin_page_exist) {
		$optinpage = get_page_by_path($optin_page_exist['post_name'], OBJECT, 'page');
		$page_status = $optinpage->post_status;
		if($page_status !== 'publish') {
			add_action( 'admin_notices', function () use ($page_status) {cf7optin_not_publish_optin_page_notice($page_status);} );
		} else {
			return true;
		}
	} else {
		add_action( 'admin_notices', 'cf7optin_no_optin_page_notice' );
	}
}
add_action('admin_init', 'cf7optin_page_exists');

/* 
	Fires page creation if current admin page uri contains creation parameters
*/
function cf7optin_check_page_create() {
	if (is_admin() && isset($_GET['cf7optinpage']) && $_GET['cf7optinpage'] === '1') cf7optin_create_optin_page();
}
add_action( 'admin_init', 'cf7optin_check_page_create');

/* Create confirmation page and insert shortcode to its content
   Only when page doesn't exist or its name was changed
   Required slug is "opt-in"
*/
function cf7optin_create_optin_page() {
	$pagecheck = cf7optin_page_exists(); //checking if still does not exist
	
	if (!$pagecheck) {
		$optin_page_title = __('Finalize Submit','cf7-optin');
		$optin_page = array (
			'post_title'	=> $optin_page_title,
			'post_name'		=> 'opt-in',
			'post_status'	=> 'publish',
			'post_author'	=> 1,
			'post_content'	=> '[cf7doubleoptin]',
			'post_type'		=> 'page'
		   );
		   
		$optin_page_id = wp_insert_post($optin_page, true, true);
		$redirect_url = optin_get_current_admin_url();
		wp_redirect($redirect_url);
		exit;
		
	} else {
		add_action( 'admin_notices', function() {echo '<div class="error"><p>' . __('Double opt-in for CF7 plugin – page with <strong>opt-in</strong> slug is already created!', 'cf7-optin') . '</p></div>';} ); 
	}
}

/* Returns current admin url without gazyllion of parameters - thanks to WooCommerce team
*/
function optin_get_current_admin_url() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
	if ( ! $uri ) {
		return '';
	}
	return remove_query_arg( array( 'cf7optinpage' ), admin_url( $uri ) ); // removing of page creation param
}
/* Admin notice when no opt-in page exists
*/
function cf7optin_no_optin_page_notice(){
	global $wp;
	$cf7optin_current_url = optin_get_current_admin_url();
	$actionurl = add_query_arg('cf7optinpage', '1', $cf7optin_current_url);
    ?><div class="error">
		<p><?php _e('Attention! For Double opt-in for CF7 plugin to work properly you have to create page with <strong>opt-in</strong> slug!', 'cf7-optin'); ?>
		<a class="page-title-action" href="<?php echo esc_url($actionurl); ?>"><?php _e('Create confirmation page', 'cf7-optin'); ?></a></p>
	</div><?php
}

/* Admin notice when opt-in page exists but not published
*/
function cf7optin_not_publish_optin_page_notice($status) {
	$optin_status = $status; //in case there is custom status
	switch($status) {
		case 'publish':
			$optin_status = _x('publish', 'WP post status', 'cf7-optin');
			break;
			case 'draft':
			$optin_status = _x('draft', 'WP post status', 'cf7-optin');
			break;
			case 'future':
			$optin_status = _x('future', 'WP post status', 'cf7-optin');
			break;
			case 'pending':
			$optin_status = _x('pending', 'WP post status', 'cf7-optin');
			break;
			case 'private':
			$optin_status = _x('private', 'WP post status', 'cf7-optin');
			break;
			case 'trash':
			$optin_status = _x('trash', 'WP post status', 'cf7-optin');
			break;
			case 'auto-draft':
			$optin_status = _x('auto-draft', 'WP post status', 'cf7-optin');
			break;
			case 'inherit':
			$optin_status = _x('inherit', 'WP post status', 'cf7-optin');
			break;
	}
	
	?><div class="error"><p><?php printf(__('Attention! For Double opt-in for CF7 to work properly the confirmation page with <strong>opt-in</strong> slug must be <strong>published</strong>. This page exists but has %s status.', 'cf7-optin'), $optin_status); ?></p></div><?php
}

/* 	Displays shortcodes and other useful strongs in admin
*	elements are selected on click. Returns string to display.
*/
function cf7optin_elem($elem) {
	$output =  '<span class="cf7-optin-shortcode" onclick="cf7optinSelectNode(this);">' . $elem . '</span></strong>';
	return $output;
}