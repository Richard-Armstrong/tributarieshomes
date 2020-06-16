<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {
	function __construct() {
		parent::__construct();
		// Check if the User has access to these functions
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Check if the Company has any Departments
		if (!company_has_departments($this->session->userdata('user_company')))
			return show_error("The company needs to have a department first.");
	}

	public function index() {
		redirect ('reports/list');
	}

	public function list() {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to load list of all active Reports that are public or owned by the User
		$company_db->where('active', 1)
				   ->group_start()
				   ->where('private', 0)
				   ->or_where('creator', $this->session->userdata('user_id'))
				   ->group_end();
		$data['reports'] = $company_db->get('reports')->result();
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "Reports List";
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('reports/list', $data);
		$this->load->view('includes/footer', $data);
	}

	public function view($id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to load the Report's record
		if (!$report = $company_db->get_where('reports', array( 'id' => $id ))->row())
			return show_error("Report not found.");
		// Check if the User has access to the Report
		if ($report->private && $report->creator != $this->session->userdata('user_id') && !is_superuser($this->session->userdata('user_level')))
			return show_error("You do not have access to this report.");
		// Attempt to load the Report's fields
		if (!$fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result())
			return show_error("Report's fields not found.");
		// Order the entries by insertdate, descending
		$insert_date_map = $company_db->get_where('report_fields', array( 'report' => $report->id, 'name' => 'Insert Date' ))->row()->map;
		$company_db->order_by($insert_date_map, 'DESC');
		// Load the Report's entries
		$entries = $company_db->get($report->map)->result();
		// If the field is a creator field, covnert the id to a name
		$this->load->model('users_model');
		$users_array = $this->users_model->get_user_id_array(NULL);
		foreach ($fields as $report_field)
			if ($report_field->field)
				if ($company_db->get_where('form_metadata', array( 'id' => $report_field->field ))->row()->name == 'creator')
					foreach ($entries as $entry)
						$entry->{$report_field->map} = $users_array[$entry->{$report_field->map}];
		// Define fields to ignore
		$data['default_fields'] = array( 'id', 'Insert Date', 'creator', 'signature', 'quality_check_date' );
		// Load logic columns (to avoid looking for them)
		$data['logic_columns'] = array();
		foreach ($fields as $field)
			if ($field->logic_operation)
				$data['logic_columns'][] = $field->name;
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "View {$report->name} Report";
		$data['report'] = $report;
		$data['fields'] = $fields;
		$data['entries'] = $entries;
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("reports/view", $data);
		$this->load->view("includes/footer", $data);
	}

	public function create($static = 0) {
		// Sanity check to prevent bad HTML inputs
		if ($static != 1)
			$static = 0;
		// Load Departments dropdown
		$this->load->model('departments_model');
		$data['departments'] = $this->departments_model->get_departments_dropdown($this->session->userdata('user_company'), "Please Select", -1);
		// Load Report types dropdown
		$this->load->model('nvp_codes_model');
		$data['types'] = $this->nvp_codes_model->getCodeValues('Report_Types', -1, "Please Select");
		// Define weekday dropdown
		$data['weekdays'] = array(
			1	=> "Monday",
			2	=> "Tuesday",
			3	=> "Wednesday",
			4	=> "Thursday",
			5	=> "Friday",
			6	=> "Saturday",
			7	=> "Sunday"
		);
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "Create Report";
		$data['static'] = $static;
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("reports/create", $data);
		$this->load->view("includes/footer", $data);
	}

	public function logic($id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Report's record from the Report's id
		if (!$report = $company_db->get_where('reports', array( 'id' => $id, 'active' => 1 ))->row())
			return show_error("Report not found.");
		// Check if the User has access to the Report
		if ($report->private && $report->creator != $this->session->userdata('user_id') && !is_superuser($this->session->userdata('user_level')))
			return show_error("You do not have access to this report.");
		// Load the Report's existing logic columns
		$company_db->where('report', $report->id)
				   ->where("logic_operation IS NOT NULL");
		$records = $company_db->get('report_fields')->result();
		// Replace column mappings with proper column names
		foreach ($records as $record) {
			$record->logic_field1 = $company_db->get_where('report_fields', array( 'report' => $report->id, 'map' => $record->logic_field1 ))->row()->name;
			$record->logic_field2 = $company_db->get_where('report_fields', array( 'report' => $report->id, 'map' => $record->logic_field2 ))->row()->name;
		}
		// Find numeric fields of the Report
		$fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result();
		$numeric_fields = array();
		foreach ($fields as $field)
			if ($field->type == 'decimal')
				$numeric_fields[] = $field->name;
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "Report Logic";
		$data['report'] = $report;
		$data['records'] = $records;
		$data['operations'] = array( '+', '-', '*', '/' );
		$data['numeric_fields'] = $numeric_fields;
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("reports/logic", $data);
		$this->load->view("includes/footer", $data);
	}

	public function deactivate($id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to load the Report's record
		if (!$report = $company_db->get_where('reports', array( 'id' => $id, 'active' => 1 ))->row())
			return show_error("Report not found.");
		// Check if the User has access to the Report
		if ($report->private && $report->creator != $this->session->userdata('user_id') && !is_superuser($this->session->userdata('user_level')))
			return show_error("You do not have access to this report.");
		// Set validation rules
		$this->form_validation->set_rules('confirm', 'Confirmation', 'required');
		// Based on validation, load views or attempt to deactivate the Form
		if ($this->form_validation->run() === FALSE) {
			// Set the flash data error message if there is an error
			$data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Provide non-loaded data for views
			$data['title'] = "Deactivate {$report->name} Report";
			$data['report'] = $report;
			// Load views
			$this->load->view('includes/header', $data);
			$this->load->view('reports/deactivate', $data);
			$this->load->view('includes/footer', $data);
		} else {
			// Check if the deactivation was confirmed
			if ($this->input->post('confirm') == 'yes') {
				$company_db->where('id', $report->id);
				$deactivated = $company_db->update('reports', array( 'active' => 0 ));
				if (!$deactivated) {
					// Report failure
					$this->session->set_flashdata('message', "Report could not be deactivated");
				} else {
					// Add the Report deactivation to the Event Log
					$this->load->model('nvp_codes_model');
					$data = array(
						'user'			=> $this->session->userdata('user_id'),
						'event'			=> "{$report->name} deactivated",
						'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Report Deactivated'),
						'date'			=> date('Y-m-d H:i:s')
					);
					$company_db->insert('event_log', $data);
					// Report success
					$this->session->set_flashdata('message', "Report deactivated");
				}
			}
			// Redirect to the Reports list
			redirect ("reports/list", 'refresh');
		}
	}

	public function reactivate($id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to load the Report's record
		if (!$report = $company_db->get_where('reports', array( 'id' => $id, 'active' => 0 ))->row())
			return show_error("Report not found.");
		// Check if the User has access to the Report
		if ($report->private && $report->creator != $this->session->userdata('user_id') && !is_superuser($this->session->userdata('user_level')))
			return show_error("You do not have access to this report.");
		// Set validation rules
		$this->form_validation->set_rules('confirm', 'Confirmation', 'required');
		// Based on validation, load views or attempt to reactivate the Form
		if ($this->form_validation->run() === FALSE) {
			// Set the flash data error message if there is an error
			$data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Provide non-loaded data for views
			$data['title'] = "Reactivate {$report->name} Report";
			$data['report'] = $report;
			// Load views
			$this->load->view('includes/header', $data);
			$this->load->view('reports/reactivate', $data);
			$this->load->view('includes/footer', $data);
		} else {
			// Check if the deactivation was confirmed
			if ($this->input->post('confirm') == 'yes') {
				// Reactivate the Report
				$company_db->where('id', $report->id);
				if (!$company_db->update('reports', array( 'active' => 1 ))) {
					// Report failure
					$this->session->set_flashdata('message', "Report could not be reactivated");
				} else {
					// Add the Report reactivation to the Event Log
					$this->load->model('nvp_codes_model');
					$data = array(
						'user'			=> $this->session->userdata('user_id'),
						'event'			=> "{$report->name} reactivated",
						'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Report Reactivated'),
						'date'			=> date('Y-m-d H:i:s')
					);
					$company_db->insert('event_log', $data);
					// Report success
					$this->session->set_flashdata('message', "Report reactivated");
				}
			}
			// Redirect to the deactivted Reports page
			redirect ("reports/deactivated");
		}
	}

	public function deactivated() {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Load the list of all inactive Reports
		$data['reports'] = $company_db->get_where('reports', array( 'active' => 0 ))->result();
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('reports/deactivated_reports', $data);
		$this->load->view('includes/footer', $data);
	}

	public function rerun($report_id, $entry_id) {
		// Load Report operation types
		$this->load->model('nvp_codes_model');
		$number_operations = $this->nvp_codes_model->getCodeValues('Report_Operations_Number');
		$date_operations = $this->nvp_codes_model->getCodeValues('Report_Operations_Date');
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Report's record from the Report's id
		if (!$report = $company_db->get_where('reports', array( 'id' => $report_id, 'active' => 1 ))->row())
			return show_error("Report not found.");
		// Check if the User has access to the Report
		if ($report->private && $report->creator != $this->session->userdata('user_id') && !is_superuser($this->session->userdata('user_level')))
			return show_error("You do not have access to this report.");
		// Attempt to pull the Report's entry from the entry's id
		if (!$entry = $company_db->get_where($report->map, array( 'id' => $entry_id ))->row())
			return show_error("Report entry not found.");
		$report_insert_date_map = $company_db->get_where('report_fields', array( 'report' => $report->id, 'name' => 'Insert Date' ))->row()->map;
		$run_date = $entry->{$report_insert_date_map};
		// Find the timeframe for the Report to run on
		if ($report->type == $this->nvp_codes_model->get_nvp_code('Report_Types', 'Daily'))
			$after_this_time = (new DateTime($run_date))->sub(new DateInterval('P1D'))->format('Y-m-d H:i:00');
		elseif ($report->type == $this->nvp_codes_model->get_nvp_code('Report_Types', 'Weekly'))
			$after_this_time = (new DateTime($run_date))->sub(new DateInterval('P7D'))->format('Y-m-d H:i:00');
		else // Type == 'Monthly'
			$after_this_time = (new DateTime($run_date))->modify('first day of last month')->format('Y-m-d H:i:00');
		// Load the Report's fields
		$report_fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result();
		// Loop through the Report's fields
		$data = array();
		foreach ($report_fields as $report_field) {
			if ($report_field->field && $report_field->operation) { // Calculate standard Report fields
				// Load the metadata of the field to operate on
				$operated_field = $company_db->get_where('form_metadata', array( 'id' => $report_field->field ))->row();
				// Load the operated field's Form
				$form = $company_db->get_where('forms', array( 'id' => $operated_field->form ))->row();
				// Find the Form's field map for 'insert_date'
				$insert_date_map = $company_db->get_where('form_metadata', array( 'form' => $form->id, 'name' => 'insert_date' ))->row()->map;
				// Check against the condition if it exists
				if ($report_field->condition_option) {
					$condition_field_map = $company_db->get_where('form_metadata', array( 'id' => $report_field->condition_field ))->row()->map;
					$company_db->where($condition_field_map, $report_field->condition_option); // Only pull entries with the condition option
				}
				// Decide which operation to run based on the operated field's type and the operation id
				$company_db->where("{$insert_date_map} > '{$after_this_time}'"); // Only pull entries after the appropriate time
				$company_db->where("{$insert_date_map} <= '{$run_date}'"); // Only pull entries before the Report's run time
				if ($operated_field->type == 'int' || $operated_field->type == 'decimal') {
					switch ($number_operations[$report_field->operation]) {
						case "Summation":
							$company_db->select_sum($operated_field->map, 'res');
							break;
						case "Average":
							$company_db->select_avg($operated_field->map, 'res');
							break;
						case "Minimum":
							$company_db->select_min($operated_field->map, 'res');
							break;
						case "Maximum":
							$company_db->select_max($operated_field->map, 'res');
							break;
					}
				} elseif ($operated_field->type == 'datetime') {
					switch ($date_operations[$report_field->operation]) {
						case "Earliest Date":
							$company_db->select_min($operated_field->map, 'res');
							break;
						case "Latest Date":
							$company_db->select_max($operated_field->map, 'res');
							break;
					}
				}
				// Add the calculated value to the data to insert
				$data[$report_field->map] = $company_db->get($form->map)->row()->res;
				// Force NULL numbers to be 0 instead
				if ($report_field->type == 'decimal' && $data[$report_field->map] == NULL)
					$data[$report_field->map] = 0;
			} elseif ($report_field->logic_operation) { // Calculate Report logic fields
				$field1 = $data[$report_field->logic_field1];
				// Decide whether to use a field or constant for logic
				if ($report_field->constant)
					$field2 = (float) $report_field->logic_field2;
				else
					$field2 = $data[$report_field->logic_field2];
				// Calculate the field's value using whatever operation is saved, or NULL if one of the input values is null
				if ($field1 === NULL || $field2 === NULL) {
					$value = NULL;
				} else {
					switch ($report_field->logic_operation) {
						case '+':
							$value = $field1 + $field2;
							break;
						case '-':
							$value = $field1 - $field2;
							break;
						case '*':
							$value = $field1 * $field2;
							break;
						case '/':
							$value = $field1 / $field2;
							break;
					}
				}
				// Save the caculated result
				$data[$report_field->map] = $value;
			} else { // Field is 'Insert Date'
				$data[$report_field->map] = date($run_date);
			}
		}
		// Update the Report entry with the newly calculated values
		$company_db->set($data)
				   ->where('id', $entry->id)
				   ->update($report->map);
		// Reload the Report's view page
		redirect ("reports/view/{$report_id}", 'refresh');
	}
}
