<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

require(APPPATH . 'libraries/Rest_controller.php');
use Restserver\Libraries\Rest_controller;

class Api extends REST_Controller {
    public function __construct() {
        parent::__construct();
    }


	/******************************************************* Bio Creation */
	public function inventory_add_post() {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);

		$active_flag = 0;

		// Pull form data
		$inv_name = trim($this->post('inv_name'));
		$inv_directory = trim($this->post('inv_directory'));
		$inv_description = trim($this->post('inv_description'));
		$inv_desc_short = trim($this->post('inv_desc_short'));
		$inv_seq = trim($this->post('inv_seq'));
		$landing_image = trim($this->post('landing_image'));
		if ($this->post('active_flag') === "on") {
			$active_flag = 1;
		}

		if (!$inv_name)
			$this->response("Inventory name is required.", 400);
		if (strlen($binv_name) > INVENTORY_NAME_MAX_LENGTH)
			$this->response("Inventory name length is " . INVENTORY_NAME_MAX_LENGTH . " characters.", 400);

		$this->load->model('inventory_model');

		// Load data to create the Bio
		$data = array(
			'inv_name'		  	=> $inv_name,
			'inv_directory'		=> $inv_directory,
			'inv_description'	=> $inv_description,
			'inv_desc_short'	=> $inv_desc_short,
			'seq'		  		=> $inv_seq,
			'active_flag' 		=> $active_flag,
			'landing_image'		=> $landing_image
		);
		// Attempt to create the Company and report success or failure
		if (!$inv_id = $this->inventory_model->insert($data))
			$this->response("Inventory could not be inserted - Inventory not created. Report this.", 500);

		// Report success
		$this->response("Inventory created.", 200);
	}

	/******************************************************* Bio Creation */
	public function bio_add_post() {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);

		// Pull form data
		$bio_name = trim($this->post('bio_name'));
		$bio_title = trim($this->post('bio_title'));
		$bio_companies = trim($this->post('bio_companies'));
		$bio_title = trim($this->post('bio_title'));
		$bio_image= trim($this->post('bio_image'));
		$bio_seq = trim($this->post('bio_seq'));
		$bio_description = trim($this->post('bio_description'));

		if (!$bio_name)
			$this->response("Bio name is required.", 400);
		if (strlen($bio_name) > BIO_NAME_MAX_LENGTH)
			$this->response("Bio name length is " . BIO_NAME_MAX_LENGTH . " characters.", 400);

		$this->load->model('bios_model');

		// Load data to create the Bio
		$data = array(
			'bio_name'		  => $bio_name,
			'bio_title'		  => $bio_title,
			'bio_companies'	  => $bio_companies,
			'bio_image'		  => $bio_image,
			'bio_seq'		  => $bio_seq,
			'bio_description' => $bio_description
		);
		// Attempt to create the Company and report success or failure
		if (!$bio_id = $this->bios_model->insert($data))
			$this->response("Bio could not be inserted - bio not created. Report this.", 500);

		// Report success
		$this->response("Bio created.", 200);
	}


  /*** OLD STUFD BELOW  **/
	public function change_company_post() {
		// Throw an error if the User lacks permission to use this function
		if (!is_superuser($this->session->userdata('user_level')))
			$this->response("You lack permissions to use this function.", 400);
		// Pull sent data
		$company = $this->post('company');
		// Change the User's Company for this session
		$this->session->set_userdata('user_company', $company);
		// Find the User's Company's database info
		$this->load->model('companies_model');
		$company = $this->companies_model->get($company);
		// Change the User's Company database for this session
		$this->session->set_userdata('user_db', db_config($company->db));
		// Change the Departments list to the Company's
		$this->load->library('ion_auth');
		$this->ion_auth->where('company', $company->id);
		$this->session->set_userdata('departments', $this->ion_auth->groups()->result());
		// Change the Company name for the header
		$this->session->set_userdata('company_name', $company->name);
		// Report success
		$this->response("Your current company has successfully been changed.", 200);
	}

	/*********************************************************** Form Entries */
	public function form_entries_post() {
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
		// Pull guaranteed form data
		$form_id = $this->post('form_id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's name
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id ))->row())
			$this->response("Form not found.", 500);
		// Check if the User has access to the Form (via user level or Department)
		if (!can_access_form($this->session->userdata('user_level'), $this->session->userdata('user_groups'), $form->department))
			$this->response("You lack permission to access this.", 401);
		// Pull extra form data
		$order = $this->post('order');
		$direction = $this->post('direction');
		$operators = $this->post('operator');
		$starts = $this->post('start');
		$ends = $this->post('end');
		if ($direction != 'DESC' && $direction != 'ASC')
			$this->response("Invalid sorting direction.", 400);
		// Ensure the operators provided are valid
		$this->load->model('nvp_codes_model');
		foreach ($operators as $operator)
			if (!$this->nvp_codes_model->get_nvp_display('Operators', $operator))
				$this->response("Invalid operator provided.", 400);
		// Ensure dates provided are valid
		foreach ($starts as $start)
			if (!$this->validate_date($start))
				$this->response("Invalid start date provided.", 400);
		foreach ($ends as $end)
			if (!$this->validate_date($end))
				$this->response("Invalid end date provided.", 400);
		// Grab the Form's field data
		$company_db->where('form', $form->id)
				   ->order_by('entry_seq');
		$fields = $company_db->get('form_metadata')->result();
		// Ensure the field to order by is valid
		if (!$company_db->field_exists($order, $form->map))
			$this->response("Invalid field to order by.", 400);
		// Apply filters where applicable
		foreach ($fields as $field) {
			// Skip signature as we don't filter by them
			if ($field->name != 'signature') {
				// Apply filters if the form passed a value for the field
				if ($this->post($field->name) || isset($starts[$field->name])) {
					if ($field->type == 'int' || $field->type == 'decimal') {
						if (isset($operators[$field->name])) // Creator has no operator
							$company_db->where("`{$field->name}` {$operators[$field->name]}", $this->post($field->name));
						else
							$company_db->where($field->name, $this->post($field->name));
					} elseif ($field->type == 'varchar' || $field->type == 'text') {
						$search_string = trim($this->post($field->name));
						$company_db->where("`{$field->name}` LIKE '%{$this->db->escape_like_str($search_string)}%' ESCAPE '!'");
					} elseif ($field->type == 'dropdown') {
						$company_db->where_in($field->name, $this->post($field->name));
					} elseif ($field->type == 'datetime') {
						// Format dates to perform comparison
						if (isset($starts[$field->name])) {
							$start_date = new DateTime($starts[$field->name]);
							$start_date = $start_date->format("Y-m-d H:i:00");
							$company_db->where("`{$field->name}` >=", $start_date);
						}

						if (isset($ends[$field->name])) {
							$end_date = new DateTime($ends[$field->name]);
							$end_date = $end_date->format("Y-m-d H:i:00");
							$company_db->where("`{$field->name}` <=", $end_date);
						}
					}
				}
			}
		}
		// Order the table before pulling entries
		$company_db->order_by($order, $direction);
		// Load array of [user_id]->user_name to convert ids to names
		$this->load->model('users_model');
		$users_array = $this->users_model->get_user_id_array();
		// Grab the newly ordered entries of the Form
		$entries = $company_db->get($form->map)->result();
		// Replace User ids with names, remove signatures
		foreach ($entries as $entry) {
			$entry->creator = $users_array[$entry->creator];
			unset($entry->signature);
		}
		$field_list = array();
		foreach ($fields as $field)
			if ($field->name != 'signature') // Don't try to make a signature column - id handles this
				$field_list[] = $field->name;
		$data['fields'] = $field_list;
		$data['entries'] = $entries;
		$this->response(json_encode($data), 200);
	}

	public function reorder_entries_post() {
		// Pull form data
		$field_id = $this->post('id');
		$direction = $this->post('direction');
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
		if ($direction != 'left' && $direction != 'right')
			$this->respose("Invalid direction.", 400);
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab the specific field's metadata
		$field = $company_db->get_where('form_metadata', array( 'id' => $field_id ))->row();
		// Grab the full list of the Form's metadata
		$metadata = $company_db->get_where('form_metadata', array( 'form' => $field->form ))->result();
		// Catch attempts to go further than the 'ends' of the table
		if ($direction == 'left' && $field->entry_seq == 1)
			$this->response("Column cannot be moved further to the left.", 400);
		if ($direction == 'right' && $field->entry_seq == count($metadata))
			$this->response("Column cannot be moved further to the right.", 400);
		// Attempt to grab the metadata of the field being swapped with the specified field, throw an error if not found
		if ($direction == 'left')
			$next_seq = $field->entry_seq - 1;
		if ($direction == 'right')
			$next_seq = $field->entry_seq + 1;
		if (!$company_db->get_where('form_metadata', array( 'form' => $field->form, 'entry_seq' => $next_seq )))
			$this->response("Field to swap positions with cannot be found.", 400);
		// Update both field's positions
		$company_db->update('form_metadata', array( 'entry_seq' => $field->entry_seq ), array( 'form' => $field->form, 'entry_seq' => $next_seq ));
		$company_db->update('form_metadata', array( 'entry_seq' => $next_seq ), array( 'id' => $field_id ));
		// Respond with success
		$this->response("Column moved.", 200);
	}

	public function edit_entry_post() {
		// Pull form data
		$form_id = $this->post('form_id');
		$entry_id = $this->post('entry_id');
		$property = $this->post('property');
		$new_value = $this->post('value');
		$signature = $this->post('signature');
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab the Form
		$form = $company_db->get_where('forms', array ( 'id' => $form_id ))->row();
		// Grab the Form's fields metadata
		$fields = $company_db->get_where('form_metadata', array( 'form' => $form_id ))->result();
		// Check if the property is valid
		$chosen_field = NULL;
		foreach ($fields as $field)
			if ($field->map == $property)
				$chosen_field = $field;
		if (!$chosen_field)
			$this->response("Invalid field.", 400);
		// Ensure empty string is null instead
		if ($new_value == "")
			$new_value = NULL;
		// Catch an empty value if not allowed
		if ($new_value == NULL && $chosen_field->required)
			$this->response("An empty value is not valid for this field.", 400);
		// Find valid dropdown options if the field is a dropdown
		if ($chosen_field->type == 'dropdown') {
			$dropdown = $company_db->get_where('dropdown_nvp', array( 'form' => $form_id, 'context' => $chosen_field->map ))->result();
			$dropdown_options = array();
			foreach ($dropdown as $option)
				$dropdown_options[] = $option->display;
		}
		// Validate new value
		if ($new_value != NULL) { // Don't bother validating an accepted null value
			switch ($chosen_field->type) {
				case 'dropdown':
					if (!in_array($new_value, $dropdown_options))
						$this->response("Invalid dropdown value.", 400);
					break;
				case 'decimal':
					if (!is_numeric($new_value))
						$this->response("This field only accepts decimals.", 400);
					break;
				case 'int':
					if (!is_numeric($new_value))
						$this->response("This field only accepts integers.", 400);
					break;
				case 'datetime':
					if (!$this->validate_date($new_value))
						$this->response("Invalid date.", 400);
					break;
			}
		}
		// Grab the old value of the entry's field for saving in the Event Log
		$entry = $company_db->get_where($form->map, array( 'id' => $entry_id ))->row();
		$old_value = $entry->{$property};
		// Update the entry
		$company_db->update($form->map, array( $property => $new_value ), array( 'id' => $entry_id ));
		// Update logic columns for the entry as well, if they are present
		if ($logics = $company_db->get_where('form_logic', array( 'form' => $form_id ))->result()) {
			foreach ($logics as $logic)
				$logic_columns[] = $logic->name;
			// Use a copy of the old entry as a basis for calculating logic columns, using the new value
			$data = $entry;
			$data->{$property} = $new_value;
			// Calculate any logic columns
			foreach ($logics as $logic) {
				// Decide whether to use a field or constant for logic
				if ($logic->constant)
					$field2 = (float) $logic->field2;
				else
					$field2 = $data->{$logic->field2};
				// Calculate the field's value using whatever operation is saved, or NULL if one of the input values is null
				if ($data->{$logic->field1} === NULL || $field2 === NULL) {
					$value = NULL;
				} else {
					switch ($logic->operation) {
						case '+':
							$value = $data->{$logic->field1} + $field2;
							break;
						case '-':
							$value = $data->{$logic->field1} - $field2;
							break;
						case '*':
							$value = $data->{$logic->field1} * $field2;
							break;
						case '/':
							$value = $data->{$logic->field1} / $field2;
							break;
					}
				}
				// Save the caculated result
				$data->{$logic->name} = $value;
			}
			// Make an array of the new logic column values
			$logic_data = array();
			foreach ($data as $data_field => $value)
				if (in_array($data_field, $logic_columns))
					$logic_data[$data_field] = $value;
			// Update the entry's logic columns
			$company_db->update($form->map, $logic_data, array( 'id' => $entry_id ));
		}
		// Decode the image
		$signature = str_replace('data:image/png;base64,', '', $signature);
		$signature = str_replace(' ', '+', $signature);
		$signature = base64_decode($signature);
		// Log the event
		$this->load->model('nvp_codes_model');
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "{$form->name}'s #{$entry_id} entry '{$property}' field changed from '{$old_value}' to '{$new_value}'",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Entry Edited'),
			'date'			=> date('Y-m-d H:i:s'),
			'signature'		=> $signature
		);
		$company_db->insert('event_log', $data);
		// Respond with success
		$this->response("Entry updated.", 200);
	}

	/************************************************************* Form Logic */
	public function form_logic_add_post() {
		// Pull form data
		$form_id = $this->post('form_id');
		$field_name = str_replace(' ', '_', trim($this->post('field_name')));
		$constant = $this->post('constant');
		$field1 = $this->post('field1');
		$operation = $this->post('operation');
		$field2 = $this->post('field2');
		$field2_constant = $this->post('field2_constant');
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			$this->response("Form not found.", 500);
		// Load the database into DBForge
		$company_forge = $this->load->dbforge($company_db, TRUE);
		// Ensure both fields belong to the Form
		$company_db->where('form', $form->id)
				   ->order_by('entry_seq');
		$fields = $company_db->get('form_metadata')->result();
		$field1_found = $field2_found = FALSE;
		$field_names = array();
		foreach ($fields as $field) {
			if ($field1 == $field->name) {
				$field1_found = TRUE;
				if ($field->type != 'int' && $field->type != 'decimal')
					$this->response("The first chosen field is not a numeric field.", 400);
			}
			if (!$constant && $field2 == $field->name) {
				$field2_found = TRUE;
				if ($field->type != 'int' && $field->type != 'decimal')
					$this->response("The second chosen field is not a numeric field.", 400);
			}
			$field_names[] = $field->name;
		}
		if (!$field1_found)
			$this->response("The first chosen field is not one of this form's fields.", 400);
		if (!$constant && !$field2_found)
			$this->response("The second chosen field is not one of this form's fields.", 400);
		// Ensure the operation is valid
		if (!in_array($operation, array( '+', '-', '*', '/' )))
			$this->response("That operation is not valid.", 400);
		// Ensure the field name isn't already in use and exists
		if (!$field_name)
			$this->response("The field name is required.", 400);
		if (in_array($field_name, $field_names))
			$this->response("That field name is already used in the form.", 400);
		if ($constant && strlen($field2_constant > 100))
			$this->response("The provided constant is too long in length.", 400);
		if ($constant && !is_numeric($field2_constant))
			$this->response("The provided constant is not numeric.", 400);
		if ($constant && $operation == '/' && $field2_constant == 0)
			$this->response("You cannot divide by zero.", 400);
		// Add the field to the Form's table
		$field = array(
			$field_name => array( 'type' => 'DECIMAL', 'constraint' => '65, 3' )
		);
		$company_forge->add_column($form->map, $field);
		// Add the logic column into the form_logic table
		$data = array(
			'form'		=> $form->id,
			'name'		=> $field_name,
			'field1'	=> $field1,
			'operation'	=> $operation,
			'field2'	=> $field2,
			'constant'	=> $constant
		);
		// Use the constant provided if applicable
		if ($constant)
			$data['field2'] = $field2_constant;
		$company_db->insert('form_logic', $data);
		// Add the new field into the form_metadata table
		$metadata = array(
			'form'			=> $form->id,
			'map'			=> $field_name,
			'name'			=> $field_name,
			'entry_seq'		=> count($fields) + 1,
			'form_seq'		=> count($fields) + 1,
			'type'			=> 'decimal',
			'max_length'	=> 65,
			'precision'		=> 3,
			'required'		=> 1
		);
		$company_db->insert('form_metadata', $metadata);
		// Populate the new field for each row
		if (!$constant)
			$company_db->set("`{$field_name}`", "`{$field1}` {$operation} `{$data['field2']}` WHERE `{$field1}` IS NOT NULL AND `{$data['field2']}` IS NOT NULL", FALSE);
		else
			$company_db->set("`{$field_name}`", "`{$field1}` {$operation} {$data['field2']} WHERE `{$field1}` IS NOT NULL", FALSE);
		$company_db->update($form->map);
		// Log the creation of the new logic column
		$this->load->model('nvp_codes_model');
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "Column {$field_name} added to {$form->name}",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Logic Column Created'),
			'date'			=> date('Y-m-d H:i:s')
		);
		$company_db->insert('event_log', $data);
		// Report success
		$this->response("Column added.", 200);
	}

	public function form_logic_delete_post() {
		// Pull form data
		$form_id = $this->post('form_id');
		$id = $this->post('id');
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			$this->response("Form not found.", 500);
		// Check if the User has access to the Form (via user level or Department)
		if (!can_access_form($this->session->userdata('user_level'), $this->session->userdata('user_groups'), $form->department))
			$this->response("You lack permission to access this page.", 401);
		// Load the database into DBForge
		$company_forge = $this->load->dbforge($company_db, TRUE);
		if (!$column = $company_db->get_where('form_logic', array( 'id' => $id ))->row())
			$this->response("Column not found.", 500);
		// Delete the row from form_logic
		$company_db->delete('form_logic', array( 'id' => $id ));
		// Grab the correct column name from metadata
		$col_name = $company_db->get_where('form_metadata', array( 'name' => $column->name ))->row()->map;
		// Drop the column from the Form's table
		$company_forge->drop_column($form->map, $col_name);
		// Delete the field's metadata
		$company_db->delete('form_metadata', array( 'name' => $column->name ));
		// Log the deletion of the logic column
		$this->load->model('nvp_codes_model');
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "Column {$column->name} deleted from {$form->name}",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Logic Column Deleted'),
			'date'			=> date('Y-m-d H:i:s')
		);
		$company_db->insert('event_log', $data);
		// Report success
		$this->response("Column deleted.", 200);
	}

	/******************************************************** Form Submission */
	public function form_submit_post() {
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
		// Pull guaranteed form data
		$form_id = $this->post('form_id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's name
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			$this->response("Form not found.", 500);
		// Check if the User has access to the Form (via user level or Department)
		if (!can_access_form($this->session->userdata('user_level'), $this->session->userdata('user_groups'), $form->department))
			$this->response("You lack permission to access this.", 401);
		// Define fields to ignore
		$default_fields = array( 'id', 'insert_date', 'creator', 'signature', 'quality_check_date' );
		// Load logic columns (to avoid looking for them)
		$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
		$logic_columns = array();
		foreach ($logics as $logic)
			$logic_columns[] = $logic->name;
		// Grab the Form's field data
		$company_db->where('form', $form->id)
				   ->order_by('form_seq');
		$fields = $company_db->get('form_metadata')->result();
		foreach ($fields as $field) {
			if (!in_array($field->name, $default_fields) && !in_array($field->name, $logic_columns)) {
				// Catch empty string as actually being null
				if (($field_value = $this->post($field->name)) == "")
					$field_value = NULL;
				// Catch empty data if not allowed
				if ($field->required && $field_value == NULL)
					$this->response("{$field->name} cannot be empty.", 400);
				// Pull variable form data
				$data[$field->name] = $field_value;
				if ($data[$field->name] == NULL) // Skip validation for an accepted null value
					continue;
				// Check form data against constraints
				if ($field->max_length && $field->max_length < strlen($data[$field->name]))
					$this->response("{$field->name} has a maximum length of {$field->max_length}.", 400);
				// Check form data against type
				if ($field->type == 'datetime' && !$this->validate_date($data[$field->name]))
					$this->response("{$field->name} has a date format of mm/dd/YY.", 400);
				if ($field->type == 'int' && !is_numeric($data[$field->name]))
					$this->response("{$field->name} only takes integers.", 400);
				if ($field->type == 'decimal' && !is_numeric($data[$field->name]))
					$this->response("{$field->name} only takes decimals.", 400);
				// TODO 10/17/19 Add check for dropdowns against expected output
				// If the field is a DateTime, format it for database entry
				if ($field->type == 'datetime')
					$data[$field->name] = (new DateTime($data[$field->name]))->format('Y-m-d H:i:s');
			}
		}
		// Always save the User who created the Form entry
		$data['creator'] = $this->session->userdata('user_id');
		// Save the date, default current_timestamp no longer works
		$data['insert_date'] = date('Y-m-d H:i:s');
		// Decode the image
		$data['signature'] = $this->post('signature');
		$data['signature'] = str_replace('data:image/png;base64,', '', $data['signature']);
		$data['signature'] = str_replace(' ', '+', $data['signature']);
		$data['signature'] = base64_decode($data['signature']);
		// Calculate any logic columns
		foreach ($logics as $logic) {
			// Decide whether to use a field or constant for logic
			if ($logic->constant)
				$field2 = (float) $logic->field2;
			else
				$field2 = $data[$logic->field2];
			// Calculate the field's value using whatever operation is saved, or NULL if one of the input values is null
			if ($data[$logic->field1] === NULL || $field2 === NULL) {
				$value = NULL;
			} else {
				switch ($logic->operation) {
					case '+':
						$value = $data[$logic->field1] + $field2;
						break;
					case '-':
						$value = $data[$logic->field1] - $field2;
						break;
					case '*':
						$value = $data[$logic->field1] * $field2;
						break;
					case '/':
						$value = $data[$logic->field1] / $field2;
						break;
				}
			}
			// Save the caculated result
			$data[$logic->name] = $value;
		}
		// Attempt to insert the new Form data
		$company_db->insert($form->map, $data);
		// Add the Form entry to the Event Log
		$this->load->model('nvp_codes_model');
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "Entry added to {$form->name} Form",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'New Form Entry'),
			'date'			=> date('Y-m-d H:i:s')
		);
		$company_db->insert('event_log', $data);
		// Pull list of Form alerts that go off on entry
		$alerts = $company_db->get_where('form_alerts', array( 'form' => $form->id, 'onentry' => 1 ))->result();
		// For any on entry alerts for this Form, notify the primary recipient
		$this->load->model('users_model');
		foreach ($alerts as $alert) {
			// Create the subject and message to be sent
			$subject = "{$form->name} Form Alert";
			$message = "The {$form->name} Form has received a new entry.";
			notify_account($this->users_model->get($alert->primary), $subject, $message);
			// Delete the alert if it is a one time alert
			if ($alert->onetime)
				$company_db->delete('form_alerts');
		}
		// Report success
		$this->response("Form submitted.", 200);
	}

	private function validate_date($date) {
		$array = explode('/', $date, 3);
		if (count($array) == 3)
			return checkdate($array[0], $array[1], $array[2]);
		else
			return FALSE;
	}

	/********************************************************** Form Creation */
	public function form_create_post() {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
		// Pull form data
		$departments = $this->post('departments');
		$form_name = trim($this->post('form_name'));
		$subtitle = trim($this->post('subtitle'));
		$dropdowns = $this->post('dropdowns');
		$input_fields = $this->post('fields');
		// Check to ensure the Form has a name and it is within the max length
		if (!$form_name)
			$this->response("The name of the form is required.", 400);
		if (strlen($form_name) > FORM_NAME_MAX_LENGTH)
			$this->response("The maximum number of characters in the form name is " . FORM_NAME_MAX_LENGTH . ".", 400);
		if (strlen($subtitle) > FORM_NAME_MAX_LENGTH)
			$this->response("The maximum number of characters in the subtitle is " . FORM_NAME_MAX_LENGTH . ".", 400);
		// Check to ensure a Department is able to view this Form before bothering to make it
		if (!$departments)
			$this->response("At least one department must be able to view this form.", 400);
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Load the database into DBForge
		$company_forge = $this->load->dbforge($company_db, TRUE);
		// Load initial fields to create the new Form table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			)
		);
		// Default fields to check for
		$default_fields = array(
			'id',
			'insert_date',
			'creator',
			'form_name',
			'department_id',
			'signature',
			'quality_check_date'
		);
		// Add fields for any input rows
		$dropdown_hack = $required_hack = array();
		foreach ($input_fields as $field) {
			// Trim the field names and replace spaces (10/24/19 fix for double space names until we do column mapping)
			$field['name'] = str_replace(' ', '_', trim($field['name']));
			// Catch attempts to overwrite the default field names
			if (in_array($field['name'], $default_fields))
				$this->response("{$field['name']} is a reserved field name.", 400);
			// Save the field's correct type (and keep track of dropdowns for later)
			if ($field['type'] == 'CURRENCY') {
				$fields[$field['name']]['type'] = 'DECIMAL';
			} elseif ($field['type'] == 'DROPDOWN') {
				$fields[$field['name']]['type'] = 'VARCHAR';
				$dropdown_hack[$field['name']] = 'dropdown';
			} else {
				$fields[$field['name']]['type'] = $field['type'];
			}
			// Define constraints
			if ($field['type'] == 'VARCHAR' || $field['type'] == 'DROPDOWN')
				$fields[$field['name']]['constraint'] = $field['length'] ? $field['length'] : 100; // Use length if provided, default 100
			elseif ($field['type'] == 'CURRENCY')
				$fields[$field['name']]['constraint'] = "65,2"; // Decimals get max decimal length, currencies get two decimal places
			elseif ($field['type'] == 'DECIMAL')
				$fields[$field['name']]['constraint'] = "65,{$field['precision']}"; // Decimals get max decimal length, with chosen number of decimal places
			// Allow all input fields to accept null values
			$fields[$field['name']]['null'] = TRUE;
			// Check if the field is required
			if ($field['required'])
				$required_hack[$field['name']] = TRUE;
		}
		// Add remaining default fields
		$fields['creator'] = array(
			'type'				=> 'INT',
			'constraint'		=> 11,
			'unsigned'			=> TRUE,
			'null'				=> TRUE
		);
		$fields['insert_date'] = array(
			'type'				=> 'DATETIME',
			'null'				=> TRUE
		);
		$fields['signature'] = array(
			'type'				=> 'BLOB',
			'null'				=> TRUE
		);
		$fields['quality_check_date'] = array(
			'type'				=> 'DATETIME',
			'null'				=> TRUE
		);
		// Add the fields before creating the new Form table
		$company_forge->add_field($fields);
		// Make the id the primary key of the Form table
		$company_forge->add_key('id', TRUE);
		// Prepare list of Departments with access to this Form
		$department = "";
		$this->load->library('ion_auth');
		foreach ($departments as $dep) {
			if ($this->ion_auth->group($dep)->row()->company != $this->session->userdata('user_company'))
				$this->response("One or more of the departments you selected is not part of this company.", 400);
			$department .= "{$dep},";
		}
		// Check if the Departments of the Form exceed the max length
		if (strlen($department) > DEPARTMENT_STRING_MAX_LENGTH)
			$this->response("List of departments exceeded maximum character length - report this", 400);
		// Get current number of Forms for generating new table name
		$num_tables = count($company_db->get('forms')->result()) + 1;
		$form_map = "table_{$num_tables}";
		// Create the new Form table
		$company_forge->create_table($form_map);
		// Prepare data to save to the Forms table
		$data = array(
			'department'	=> $department,
			'name'			=> $form_name,
			'map'			=> $form_map,
			'subtitle'		=> $subtitle
		);
		// Add the Form to the Forms table
		$company_db->insert('forms', $data);
		$form_id = $company_db->insert_id(); // Grab the ID of the newly created Form
		// Add the Form's fields to the metadata table
		$count = 1;
		foreach ($fields as $name => $field) {
			if ($name != 'id') {
				// Decide precision
				if ($field['type'] == 'DECIMAL')
					$precision = explode(',', $field['constraint'])[1]; // Constraint is formatted "<maxlength,precision>"
				else
					$precision = 0;
				// Decide max length
				if (isset($field['constraint']))
					$length = explode(',', $field['constraint'])[0]; // Constraint is formatted "<maxlength,precision>"
				else
					$length = NULL;
				$data = array(
					'form'			=> $form_id,
					'name'			=> $name,
					'map'			=> $name, // TODO when changing column names to standardized versions
					'entry_seq'		=> $count,
					'form_seq'		=> $count,
					'max_length'	=> $length,
					'precision'		=> $precision
				);
				// Decide type for metadata
				if (isset($dropdown_hack[$name]))
					$data['type'] = 'dropdown';
				else
					$data['type'] = strtolower($field['type']);
				// Decide required for metadata
				if (isset($required_hack[$name]))
					$data['required'] = 1;
				else
					$data['required'] = 0;

				$company_db->insert('form_metadata', $data);
				$count++;
			}
		}
		// Add dropdown options to the Dropdown NVP table
		for ($i = 2; $i < count($dropdowns); $i++) {
			if ($dropdowns[$i]) {
				$count = 1;
				foreach ($dropdowns[$i] as $option) {
					$data = array(
						'context'	=> $input_fields[$i - 2]['name'], // Fields starts at 0, dropdowns at 2
						'form'		=> $form_id,
						'seq'		=> $count,
						'display'	=> trim($option)
					);
					$company_db->insert('dropdown_nvp', $data);
					$count++;
				}
			}
		}
		// Add the creation of the new Form to the Event Log
		$this->load->model('nvp_codes_model');
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "New Form {$form_name} created",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'New Form Created'),
			'date'			=> date('Y-m-d H:i:s')
		);
		$company_db->insert('event_log', $data);
		// Report success
		$this->response("Form created.", 200);
	}

	/*********************************************************** Form Editing */
	public function form_edit_post() {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Load form data
		$form_id = $this->post('form_id');
		$departments = $this->post('departments');
		$input_form_name = trim($this->post('form_name'));
		$subtitle = trim($this->post('subtitle'));
		$input_dropdowns = $this->post('dropdowns');
		$input_required = $this->post('required');
		// Attempt to pull the Form's record from the Form's name
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			$this->response("Form not found.", 500);
		// Prepare list of Departments from input
		$department = "";
		$this->load->library('ion_auth');
		foreach ($departments as $dep) {
			if ($this->ion_auth->group($dep)->row()->company != $this->session->userdata('user_company'))
				$this->response("One or more of the departments you selected is not part of this company.", 400);
			$department .= "{$dep},";
		}
		// Catch bad string inputs
		if (strlen($input_form_name) > FORM_NAME_MAX_LENGTH)
			$this->response("The maximum number of characters in the form name is " . FORM_NAME_MAX_LENGTH . ".", 400);
		if (strlen($subtitle) > FORM_NAME_MAX_LENGTH)
			$this->response("The maximum number of characters in the subtitle is " . FORM_NAME_MAX_LENGTH . ".");
		// Load data to update the Form
		$data = array();
		if ($department && $department != $form->department)
			$data['department'] = $department;
		if ($input_form_name && $input_form_name != $form->name)
			$data['name'] = $input_form_name;
		if ($subtitle && $subtitle != $form->subtitle)
			$data['subtitle'] = $subtitle;
		// Grab the Form's field data
		$company_db->where('form', $form->id)
				   ->order_by('form_seq');
		$fields = $company_db->get('form_metadata')->result();
		// Define fields to ignore
		$default_fields = array( 'id', 'insert_date', 'creator', 'signature', 'quality_check_date' );
		// Load logic columns (to avoid looking for them)
		$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
		$logic_columns = array();
		foreach ($logics as $logic)
			$logic_columns[] = $logic->name;
		// Load existing dropdown data and 'required' states to compare
		$dropdowns = $required = array();
		foreach ($fields as $field) {
			if ($field->type == 'dropdown') {
				$company_db->where('form', $form->id)
						   ->where('context', $field->name);
				$options = $company_db->get('dropdown_nvp')->result();
				foreach ($options as $option)
					$dropdowns[$field->id][] = $option->display;
			}
			// Don't keep track of whether or not defaults/logics are required
			if (!in_array($field->name, $default_fields) && !in_array($field->name, $logic_columns))
				$required[$field->id] = $field->required;
		}
		// Check if there were any changes made to the Form
		if (!count($data) && (!$input_dropdowns || $input_dropdowns == $dropdowns) && $input_required == $required)
			$this->response("No changes were made.", 400);
		// Update the Form's dropdowns if changes were made
		if ($input_dropdowns && $input_dropdowns != $dropdowns) {
			// Remove existing dropdown options
			$company_db->where('form', $form->id)
					   ->delete('dropdown_nvp');
			// Add new dropdown options
			foreach ($input_dropdowns as $dd_id => $dd) {
				$count = 0;
				$dd_field = $company_db->get_where('form_metadata', array( 'id' => $dd_id ))->row()->map;
				foreach ($dd as $option) {
					$option_data = array(
						'context'	=> $dd_field,
						'form'		=> $form->id,
						'seq'		=> $count,
						'display'	=> trim($option)
					);
					$company_db->insert('dropdown_nvp', $option_data);
					$count++;
				}
			}
		}
		// Update the Form's required fields if changes were made
		if ($input_required != $required)
			foreach ($input_required as $required_id => $required)
				$company_db->update('form_metadata', array( 'required' => $required ), array( 'id' => $required_id ));
		// Update the Form and log the event
		if ($data)
			$company_db->update('forms', $data, array( 'id' => $form->id ));
		// Add the Form edit to the Event Log
		$this->load->model('nvp_codes_model');
		$event = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "{$form->name} edited",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Form Edited'),
			'date'			=> date('Y-m-d H:i:s')
		);
		$company_db->insert('event_log', $event);

		$this->response("Form updated.", 200);
	}

	/******************************************************* Company Creation */
	public function company_add_post() {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);
		// Pull form data
		$company_name = trim($this->post('company_name'));
		$company_db = str_replace(' ', '_', trim($this->post('company_db')));
		$no_db = $this->post('no_db');
		if (!$company_name)
			$this->response("Company name is required.", 400);
		if (strlen($company_name) > COMPANY_NAME_MAX_LENGTH)
			$this->response("Maximum company name length is " . COMPANY_NAME_MAX_LENGTH . " characters.", 400);

		// Generate a unique guid for the Company
		$this->load->model('companies_model');

		// Load data to create the Company
		$data = array(
			'name'		=> $company_name,
			'db'		=> $company_db,
			'guid'		=> $guid,
			'api_key'	=> $api_key
		);
		// Attempt to create the Company and report success or failure
		if (!$bio_id = $this->bios_model->insert($data))
			$this->response("Bio could not be inserted - bio not created. Report this.", 500);
		elseif (!$no_db) {
			// Load the new database
			$new_db = $this->load->database(db_config($company_db), TRUE);
			// Add the creation of the new Company to the Event Log
			$this->load->model('nvp_codes_model');
			$data = array(
				'user'			=> $this->session->userdata('user_id'),
				'event'			=> "New Company {$company_name} created",
				'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Company Created'),
				'date'			=> date('Y-m-d H:i:s')
			);
			$new_db->insert('event_log', $data);

		}
		// Report success
		$this->response("Bio created.", 200);
	}

	private function get_guid() {
		mt_srand((double) microtime() * 10000); // optional for php 4.2.0 and up
		$charid = strtoupper(md5(uniqid(rand(), TRUE)));
		$hyphen = chr(45); // "-"
		$uuid = substr($charid, 0, 8) . $hyphen .
				substr($charid, 8, 4) . $hyphen .
				substr($charid,12, 4);
		return $uuid;
	}

	private function get_api_key() {
		mt_srand((double) microtime() * 10000); // optional for php 4.2.0 and up
		$charid = strtoupper(md5(uniqid(rand(), TRUE)));
		$hyphen = chr(45); // "-"
		$uuid = substr($charid, 0, 8) . $hyphen .
				substr($charid, 8, 4) . $hyphen .
				substr($charid,12, 4) . $hyphen .
				substr($charid,16, 4) . $hyphen .
				substr($charid,20,12);
		return $uuid;
	}

	private function create_forms_table($db) {
		// Load the database into DBForge
		$this->company_forge = $this->load->dbforge($db, TRUE);
		// Load fields to create the Forms table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			),
			'department' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'unsigned'			=> TRUE,
				'null'				=> FALSE
			),
			'name' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'subtitle' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100
			),
		);
		// Add the fields before creating the Forms table
		$this->company_forge->add_field($fields);
		// Add active as the last field
		$this->company_forge->add_field("active TINYINT(1) NOT NULL DEFAULT 1");
		// Make the id the primary key of the Forms table
		$this->company_forge->add_key('id', TRUE);
		// Attempt to create the Forms table and return success or failure
		if ($this->company_forge->create_table('forms'))
			return TRUE;
		else
			return FALSE;
	}

	private function create_form_logic_table($db) {
		// Load the database into DBForge
		$this->company_forge = $this->load->dbforge($db, TRUE);
		// Load fields to create the Forms table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			),
			'form_name' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 255,
				'null'				=> FALSE
			),
			'name' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'field1' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 255,
				'null'				=> FALSE
			),
			'operation' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 255,
				'null'				=> FALSE
			),
			'field2' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 255,
				'null'				=> FALSE
			),
			'constant' => array(
				'type'			=> 'INT',
				'constraint'	=> 1,
				'null'			=> FALSE
			)
		);
		// Add the fields before creating the Forms table
		$this->company_forge->add_field($fields);
		// Make the id the primary key of the Forms table
		$this->company_forge->add_key('id', TRUE);
		// Attempt to create the Forms table and return success or failure
		if ($this->company_forge->create_table('form_logic'))
			return TRUE;
		else
			return FALSE;
	}

	private function create_form_alerts_table($db) {
		// Load the database into DBForge
		$this->company_forge = $this->load->dbforge($db, TRUE);
		// Load fields to create the Forms table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			),
			'form_name' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'frequency' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'null'				=> FALSE
			),
			'quota' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'null'				=> FALSE
			),
			'primary' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
			),
			'secondary' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
			),
			'days' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE,
			),
			'time' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 10,
				'null'				=> TRUE
			),
			'onetime' => array(
				'type'				=> 'TINYINT',
				'constraint'		=> 1,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'default'			=> 0
			),
			'onentry' => array(
				'type'				=> 'TINYINT',
				'constraint'		=> 1,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'default'			=> 0
			),
			'creator' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
			),
		);
		// Add the fields before creating the Forms table
		$this->company_forge->add_field($fields);
		// Keep track of the number of repeated alerts - used for logic to notify secondary recipient
		$this->company_forge->add_field("repeated INT(11) NOT NULL DEFAULT 0");
		// Make the id the primary key of the Forms table
		$this->company_forge->add_key('id', TRUE);
		// Attempt to create the Forms table and return success or failure
		if ($this->company_forge->create_table('form_alerts'))
			return TRUE;
		else
			return FALSE;
	}

	private function create_form_metadata_table($db) {
		// Load the database into DBForge
		$this->company_forge = $this->load->dbforge($db, TRUE);
		// Load fields to create the Forms table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			),
			'form' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE
			),
			'map' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'name' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'entry_seq' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'null'				=> FALSE
			),
			'form_seq' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'null'				=> FALSE
			),
			'type' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 32,
				'null'				=> FALSE
			),
			'max_length' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> TRUE
			),
			'precision' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE
			),
			'required' => array(
				'type'				=> 'TINYINT',
				'constraint'		=> 1,
				'null'				=> FALSE
			)
		);
		// Add the fields before creating the Forms table
		$this->company_forge->add_field($fields);
		// Make the id the primary key of the Forms table
		$this->company_forge->add_key('id', TRUE);
		// Attempt to create the Forms table and return success or failure
		if ($this->company_forge->create_table('form_metadata'))
			return TRUE;
		else
			return FALSE;
	}

	private function create_dropdown_nvp_table($db) {
		// Load the database into DBForge
		$this->company_forge = $this->load->dbforge($db, TRUE);
		// Load fields to create the Forms table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			),
			'context' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'form_name' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'seq' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE
			),
			'display' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
		);
		// Add the fields before creating the Forms table
		$this->company_forge->add_field($fields);
		// Make the id the primary key of the Forms table
		$this->company_forge->add_key('id', TRUE);
		// Attempt to create the Forms table and return success or failure
		if ($this->company_forge->create_table('dropdown_nvp'))
			return TRUE;
		else
			return FALSE;
	}

	private function create_reports_table($db) {
		// Load the database into DBForge
		$this->company_forge = $this->load->dbforge($db, TRUE);
		// Load fields to create the Reports table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			),
			'name' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'map' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 18,
				'null'				=> FALSE
			),
			'type' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE
			),
			'day' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> TRUE
			),
			'time' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 10,
				'null'				=> FALSE
			),
			'creator' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE
			),
			'private' => array(
				'type'				=> 'TINYINT',
				'constraint'		=> 1,
				'null'				=> FALSE
			),
			'active' => array(
				'type'				=> 'TINYINT',
				'default'			=> 1,
				'constraint'		=> 1,
				'null'				=> FALSE
			),
		);
		// Add the fields before creating the Reports table
		$this->company_forge->add_field($fields);
		// Make the id the primary key of the Reports table
		$this->company_forge->add_key('id', TRUE);
		// Attempt to create the Reports table and return success or failure
		if ($this->company_forge->create_table('reports'))
			return TRUE;
		else
			return FALSE;
	}

	private function create_report_fields_table($db) {
		// Load the database into DBForge
		$this->company_forge = $this->load->dbforge($db, TRUE);
		// Load fields to create the Report Fields table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			),
			'name' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'map' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 15,
				'null'				=> FALSE
			),
			'report' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE
			),
			'field' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> TRUE
			),
			'operation' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> TRUE
			),
		);
		// Add the fields before creating the Report Fields table
		$this->company_forge->add_field($fields);
		// Make the id the primary key of the Report Fields table
		$this->company_forge->add_key('id', TRUE);
		// Attempt to create the Report Fields table and return success or failure
		if ($this->company_forge->create_table('report_fields'))
			return TRUE;
		else
			return FALSE;
	}

	private function create_event_log_table($db) {
		// Load the database into DBForge
		$this->company_forge = $this->load->dbforge($db, TRUE);
		// Load fields to create the Event Log table
		$fields = array(
			'id' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE,
				'auto_increment'	=> TRUE
			),
			'user' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> FALSE
			),
			'event' => array(
				'type'				=> 'VARCHAR',
				'constraint'		=> 100,
				'null'				=> FALSE
			),
			'event_type' => array(
				'type'				=> 'INT',
				'constraint'		=> 11,
				'unsigned'			=> TRUE,
				'null'				=> TRUE
			),
		);
		// Add the fields before creating the Forms table
		$this->company_forge->add_field($fields);
		// Array format doesn't work for default DateTimes, so we add them by string
		$this->company_forge->add_field("date DATETIME NOT NULL");
		$this->company_forge->add_field("signature BLOB NOT NULL");
		// Make the id the primary key of the Event Log table
		$this->company_forge->add_key('id', TRUE);
		// Attempt to create the Event Log table and return success or failure
		if ($this->company_forge->create_table('event_log'))
			return TRUE;
		else
			return FALSE;
	}

	/*********************************************************** External API */
	public function companies_get() {
		// Pull inputs
		$api_key = $this->get('key');
		// Find the acting Company with the input key
		$this->load->model('companies_model');
		$company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$company || !$api_key)
			$this->response("Invalid API key.", 400);
		// Pull the list of Companies that Company has access to
		$this->load->model('access_model');
		if ($companies = $this->access_model->get_access_export($company->id))
			$this->response(json_encode($companies), 200);
		else
			$this->response("No companies accessible.", 400);
	}

	public function departments_get() {
		// Pull inputs
		$api_key = $this->get('key');
		$company_guid = $this->get('guid');
		// Find the acting Company with the input key
		$this->load->model('companies_model');
		$acting_company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$acting_company)
			$this->response("Invalid API key.", 400);
		// Find the searched Company with the input GUID
		$searched_company = $this->companies_model->get_by(array( 'guid' => $company_guid, 'active' => 1 ));
		// Check if the searched Company was found
		if (!$searched_company)
			$this->response("Company not found.", 400);
		// Check if the acting Company has access to the searched Company
		$this->load->model('access_model');
		if (!in_array($searched_company->id, $this->access_model->get_accessible($acting_company->id)))
			$this->response("You do not have acccess to that company.", 400);
		// Return a list of the Company's Departments
		$this->load->model('departments_model');
		if ($departments = $this->departments_model->get_departments_export($searched_company->id))
			$this->response(json_encode($departments), 200);
		else
			$this->response("No departments found.", 400);
	}

	public function forms_get() {
		// Pull inputs
		$api_key = $this->get('key');
		$company_guid = $this->get('guid');
		$department_id = $this->get('department_id');
		// Find the acting Company with the input key
		$this->load->model('companies_model');
		$acting_company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$acting_company)
			$this->response("Invalid API key.", 400);
		// Find the searched Company with the input GUID
		$searched_company = $this->companies_model->get_by(array( 'guid' => $company_guid, 'active' => 1 ));
		// Check if the searched Company was found
		if (!$searched_company)
			$this->response("Company not found.", 400);
		// Check if the acting Company has access to the searched Company
		$this->load->model('access_model');
		if (!in_array($searched_company->id, $this->access_model->get_accessible($acting_company->id)))
			$this->response("You do not have acccess to that company.", 400);
		// Check if the Department is valid and part of the searched Company
		$this->load->model('departments_model');
		if (!$this->departments_model->get_by(array( 'id' => $department_id, 'company' => $searched_company->id )))
			$this->response("That department does not exist as part of the company.", 400);
		// Load the searched Company's database
		$company_db = $this->load->database(db_config($searched_company->db), TRUE);
		// Attempt to pull the searched Company's Forms
		if (!$forms = $company_db->get('forms')->result())
			$this->response("No forms found.", 500);
		// Load a list of Forms accessible by the Department provided
		$response = array();
		foreach ($forms as $form) {
			$departments = explode(',', $form->department);
			if (in_array($department_id, $departments))
				$response[$form->id] = $form->name;
		}
		// Return a list of Forms accessible by the Department provided
		$this->response(json_encode($response), 200);
	}

	public function fields_get() {
		// Pull inputs
		$api_key = $this->get('key');
		$company_guid = $this->get('guid');
		$form_id = $this->get('form_id');
		// Find the acting Company with the input key
		$this->load->model('companies_model');
		$acting_company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$acting_company)
			$this->response("Invalid API key.", 400);
		// Find the searched Company with the input GUID
		$searched_company = $this->companies_model->get_by(array( 'guid' => $company_guid, 'active' => 1 ));
		// Check if the searched Company was found
		if (!$searched_company)
			$this->response("Company not found.", 400);
		// Check if the acting Company has access to the searched Company
		$this->load->model('access_model');
		if (!in_array($searched_company->id, $this->access_model->get_accessible($acting_company->id)))
			$this->response("You do not have acccess to that company.", 400);
		// Load the searched Company's database
		$company_db = $this->load->database(db_config($searched_company->db), TRUE);
		// Attempt to pull the Form's record from the Form's name
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			$this->response("Form not found.", 500);
		// Return a list of the Form's fields
		$this->response(json_encode($company_db->list_fields($form->map)), 200);
	}

	public function data_get() {
		// Pull inputs
		$api_key = $this->get('key');
		$company_guid = $this->get('guid');
		$form_id = $this->get('form_id');
		$fields = $this->get('fields');
		// Find the acting Company with the input key
		$this->load->model('companies_model');
		$acting_company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$acting_company)
			$this->response("Invalid API key.", 400);
		// Find the searched Company with the input GUID
		$searched_company = $this->companies_model->get_by(array( 'guid' => $company_guid, 'active' => 1 ));
		// Check if the searched Company was found
		if (!$searched_company)
			$this->response("Company not found.", 400);
		// Check if the acting Company has access to the searched Company
		$this->load->model('access_model');
		if (!in_array($searched_company->id, $this->access_model->get_accessible($acting_company->id)))
			$this->response("You do not have acccess to that company.", 400);
		// Load the searched Company's database
		$company_db = $this->load->database(db_config($searched_company->db), TRUE);
		// Attempt to pull the Form's record from the Form's name
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			$this->response("Form not found.", 500);
		// Load the list of valid field names
		$field_names = $company_db->list_fields($form->map);
		if (is_array($fields)) {
			// Define which fields are to be included in the Select
			foreach ($fields as $field) {
				if (!in_array($field, $field_names))
					$this->response("{$field} is not a field of this form.", 500);
				$company_db->select($field);
			}
		} else {
			if (!in_array($fields, $field_names))
				$this->response("{$fields} is not a field of this form.", 500);
			$company_db->select($fields);
		}
		// Return the Form's data entries with only the selected fields
		$this->response(json_encode($company_db->get($form->map)->result()), 200);
	}

	public function analytics_forms_get() {
		// Pull input
		$api_key = $this->get('key');
		// Check if the key is valid
		if (!$api_key)
			$this->response("No key provided.", 400);
		// Find the Company with the input key
		$this->load->model('companies_model');
		$company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$company)
			$this->response("Invalid API key.", 400);
		// Attempt to load the Company's database
		if (!$company_db = $this->load->database(db_config($company->db), TRUE))
			$this->response("Could not load company's database.", 500);
		// Attempt to pull the Company's Forms
		if (!$forms = $company_db->get_where('forms', array( 'active' => 1 ))->result())
			$this->response("No active forms found.", 500);
		// Load array of Departments to convert IDs to names
		$this->load->model('departments_model');
		$departments_array = $this->departments_model->get_departments_dropdown($company->id);
		// Load a list of Forms accessible by the Department provided
		$response = array();
		foreach ($forms as $form) {
			$departments = explode(',', $form->department);
			if (isset($departments_array[$departments[0]]))
				$first_department = $departments_array[$departments[0]];
			else
				$first_department = '';
			// Replace '_' with ' ' in Department names
			$first_department = str_replace('_', ' ', $first_department);
			// Create object for the row
			$object_row = new stdClass();
			$object_row->id = $form->id;
			$object_row->name = "{$first_department}: {$form->name}";
			$response[] = $object_row;
		}
		// Return a list of the Company's active Forms
		$this->response(json_encode($response), 200);
	}

	public function analytics_form_get() {
		// Pull input
		$api_key = $this->get('key');
		$form_id = $this->get('form');
		// Check if the key is provided
		if (!$api_key)
			$this->response("No key provided.", 400);
		// Check if the form is provided
		if (!$form_id)
			$this->response("No form provided.", 400);
		// Find the Company with the input key
		$this->load->model('companies_model');
		$company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$company)
			$this->response("Invalid API key.", 400);
		// Load the Company's database
		$company_db = $this->load->database(db_config($company->db), TRUE);
		// Attempt to pull the Form
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id ))->row())
			$this->response("Form not found.", 500);
		// Pull Form entries
		$entries = $company_db->get($form->map)->result();
		// Load array of [user_id]->user_name to convert IDs to names
		$this->load->model('users_model');
		$users_array = $this->users_model->get_user_id_array();
		// Replace User IDs with names, remove signatures and IDs
		foreach ($entries as $entry) {
			$entry->creator = $users_array[$entry->creator];
			unset($entry->signature);
			unset($entry->id);
		}
		// Return the Form's data
		$this->response(json_encode($entries), 200);
	}

	public function analytics_reports_get() {
		// Pull input
		$api_key = $this->get('key');
		// Check if the key is valid
		if (!$api_key)
			$this->response("No key provided.", 400);
		// Find the Company with the input key
		$this->load->model('companies_model');
		$company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$company)
			$this->response("Invalid API key.", 400);
		// Load the Company's database
		$company_db = $this->load->database(db_config($company->db), TRUE);
		// Attempt to pull the Company's public Reports
		if (!$reports = $company_db->get_where('reports', array( 'active' => 1, 'private' => 0 ))->result())
			$this->response("No active, public reports found.", 500);
		// Load a list of Forms accessible by the Department provided
		$response = array();
		foreach ($reports as $report) {
			// Create object for the row
			$object_row = new stdClass();
			$object_row->id = $report->id;
			$object_row->name = $report->name;
			$response[] = $object_row;
		}
		// Return a list of active, public Reports
		$this->response(json_encode($response), 200);
	}

	public function analytics_report_get() {
		// Pull input
		$api_key = $this->get('key');
		$report_id = $this->get('report');
		// Check if the key is provided
		if (!$api_key)
			$this->response("No key provided.", 400);
		// Check if the Report id is provided
		if (!$report_id)
			$this->response("No report id provided.", 400);
		// Find the Company with the input key
		$this->load->model('companies_model');
		$company = $this->companies_model->get_by(array( 'api_key' => $api_key, 'active' => 1 ));
		// Check if the key is valid
		if (!$company)
			$this->response("Invalid API key.", 400);
		// Load the Company's database
		$company_db = $this->load->database(db_config($company->db), TRUE);
		// Attempt to pull the Report
		if (!$report = $company_db->get_where('reports', array( 'id' => $report_id ))->row())
			$this->response("Report not found.", 500);
		// Pull Report entries
		$entries = $company_db->get($report->map)->result();
		// Load the Report's fields
		$fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result();
		//
		$result = array();
		foreach ($entries as $index => $entry)
			foreach ($fields as $field)
				$result[$index][$field->name] = $entry->{$field->map};
		// Return the Report's data
		$this->response(json_encode($result), 200);
	}
}
