<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

require(APPPATH . 'libraries/Rest_controller.php');
use Restserver\Libraries\Rest_controller;

class Reports_Api extends REST_Controller {
    public function __construct() {
        parent::__construct();
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			$this->response("You lack permission to access this page.", 401);
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			$this->response("You need to select a company first.", 401);
    }

	public function get_forms_post() {
		// Pull form data
		$department_id = $this->post('department_id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab a list of the Company's Forms
		$forms = $company_db->get_where('forms', array( 'active' => 1 ))->result();
		// Find which Forms have access to the input Department
		$accessed_forms = array( 0 => "Please Select" );
		foreach ($forms as $form) {
			$departments = explode(',', $form->department);
			if (in_array($department_id, $departments))
				$accessed_forms[$form->id] = $form->name;
		}
		// Catch if no forms were found
		if (count($accessed_forms) == 1)
			$this->response("That department has no forms.", 401);
		// Respond with the found list of Forms
		$this->response(json_encode($accessed_forms), 200);
	}

	public function get_fields_post() {
		// Pull form data
		$form_id = $this->post('form_id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab the Form's field data
		$company_db->where('form', $form_id)
				   ->where('name !=', 'creator')
				   ->group_start() // Only grab fields which we can operate on
				   ->or_where('type', 'decimal')
				   ->or_where('type', 'int')
				   ->or_where('type', 'datetime')
				   ->group_end()
				   ->order_by('form_seq');
		$fields = $company_db->get('form_metadata')->result();
		// Add header to the fields
		$found_fields = array( 0 => "Please Select" );
		foreach ($fields as $field)
			$found_fields[$field->id] = $field->name;
		// Catch if no fields were found
		if (count($found_fields) == 1)
			$this->response("No operable fields were found for that form.", 401);
		// Respond with the list of fields
		$this->response(json_encode($found_fields), 200);
	}

	public function get_cond_fields_post() {
		// Pull form data
		$form_id = $this->post('form_id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab the Form's dropdown field data
		$company_db->where('form', $form_id)
				   ->where('type', 'dropdown')
				   ->order_by('form_seq');
		$fields = $company_db->get('form_metadata')->result();
		// Add header to the dropdown fields
		$found_fields = array( 0 => "None" );
		foreach ($fields as $field)
			$found_fields[$field->id] = $field->name;
		// Respond with the list of dropdown fields
		$this->response(json_encode($found_fields), 200);
	}

	public function get_operations_post() {
		// Pull form data
		$field_id = $this->post('field_id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab the Form's field data
		$field = $company_db->get_where('form_metadata', array( 'id' => $field_id ))->row();
		// Load the operations available to the field's type
		$this->load->model('nvp_codes_model');
		if ($field->type == 'decimal' || $field->type == 'int')
			$operations = $this->nvp_codes_model->getCodeValues('Report_Operations_Number');
		elseif ($field->type == 'datetime')
			$operations = $this->nvp_codes_model->getCodeValues('Report_Operations_Date');
		else
			$this->response("The selected field's has no available operations.", 400);
		// Respond with the field's available operations
		$this->response(json_encode($operations), 200);
	}

	public function get_cond_options_post() {
		// Pull form data
		$field_id = $this->post('cond_field_id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab the Form's field data
		$field = $company_db->get_where('form_metadata', array( 'id' => $field_id ))->row();
		// Load the field's options
		$options = $company_db->get_where('dropdown_nvp', array( 'form' => $field->form, 'context' => $field->name ))->result();
		// Format the options for dropdown
		$found_options = array();
		foreach ($options as $option)
			$found_options[$option->id] = $option->display;
		// Respond with the field's options
		$this->response(json_encode($found_options), 200);
	}

	public function use_fields_post() {
		// Pull form data
		$form_id = $this->post('form_id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab the Form's field data
		$company_db->where('form', $form_id)
				   ->order_by('form_seq');
		$fields = $company_db->get('form_metadata')->result();
		// Format fields for view
		$found_fields = array();
		foreach ($fields as $field)
			if ($field->name != 'signature') // Skip signature fields
				$found_fields[$field->id] = $field->name;
		// Catch if no fields were found
		if (!count($found_fields))
			$this->response("No fields were found for that form.", 400);
		// Respond with the list of fields
		$this->response(json_encode($found_fields), 200);
	}

	public function create_post() {
		// Pull form data
		$report_name = trim($this->post('report_name'));
		$static = $this->post('static');
		$type = $this->post('type');
		$weekday = $this->post('weekday');
		$time = $this->post('time');
		$private = $this->post('private');
		$input_fields = $this->post('fields');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Load the database into DBForge
		$company_forge = $this->load->dbforge($company_db, TRUE);
		// Validate the Report's name and type
		if (!$report_name)
			$this->response("The report's name is required.", 400);
		if (strlen($report_name) > FORM_NAME_MAX_LENGTH)
			$this->response("The report's name can only be " . FORM_NAME_MAX_LENGTH . " characters long.", 400);
		if (!$static && !in_array($type, array( 1, 2, 3 ))) // Only three types of reports currently
			$this->response("That report type is invalid.", 400);
		// Validate the time
		if (!$static && !$this->validate_time($time))
			$this->response("Invalid time to run.", 400);
		// DRA TODO 11/15/19 Validate condition?
		// Load initial fields to create the new Report table
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
		$count = 1;
		$report_field_data = array();
		foreach ($input_fields as $field) {
			// Trim the input name before checking it
			$field['name'] = trim($field['name']);
			// Catch attempts to overwrite the default field names if not a Static Report
			if (!$static && in_array($field['name'], $default_fields))
				$this->response("{$field['name']} is a reserved field name.", 400);
			$field_data = $company_db->get_where('form_metadata', array( 'id' => $field['field'] ))->row();
			// Catch operating on a non-existing field
			if (!$field_data)
				$this->response("{$field['name']}'s field to operate on was not found.", 400);
			// Add the field to our fields array for creating the Report table
			if (!$static) {
				if ($field_data->type == 'int' || $field_data->type == 'decimal') {
					$fields["col_{$count}"]['type'] = 'decimal';
					$fields["col_{$count}"]['constraint'] = '65,3';
					$fields["col_{$count}"]['null'] = TRUE;
				} elseif ($field_data->type == 'datetime') {
					$fields["col_{$count}"]['type'] = 'datetime';
					$fields["col_{$count}"]['null'] = TRUE;
				} else {
					$this->response("{$field['name']}'s field to operate on is not a valid type.", 400);
				}
			} else {
				if ($field_data->type == 'dropdown')
					$fields["col_{$count}"]['type'] = 'varchar';
				else
					$fields["col_{$count}"]['type'] = $field_data->type;
				$fields["col_{$count}"]['null'] = TRUE;
				if ($field_data->max_length)
					$fields["col_{$count}"]['constraint'] = $field_data->max_length;
				if ($field_data->precision)
					$fields["col_{$count}"]['constraint'] .= ",{$field_data->precision}";
			}
			// Save Report Field data to input later
			$report_field_data[$count] = array(
				'name'				=> $field['name'],
				'map'				=> "col_{$count}",
				'field'				=> $field['field']
			);
			// Leave these fields as NULL if no values are given
			if ($field['cond_field'])
				$report_field_data[$count]['condition_field'] = $field['cond_field'];
			if ($field['cond_option'])
				$report_field_data[$count]['condition_option'] = $field['cond_option'];
			// Use the operation of the field if applicable
			if (!$static) {
				$report_field_data[$count]['type'] = $fields["col_{$count}"]['type'];
				$report_field_data[$count]['operation'] = $field['operation'];
			} else {
				$report_field_data[$count]['type'] = $field_data->type;
			}
			// Increment the number of fields for mapping each column
			$count++;
		}
		// Add a default field to the Report table (Insert Date) if not Static
		if (!$static) {
			$fields["col_{$count}"] = array(
				'type'				=> 'DATETIME',
				'null'				=> TRUE
			);
		}
		// Add the fields before creating the new Form table
		$company_forge->add_field($fields);
		// Make the id the primary key of the Form table
		$company_forge->add_key('id', TRUE);
		// Get current number of Reports for generating new table name
		$num_tables = count($company_db->get('reports')->result()) + 1;
		$report_map = "report_{$num_tables}";
		// Create the new Report table
		$company_forge->create_table($report_map);
		// Load data to save the Report
		$data = array(
			'name'				=> $report_name,
			'map'				=> $report_map,
			'creator'			=> $this->session->userdata('user_id'),
			'private'			=> $private,
			'static'			=> $static,
			'active'			=> 1
		);
		// Save the Report's type and time to run if not Static
		if (!$static) {
			$data['type'] = $type;
			$data['time'] = $time;
		}
		// If it is a weekly report, validate the input day of the week to run on and save interface if not Static
		$this->load->model('nvp_codes_model');
		if (!$static && $type == $this->nvp_codes_model->get_nvp_code('Report_Types', 'Weekly')) {
			if (!in_array($weekday, array( 1, 2, 3, 4, 5, 6, 7 )))
				$this->response("Invalid weekday selected.", 400);
			else
				$data['day'] = $weekday;
		}
		// Save the Report
		$company_db->insert('reports', $data);
		$report_id = $company_db->insert_id(); // Grab the ID of the newly created Report
		// Save the Report's fields
		foreach ($report_field_data as $report_field) {
			$report_field['report'] = $report_id;
			$company_db->insert('report_fields', $report_field);
		}
		// Add the insert date into Report Fields as well, with no field or operation references if not Static
		if (!$static) {
			$data = array(
				'name'		=> 'Insert Date',
				'map'		=> "col_{$count}",
				'type'		=> 'datetime',
				'report'	=> $report_id
			);
			$company_db->insert('report_fields', $data);
		} else { // Initialize the Static Report before logging the event
			$this->initialize_static_report($report_id);
		}
		// Add the creation of the new Report to the Event Log
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "New Report {$report_name} created",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Report Created'),
			'date'			=> date('Y-m-d H:i:s')
		);
		$company_db->insert('event_log', $data);
		// Respond with success
		$this->response("Report created.", 200);
	}

	private function initialize_static_report($report_id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Load the Report
		$report = $company_db->get_where('reports', array( 'id' => $report_id ))->row();
		// Load the Report's fields
		$report_fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result();
		// Find the map of the Form whose fields we are using
		$form_id = $company_db->get_where('form_metadata', array( 'id' => $report_fields[0]->field ))->row()->form; // All fields of a Static Report have the same Form
		$form_map = $company_db->get_where('forms', array( 'id' => $form_id ))->row()->map;
		// SELECT the ID
		$query = "SELECT id";
		// Loop through the Report's fields to SELECT the Form's fields
		foreach ($report_fields as $report_field) {
			$query .= ", {$company_db->get_where('form_metadata', array( 'id' => $report_field->field ))->row()->map}"; // SELECT each Form field's map
			$query .= " AS {$report_field->map}"; // Alias the field as the Report field's map
		}
		$query .= " FROM {$form_map}";
		// Only pull entries from yesterday or earlier, since the script pulls at midnight daily
		$yesterday = (new DateTime())->sub(new DateInterval('P1D'))->format('Y-m-d 23:59:59');
		$query .= " WHERE `insert_date` <= '{$yesterday}'";
		// Check against the condition if it exists
		if ($report_fields[0]->condition_option) {
			$condition_field_map = $company_db->get_where('form_metadata', array( 'id' => $report_fields[0]->condition_field ))->row()->map;
			$query .= " AND `{$condition_field_map}` = '{$report_fields[0]->condition_option}'"; // Only pull entries with the condition option
		}
		// Load the Form entries that match our criteria
		$entries = $company_db->query($query)->result();
		// Add each entry to the Static Report
		foreach ($entries as $entry)
			$company_db->insert($report->map, $entry);
	}

	public function add_logic_post() {
		// Pull form data
		$report_id = $this->post('report_id');
		$field_name = trim($this->post('field_name'));
		$constant = $this->post('constant');
		$field1 = $this->post('field1');
		$operation = $this->post('operation');
		$field2 = $this->post('field2');
		$field2_constant = $this->post('field2_constant');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Report's record from the Report's id
		if (!$report = $company_db->get_where('reports', array( 'id' => $report_id, 'active' => 1 ))->row())
			$this->response("Report not found.", 500);
		// Load the database into DBForge
		$company_forge = $this->load->dbforge($company_db, TRUE);
		// Ensure both fields belong to the Report
		$fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result();
		$field1_found = $field2_found = FALSE;
		$field_names = array();
		foreach ($fields as $field) {
			if ($field1 == $field->name) {
				$field1_found = TRUE;
				if ($field->type != 'decimal')
					$this->response("The first chosen field is not a numeric field.", 400);
			}
			if (!$constant && $field2 == $field->name) {
				$field2_found = TRUE;
				if ($field->type != 'decimal')
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
			$this->response("That field name is already used in the report.", 400);
		if ($constant && strlen($field2_constant > 100))
			$this->response("The provided constant is too long in length.", 400);
		if ($constant && !is_numeric($field2_constant))
			$this->response("The provided constant is not numeric.", 400);
		if ($constant && $operation == '/' && $field2_constant == 0)
			$this->response("You cannot divide by zero.", 400);
		// Find the appropriate mapping for the new field
		$company_db->select('map')
				   ->where('report', $report_id)
				   ->order_by('id', 'DESC');
		$last_field = $company_db->get('report_fields')->row()->map;
		$last_field_num = explode('_', $last_field)[1]; // Format is 'col_<num>'
		$field_map = "col_" . ($last_field_num + 1);
		// Find the appropriate maps for the chosen fields
		$field1 = $company_db->get_where('report_fields', array( 'report' => $report->id, 'name' => $field1 ))->row()->map;
		$field2 = $company_db->get_where('report_fields', array( 'report' => $report->id, 'name' => $field2 ))->row()->map;
		// Add the logic column into the report_fields table
		$data = array(
			'report'			=> $report->id,
			'name'				=> $field_name,
			'map'				=> $field_map,
			'type'				=> 'decimal',
			'logic_field1'		=> $field1,
			'logic_operation'	=> $operation,
			'logic_field2'		=> $field2,
			'constant'			=> $constant
		);
		// Add the field to the Form's table
		$field = array(
			$field_map => array( 'type' => 'DECIMAL', 'constraint' => '65, 3', 'null' => TRUE )
		);
		$company_forge->add_column($report->map, $field);
		// Use the constant provided if applicable
		if ($constant)
			$data['logic_field2'] = $field2_constant;
		$company_db->insert('report_fields', $data);
		// Populate the new field for each row
		if (!$constant)
			$company_db->set("`{$field_map}`", "`{$field1}` {$operation} `{$data['logic_field2']}` WHERE `{$field1}` IS NOT NULL AND `{$data['logic_field2']}` IS NOT NULL", FALSE);
		else
			$company_db->set("`{$field_map}`", "`{$field1}` {$operation} {$data['logic_field2']} WHERE `{$field1}` IS NOT NULL", FALSE);
		$company_db->update($report->map);
		// Log the creation of the new logic column
		$this->load->model('nvp_codes_model');
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "Column {$field_name} added to {$report->name}",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Report Logic Added'),
			'date'			=> date('Y-m-d H:i:s')
		);
		$company_db->insert('event_log', $data);
		// Report success
		$this->response("Column added.", 200);
	}

	public function delete_logic_post() {
		// Pull form data
		$report_id = $this->post('report_id');
		$id = $this->post('id');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Report's record from the Report's id
		if (!$report = $company_db->get_where('reports', array( 'id' => $report_id, 'active' => 1 ))->row())
			$this->response("Report not found.", 500);
		// Check if the User has access to the Report
		if ($report->private && $report->creator != $this->session->userdata('user_id') && !is_superuser($this->session->userdata('user_level')))
			$this->response("You do not have access to this report.", 401);
		// Load the database into DBForge
		$company_forge = $this->load->dbforge($company_db, TRUE);
		if (!$column = $company_db->get_where('report_fields', array( 'id' => $id ))->row())
			$this->response("Column not found.", 500);
		// Delete the row from report_fields
		$company_db->delete('report_fields', array( 'id' => $id ));
		// Drop the column from the Report's table
		$company_forge->drop_column($report->map, $column->map);
		// Log the deletion of the logic column
		$this->load->model('nvp_codes_model');
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "Column {$column->name} deleted from {$report->name}",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Report Logic Deleted'),
			'date'			=> date('Y-m-d H:i:s')
		);
		$company_db->insert('event_log', $data);
		// Report success
		$this->response("Column deleted.", 200);
	}

	public function edit_entry_post() {
		// Pull form data
		$report_id = $this->post('report_id');
		$entry_id = $this->post('entry_id');
		$property_id = $this->post('property_id');
		$new_value = $this->post('value');
		$signature = $this->post('signature');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Grab the Report
		$report = $company_db->get_where('reports', array ( 'id' => $report_id ))->row();
		// Grab the Report's fields
		$fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result();
		// Grab the property's full field info
		$property = $company_db->get_where('report_fields', array( 'id' => $property_id ))->row();
		// Check if the property is valid
		$chosen_field = NULL;
		foreach ($fields as $field)
			if ($field->map == $property->map)
				$chosen_field = $field;
		if (!$chosen_field)
			$this->response("Invalid field.", 400);
		// Ensure empty string is null instead
		if ($new_value == "")
			$new_value = NULL;
		// Validate new value
		if ($new_value != NULL) { // Don't bother validating an accepted null value
			switch ($chosen_field->type) {
				case 'decimal':
					if (!is_numeric($new_value))
						$this->response("This field only accepts decimals.", 400);
					break;
				case 'datetime':
					if (!$this->validate_datetime($new_value))
						$this->response("Invalid date.", 400);
					break;
			}
		}
		// Grab the old value of the entry's field for saving in the Event Log
		$entry = $company_db->get_where($report->map, array( 'id' => $entry_id ))->row();
		$old_value = $entry->{$property->map};
		// Update the entry
		$company_db->update($report->map, array( $property->map => $new_value ), array( 'id' => $entry_id ));
		// Update logic columns for the entry as well
		$company_db->where('report', $report->id)
				   ->where('logic_operation IS NOT NULL');
		if ($logics = $company_db->get('report_fields')->result()) {
			foreach ($logics as $logic)
				$logic_columns[] = $logic->map;
			// Use a copy of the old entry as a basis for calculating logic columns, using the new value
			$data = $entry;
			$data->{$property->map} = $new_value;
			// Calculate any logic columns
			foreach ($logics as $logic) {
				// Decide whether to use a field or constant for logic
				if ($logic->constant)
					$field2 = (float) $logic->logic_field2;
				else
					$field2 = $data->{$logic->logic_field2};
				// Calculate the field's value using whatever operation is saved, or NULL if one of the input values is null
				if ($data->{$logic->logic_field1} == NULL || $field2 == NULL) {
					$value = NULL;
				} else {
					switch ($logic->logic_operation) {
						case '+':
							$value = $data->{$logic->logic_field1} + $field2;
							break;
						case '-':
							$value = $data->{$logic->logic_field1} - $field2;
							break;
						case '*':
							$value = $data->{$logic->logic_field1} * $field2;
							break;
						case '/':
							$value = $data->{$logic->logic_field1} / $field2;
							break;
					}
				}
				// Save the caculated result
				$data->{$logic->map} = $value;
			}
			// Make an array of the new logic column values
			$logic_data = array();
			foreach ($data as $data_field => $value)
				if (in_array($data_field, $logic_columns))
					$logic_data[$data_field] = $value;
			// Update the entry's logic columns
			$company_db->update($report->map, $logic_data, array( 'id' => $entry_id ));
		}
		// Decode the image
		$signature = str_replace('data:image/png;base64,', '', $signature);
		$signature = str_replace(' ', '+', $signature);
		$signature = base64_decode($signature);
		// Log the event
		$this->load->model('nvp_codes_model');
		$data = array(
			'user'			=> $this->session->userdata('user_id'),
			'event'			=> "{$report->name}'s #{$entry_id} entry '{$property->name}' field changed from '{$old_value}' to '{$new_value}'",
			'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Report Entry Edited'),
			'date'			=> date('Y-m-d H:i:s'),
			'signature'		=> $signature
		);
		$company_db->insert('event_log', $data);
		// Respond with success
		$this->response("Entry updated.", 200);
	}

	private function validate_time($time) {
		if (!preg_match('/^([1-9]|1[012]):[03]0 [AP]M$/', $time))
			return FALSE;
		return TRUE;
	}

	static private function validate_datetime($datetime) {
		return DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
	}
}
