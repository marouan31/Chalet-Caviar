<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * This Ninja Forms Processing Class is used to interact with Ninja Forms as it processes form data.
 * It is based upon the WordPress Error API.
 *
 * Contains the Ninja_Forms_Processing class
 *
 */

/**
 * Ninja Forms Processing class.
 *
 * Class used to interact with form processing.
 * This class stores all data related to the form submission, including data from the Ninja Form mySQL table.
 * It can also be used to report processing errors and/or processing success messages.
 *
 * Form Data Methods:
 *		get_form_ID() - Used to retrieve the form ID of the form being processed.
 *		get_user_ID() - Used to retrieve the User ID if the user was logged in.
 *		get_action() - Used to retrieve the action currently being performed. ('submit', 'save', 'edit_sub').
 *		set_action('action') - Used to set the action currently being performed. ('submit', 'save', 'edit_sub').
 *
 * Submitted Values Methods:
 *		get_all_fields() - Returns an array of all the fields within a form. The return is array('field_ID' => 'user value').
 *		get_submitted_fields() - Returns an array of just the fields that the user has submitted. The return is array('field_ID' => 'user_value').
 *		get_field_value('field_ID') - Used to access the submitted data by field_ID.
 *		update_field_value('field_ID', 'new_value') - Used to change the value submitted by the user. If the field does not exist, it will be created.
 *		remove_field_value('field_ID') - Used to delete values submitted by the user.
 *		get_field_settings('field_ID') - Used to get all of the back-end data related to the field (type, label, required, show_help, etc.).
 *		get_field_setting( 'field_ID', 'setting_ID' ) - Used to retrieve a specific field setting.
 *		update_field_setting( 'field_ID', 'setting_ID', 'value' ) - Used to temporarily update a piece of back-end data related to the field. This is NOT permanent and will only affect the current form processing.
 *		update_field_settings('field_ID', $data) - Used to temporarily update the back-end data related to the field. This is NOT permanent and will only affect the current form processing.
 *
 * Extra Fields Methods (These are fields that begin with an _ and aren't Ninja Forms Fields )
 * 		get_all_extras() - Returns an array of all extra form inputs.
 *		get_extra_value('name') - Used to access the value of an extra field.
 *		update_extra_value('name', 'new_value') - Used to update an extra value.
 *		remove_extra_value('name') - Used to delete the extra value from the processing variable.
 *
 * Form Settings Methods (Please note that the changes made with these methods only affect the current process and DO NOT permanently change these settings):
 *		get_all_form_settings() - Used to get all of the settings of the form currently being processed.
 *		get_form_setting('setting_ID') - Used to retrieve a form setting from the form currently being processed.
 *		update_form_setting('setting_ID', 'new_value') - Used to change the value of a form setting using its unique ID. If the setting does not exist, it will be created.
 *		remove_form_setting('setting_ID') - Used to remove a form setting by its unique ID.
 *
 * Error Reporting Methods:
 *		get_all_errors() - Used to get an array of all error messages in the format: array('unique_id' => array('error_msg' => 'Error Message', 'display_location' => 'Display Location')).
 *			An empty array is returned if no errors are found.
 *		get_error('unique_id') - Used to get a specific error message by its unique ID.
 *		get_errors_by_location('location') - Used to retrieve an array of error messages with a given display location.
 *		add_error('unique_ID', 'Error Message', 'display_location') - Used to add an error message. The optional 'display_location' tells the display page where to show this error.
 *			Possible examples include a valid field_ID or 'general'. If this value is not included, the latter will be assumed and  will place this error at the beginning of the form.
 *		remove_error('unique_ID') - Used to remove an error message.
 *		remove_all_errors() - Used to remove all currently set error messages.
 *
 * Success Reporting Methods:
 *		get_all_success_msgs() - Used to get an array of all success messages in the format: array('unique_ID' => 'Success Message').
 *		get_success_msg('unique_ID') - Used to get a specific success message.
 *		add_success_msg('unique_ID', 'Success Message') - Used to add a success message.
 *		remove_success_msg('unique_ID') - Used to remove a success message.
 *		remove_all_success_msgs() - Used to remove all currently set success messages.
 *
 * Calculation Methods:
 *		get_calc( name or id, return array ) - Used to get the value of the specified calculation field. Unless bool(false) is sent, returns an array including all of the fields that contributed to the value.
 *		get_calc_fields(calc_id) - Used to get an array of the fields that contributed to the calculation. This array includes a field_id and calculation value.
 *		get_calc_total( return array ) - Used to get the final value of the "Payment Total" if it exists. Unless bool(false) is sent, returns an array including all of the fields that contributed to the value and are marked with calc_option.
 *		get_calc_sub_total( return array ) - Used to get the value of the "Payment Subtotal" if it exists. Unless bool(false) is sent, returns an array including all of the fields that contributed to the value and are marked with calc_option.
 *		get_calc_tax_rate() - Used to get the value of the "Tax" field if it exists.
 *		get_calc_tax_total() - Used to get the total amount of tax if the tax field is set.	
 *
 * User Information Methods:
 *		get_user_info() - Used to get an array of the user's information. Requires that the appropriate "User Information" fields be used.
 *
 * Credit Card Information Methods:
 *		get_credit_card() - Used to get an array of the user's credit card information.
 */

class Ninja_Forms_Processing {

	/**
	 *
	 * Stores the data accessed by the other parts of the class.
	 * All response messages will be stored in this value.
	 *
	 * @var array
	 * @access private
	 */
	var $data = array();

	/**
	 * Constructor - Sets up the form ID.
	 *
	 * If the form_ID parameter is empty then nothing will be done.
	 *
	 */
	function __construct($form_ID = '') {
		if(empty($form_ID)){
			return false;
		}else{
			$this->data['form_ID'] = $form_ID;
			$user_ID = get_current_user_id();
			if(!$user_ID){
				$user_ID = '';
			}
			$this->data['user_ID'] = $user_ID;
		}
	}

	/**
	 * Add the submitted vars to $this->data['fields'].
	 * Also runs any functions registered to the field's pre_process hook.
	 *
	 *
	 */
	function setup_submitted_vars() {
		global $ninja_forms_fields, $wp;
		$form_ID = $this->data['form_ID'];

		//Get our plugin settings
		$plugin_settings = nf_get_settings();
		$req_field_error = __( $plugin_settings['req_field_error'], 'ninja-forms' );

		if ( empty ( $this->data ) )
			return '';
		
		$this->data['action'] = 'submit';
		$this->data['form']['form_url'] = $this->get_current_url();
		$cache = ( Ninja_Forms()->session->get( 'nf_cache' ) ) ? Ninja_Forms()->session->get( 'nf_cache' ) : null;

		// If we have fields in our $_POST object, then loop through the $_POST'd field values and add them to our global variable.
		if ( isset ( $_POST['_ninja_forms_display_submit'] ) OR isset ( $_POST['_ninja_forms_edit_sub'] ) ) {
			$field_results = ninja_forms_get_fields_by_form_id($form_ID);
			//$field_results = apply_filters('ninja_forms_display_fields_array', $field_results, $form_ID);

			foreach( $field_results as $field ) {
				$data = $field['data'];
				$field_id = $field['id'];
				$field_type = $field['type'];

				if ( isset ( $_POST['ninja_forms_field_' . $field_id ] ) ) {
					$val = ninja_forms_stripslashes_deep( $_POST['ninja_forms_field_' . $field_id ] );
					$this->data['submitted_fields'][] = $field_id;
				} else {
					$val = false;
				}

                $val = nf_wp_kses_post_deep( $val );

				$this->data['fields'][$field_id] = $val;
				$field_row = ninja_forms_get_field_by_id( $field_id );
				$field_row['data']['field_class'] = 'ninja-forms-field';
				$this->data['field_data'][$field_id] = $field_row;
			}

			foreach($_POST as $key => $val){
				if(substr($key, 0, 1) == '_'){
					$this->data['extra'][$key] = $val;
				}
			}

			//Grab the form info from the database and store it in our global form variables.
			$form_row = ninja_forms_get_form_by_id($form_ID);
			$form_data = $form_row['data'];

			if(isset($_REQUEST['_sub_id']) AND !empty($_REQUEST['_sub_id'])){
				$form_data['sub_id'] = absint ( $_REQUEST['_sub_id'] );
			}else{
				$form_data['sub_id'] = '';
			}

			//Loop through the form data and set the global $ninja_form_data variable.
			if(is_array($form_data) AND !empty($form_data)){
				foreach($form_data as $key => $val){
					if(!is_array($val)){
						$value = stripslashes($val);
						$value = nf_wp_kses_post_deep( $value );
						//$value = htmlspecialchars($value);
					}else{
						$value = nf_wp_kses_post_deep( $val );
					}
					$this->data['form'][$key] = $value;
				}
				$this->data['form']['admin_attachments'] = array();
				$this->data['form']['user_attachments'] = array();
			}

		} else if ( $cache !== null ) { // Check to see if we have cached values from a submission.
			if ( is_array ( $cache['field_values'] ) ) {
				// We do have a submission contained in our cache. We'll populate the field values with that data.
				foreach ( $cache['field_values'] as $field_id => $val ) {
					$field_row = ninja_forms_get_field_by_id($field_id);
					if(is_array($field_row) AND !empty($field_row)){
						if(isset($field_row['type'])){
							$field_type = $field_row['type'];
						}else{
							$field_type = '';
						}
						if(isset($field_row['data']['req'])){
							$req = $field_row['data']['req'];
						}else{
							$req = '';
						}

						$val = ninja_forms_stripslashes_deep( $val );
						$val = nf_wp_kses_post_deep( $val );

						$this->data['fields'][$field_id] = $val;
						if ( isset ( $cache['field_settings'][$field_id] ) ) {
							$field_row = $cache['field_settings'][$field_id];
						} else {
							$field_row = ninja_forms_get_field_by_id( $field_id );
						}

						$field_row['data']['field_class'] = 'ninja-forms-field';
						
						$this->data['field_data'][$field_id] = $field_row;
					}
				}
			}
			$this->data['form'] = $cache['form_settings'];
			$this->data['success'] = $cache['success_msgs'];
			$this->data['errors'] = $cache['error_msgs'];
			$this->data['extra'] = $cache['extra_values'];
			
		}

	} // Submitted Vars function

	/**
	 * Submitted Values Methods:
	 *
	**/

	/**
	 * Retrieve the form ID of the form currently being processed.
	 *
	 */
	function get_form_ID() {
		if ( empty($this->data) ){
			return false;
		}else{
			return $this->data['form_ID'];
		}
	}

	/**
	 * Retrieve the User ID of the form currently being processed.
	 *
	 */
	function get_user_ID() {
		if ( empty($this->data) ){
			return false;
		}else{
			return $this->data['user_ID'];
		}
	}

	/**
	 * Set the User ID of the form currently being processed.
	 *
	 */
	function set_user_ID( $user_id ) {
		if ( empty($this->data) ){
			return false;
		}else{
			return $this->data['user_ID'] = $user_id;
		}
	}

	/**
	 * Retrieve the action currently being performed.
	 *
	 */
	function get_action() {
		if ( empty($this->data['action']) ){
			return false;
		}else{
			return $this->data['action'];
		}
	}

	/**
	 * Set the action currently being performed.
	 *
	 */
	function set_action( $action ) {
		if ( empty($this->data) ){
			return false;
		}else{
			return $this->data['action'] = $action;
		}
	}

	/**
	 * Retrieve all the fields attached to a form.
	 *
	 */
	function get_all_fields() {
		if ( empty($this->data['fields']) ){
			return false;
		}else{
			return $this->data['fields'];
		}
	}

	/**
	 * Retrieve all the user submitted form data.
	 *
	 */
	function get_all_submitted_fields() {
		if ( empty( $this->data['submitted_fields'] ) ) {
			return false;
		} else {
			$fields = array();
			$submitted_fields = $this->data['submitted_fields'];
			foreach ( $submitted_fields as $field_id ) {
				if ( isset ( $this->data['fields'][$field_id] ) ) {
					$fields[$field_id] = $this->data['fields'][$field_id];
				}
			}
			return $fields;
		}
	}


	/**
	 * Retrieve user submitted form data by field ID.
	 *
	 */
	function get_field_value($field_ID = '') {
		if(empty($this->data) OR $field_ID == '' OR !isset($this->data['fields'][$field_ID])){
			return false;
		}else{
			return $this->data['fields'][$field_ID];
		}
	}

	/**
	 * Change the value of a field.
	 *
	 */
	function update_field_value($field_ID = '', $new_value = '') {
		if(empty($this->data) OR $field_ID == ''){
			return false;
		}else{
			$this->data['fields'][$field_ID] = $new_value;
			return true;
		}
	}

	/**
	 * Remove a field and its value from the user submissions.
	 *
	 */
	function remove_field_value($field_ID = '') {
		if(empty($this->data) OR $field_ID == ''){
			return false;
		}else{
			unset($this->data['fields'][$field_ID]);
			return true;
		}
	}

	/**
	 * Retrieve field data by field ID. This data includes all of the information entered in the admin back-end.
	 *
	 */
	function get_field_settings($field_ID = '') {
		if(empty($this->data) OR $field_ID == '' OR !isset($this->data['field_data'][$field_ID])){
			return false;
		}else{
			return $this->data['field_data'][$field_ID];
		}
	}

	/**
	 * Retrieve a specific piece of field setting data.
	 *
	 * @since 2.2.45
	 * @return $value or bool(false)
	 */
	function get_field_setting( $field_id = '', $setting_id = '' ) {
		if ( empty ( $this->data ) OR $field_id == '' OR $setting_id == '' )
			return false;
		if ( isset ( $this->data['field_data'][$field_id][$setting_id] ) ) {
			return $this->data['field_data'][$field_id][$setting_id];
		} else if ( isset ( $this->data['field_data'][$field_id]['data'][$setting_id] ) ) {
			return $this->data['field_data'][$field_id]['data'][$setting_id];
		} else {
			return false;
		}
	}

	/**
	 * Update field data by field ID. This data includes all of the informatoin entered into the admin back-end. (Please note that the changes made with these methods only affect the current process and DO NOT permanently change these settings):
	 *
	 */
	function update_field_settings($field_ID = '', $new_value = '') {
		if(empty($this->data) OR $field_ID == ''){
			return false;
		}else{
			$this->data['field_data'][$field_ID] = $new_value;
			return true;
		}
	}

	/**
	 *
	 * Update a specific piece of field setting data by giving the field id and setting id.
	 *
	 * @since 2.2.45
	 * @return void or bool(false)
	 */
	function update_field_setting( $field_id = '', $setting_id = '', $value = '' ) {
		if( empty( $this->data ) OR $field_id == '' OR $setting_id == '' OR $value == '' )
			return false;
		
		if ( isset ( $this->data['field_data'][$field_id][$setting_id] ) ) {
			$this->data['field_data'][$field_id][$setting_id] = $value;
		} else {
			$this->data['field_data'][$field_id]['data'][$setting_id] = $value;
		}
	}


	/**
	 * Extra Form Values Methods
	 *
	**/

	/**
	 * Retrieve all the extra submitted form data.
	 *
	 */
	function get_all_extras() {
		if ( empty($this->data['extra']) ){
			return false;
		}else{
			return $this->data['extra'];
		}
	}


	/**
	 * Retrieve user submitted form data by field ID.
	 *
	 */
	function get_extra_value($name = '') {
		if(empty($this->data) OR $name == '' OR !isset($this->data['extra'][$name])){
			return false;
		}else{
			return $this->data['extra'][$name];
		}
	}

	/**
	 * Change the value of a field.
	 *
	 */
	function update_extra_value($name = '', $new_value = '') {
		if(empty($this->data) OR $name == ''){
			return false;
		}else{
			$this->data['extra'][$name] = $new_value;
			