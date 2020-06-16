<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	/*********************************************************** Main Landing */
	public function index() {
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');

		// Load bios
		$this->load->model('bios_model');
		$data['bio_records'] = $this->bios_model->get_all();

		// Load inventory
		$this->load->model('inventory_model');
		$data['inventory'] = $this->inventory_model->get_active_inventory();


		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('landings/home', $data);
		$this->load->view('includes/footer', $data);
	}




	public function view($id) {

		$this->load->model('inventory_model');
		$inv_record = $this->inventory_model->get($id);

		$dir = getcwd() .'/assets/inventory/' . $inv_record->inv_directory;

		$scanned_directory = array_diff(scandir($dir), array('..', '.'));
		$data['directory'] = $dir;
		$data['records'] = $scanned_directory;

		$data['inv_record'] = $inv_record;

		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('landings/inventory', $data);
		$this->load->view('includes/footer', $data);
	}

	public function demo() {
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('landings/demo', $data);
		$this->load->view('includes/footer', $data);
	}

	/************************************************************ Departments */
	public function department($department_id) {
		// Check if this Department is part of the User's Company
		if (!in_company($this->session->userdata('user_company'), $department_id))
			return show_error("The chosen department is not part of your company.");
		// Check if the User has permission to view this Department
		if (!in_department($this->session->userdata('user_groups'), $department_id) && !is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Attempt to load the User's Company's database
		if (!$company_db = $this->load->database($this->session->userdata('user_db'), TRUE))
			return show_error("Could not load the company database.");
		// Attempt to load list of all active Forms
		$forms = $company_db->get_where('forms', array( 'active' => 1 ))->result();
		// Loop through Forms to see if they are viewable by this Department
		$data['forms'] = array();
		foreach ($forms as $form) {
			// Make an array of the Form's Departments
			$departments = explode(',', $form->department);
			// Provide the Form to the view if the Department has access to it
			if (in_array($department_id, $departments))
				$data['forms'][] = $form;
		}
		// Load Department's record
		$this->load->library('ion_auth');
		$data['group'] = $this->ion_auth->group($department_id)->row();
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Track the User's current Department for navigation
		$this->session->set_userdata('current_department', $department_id);
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('landings/department', $data);
		$this->load->view('includes/footer', $data);
	}

	/****************************************************************** Forms */
	public function create_form($duplicate_form_id = NULL) {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Check if the Company has any Departments
		if (!company_has_departments($this->session->userdata('user_company')))
			return show_error("The company needs to have a department first.");
		// Define default fields
		$data['default_fields'] = array( 'id', 'insert_date', 'creator', 'form_name', 'department_id',  'signature', 'quality_check_date' );
		if ($duplicate_form_id) {
			// Load the User's Company's database
			$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
			// Attempt to pull the Form-to-duplicate's record from the Form-to-duplicate's ID
			if (!$form = $company_db->get_where('forms', array( 'id' => $duplicate_form_id, 'active' => 1 ))->row())
				return show_error("Form to duplicate not found.");
			// Load logic columns (to avoid looking for them)
			$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
			$logic_columns = array();
			foreach ($logics as $logic)
				$logic_columns[] = $logic->name;
			// Load the Form-to-duplicate's field data for recreation
			$company_db->where('form', $form->id)
					   ->order_by('form_seq');
			$data['fields'] = $fields = $company_db->get('form_metadata')->result();
			$offset = 2; // +2 for the first two rows (name, subtitle)
			// Load dropdown data
			$data['dropdowns'] = [NULL, NULL];
			foreach ($fields as $key => $field) {
				// Keep the proper size of the dropdowns array
				if (!in_array($field->name, $data['default_fields']) && !in_array($field->name, $logic_columns))
					$data['dropdowns'][$key + $offset] = NULL;
				if ($field->type == 'dropdown') {
					$company_db->where('form', $form->id)
							   ->where('context', $field->name);
					$options = $company_db->get('dropdown_nvp')->result();
					foreach ($options as $option)
						$data['dropdowns'][$key + $offset][] = $option->display;
				}
			}
			// Load the Form-to-duplicate's logic columns for unsetting from the field data array
			$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
			$logic_columns = array();
			foreach ($logics as $logic)
				$logic_columns[] = $logic->name;
			// Unset fields that aren't part of the original Form creation inputs
			foreach ($fields as $key => $field)
				if (in_array($field->name, $data['default_fields']) || in_array($field->name, $logic_columns))
					unset($fields[$key]);
			// Provide the list of relevant fields to the view
			$data['fields'] = $fields;
			// Provide remaining Form data to the view
			$data['form_name'] = $form->name;
			$data['subtitle'] = $form->subtitle;
			$data['duplicate_departments'] = explode(',', $form->department);
		} else {
			$data['fields'] = NULL;
			$data['dropdowns'] = array();
			$data['form_name'] = NULL;
			$data['subtitle'] = NULL;
			$data['duplicate_departments'] = NULL;
		}
		// Load Departments dropdown
		$this->load->model('departments_model');
		$data['departments'] = $this->departments_model->get_departments_dropdown($this->session->userdata('user_company'));
		// Load Field Types dropdown
		$this->load->model('nvp_codes_model');
		$data['field_types'] = $this->nvp_codes_model->getCodeValues('Field_Types');
		// Define precisions dropdown
		$data['precisions'] = array(
			1	=> ".1",
			2	=> ".01",
			3	=> ".001",
			4	=> ".0001",
			5	=> ".00001",
			6	=> ".000001",
			7	=> ".0000001",
			8	=> ".00000001"
		);
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "Create Form";
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("forms/create", $data);
		$this->load->view("includes/footer", $data);
	}

	public function form($form_id) {
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Check if the User has access to the Form (via user level or Department)
		if (!can_access_form($this->session->userdata('user_level'), $this->session->userdata('user_groups'), $form->department))
			return show_error("You lack permission to access this page.");
		// Grab the Form's field data
		$company_db->where('form', $form->id)
				   ->order_by('form_seq');
		$data['fields'] = $fields = $company_db->get('form_metadata')->result();
		// Load dropdowns for fields if applicable
		foreach ($fields as $field) {
			if ($field->type == 'dropdown') {
				$options = $company_db->get_where('dropdown_nvp', array( 'context' => $field->map, 'form' => $form->id ))->result_array();
				foreach ($options as $option)
					$data['dropdowns'][$field->name][$option['display']] = $option['display'];
			}
		}
		// Define fields to ignore
		$data['default_fields'] = array( 'id', 'insert_date', 'creator', 'signature', 'quality_check_date' );
		// Load logic columns (to avoid looking for them)
		$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
		$data['logic_columns'] = array();
		foreach ($logics as $logic)
			$data['logic_columns'][] = $logic->name;
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "{$form->name} Form";
		$data['form'] = $form;
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('forms/form', $data);
		$this->load->view('includes/footer', $data);
	}

	public function form_entries($form_id) {
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id ))->row())
			return show_error("Form not found.");
		// Check if the User has access to the Form (via user level or Department)
		if (!can_access_form($this->session->userdata('user_level'), $this->session->userdata('user_groups'), $form->department))
			return show_error("You lack permission to access this page.");
		// Add Quality Check Date to entries if form submitted
		if ($this->input->post('quality_check')) {
			$company_db->set('quality_check_date', date('Y-m-d H:i:s'));
			$company_db->where('quality_check_date IS NULL');
			$company_db->update($form->map);
			// Decode the image
			$signature = $this->input->post('signature');
			$signature = str_replace('data:image/png;base64,', '', $signature);
			$signature = str_replace(' ', '+', $signature);
			$signature = base64_decode($signature);
			// Log the event
			$this->load->model('nvp_codes_model');
			$data = array(
				'user'			=> $this->session->userdata('user_id'),
				'event'			=> "Entries of {$form->name} checked for quality",
				'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Quality Check'),
				'date'			=> date('Y-m-d H:i:s'),
				'signature'		=> $signature
			);
			$company_db->insert('event_log', $data);
			// Report and redirect
			$this->session->set_flashdata('message', "Quality Assurance Checked");
			redirect ("main/form_entries/{$form_id}",  'refresh');
		}
		// Define default ordering fields
		$data['order'] = 'insert_date';
		$data['direction'] = 'DESC';
		// Load the Form's entries
		$company_db->order_by($data['order'], $data['direction']);
		$data['entries'] = $company_db->get($form->map)->result();
		// Load array of [user_id]->user_name to convert ids to names
		$this->load->model('users_model');
		$data['users'] = $users_array = $this->users_model->get_user_id_array(NULL, 'All', 0);
		// Replace User IDs in entries with the Users' names and remove signatures
		for ($i = 0; $i < count($data['entries']); $i++) {
			$data['entries'][$i]->creator = $users_array[$data['entries'][$i]->creator];
			unset($data['entries'][$i]->signature);
		}
		// Grab the Form's field data
		$company_db->where('form', $form->id)
				   ->order_by('entry_seq');
		$data['fields'] = $fields = $company_db->get('form_metadata')->result();
		// Load dropdowns for any dropdown fields
		foreach ($fields as $field) {
			if ($field->type == 'dropdown') {
				$options = $company_db->get_where('dropdown_nvp', array( 'context' => $field->map, 'form' => $form->id ))->result();
				foreach ($options as $option)
					$dropdown[$option->display] = $option->display;
				$data['dropdowns'][$field->map] = $dropdown;
			}
		}
		// Load operators dropdown
		$this->load->model('nvp_codes_model');
		$data['operators'] = $this->nvp_codes_model->getCodeValues('Operators');
		// Define fields to ignore
		$data['default_fields'] = array( 'id', 'insert_date', 'creator', 'signature', 'quality_check_date' );
		// Load logic columns (to avoid looking for them)
		$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
		$data['logic_columns'] = array();
		foreach ($logics as $logic)
			$data['logic_columns'][] = $logic->name;
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "{$form->name} Form Entries";
		$data['form'] = $form;
		$data['operations'] = array( '+', '-', '*', '/' );
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('forms/entries', $data);
		$this->load->view('includes/footer', $data);
	}

	public function form_logic($form_id) {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Load the Form's existing logic columns
		$data['records'] = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
		// Find numeric fields of the Form
		$company_db->where('form', $form->id)
				   ->order_by('entry_seq');
		$field_data = $company_db->get('form_metadata')->result();
		$numeric_fields = array();
		foreach ($field_data as $field)
			if (($field->type == 'int' || $field->type == 'decimal') && $field->name != 'creator')
				$numeric_fields[] = $field->name;
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "Form Logic";
		$data['form'] = $form;
		$data['operations'] = array( '+', '-', '*', '/' );
		$data['numeric_fields'] = $numeric_fields;
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("forms/logic", $data);
		$this->load->view("includes/footer", $data);
	}

	public function view_initials($form_id, $entry_id) {
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id ))->row())
			return show_error("Form not found.");
		// Check if the User has access to the Form (via user level or Department)
		if (!can_access_form($this->session->userdata('user_level'), $this->session->userdata('user_groups'), $form->department))
			return show_error("You lack permission to access this page.");
		// Load the Form entry
		$data['form'] = $company_db->get_where($form->map, array( 'id' => $entry_id ))->row();
		// Load the page
		$this->load->view('forms/initials', $data);
	}

	public function edit_form($form_id) {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Grab the Form's field data
		$company_db->where('form', $form->id)
				   ->order_by('form_seq');
		$fields = $company_db->get('form_metadata')->result();
		// Load dropdown data
		$data['dropdowns'] = array();
		foreach ($fields as $field) {
			if ($field->type == 'dropdown') {
				$company_db->where('form', $form->id)
						   ->where('context', $field->name);
				$options = $company_db->get('dropdown_nvp')->result();
				foreach ($options as $option)
					$data['dropdowns'][$field->id][] = $option->display;
			}
		}
		// Define default fields
		$default_fields = array( 'insert_date', 'creator', 'signature', 'quality_check_date' );
		// Load the Form-to-duplicate's logic columns for unsetting from the field data array
		$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
		$logic_columns = array();
		foreach ($logics as $logic)
			$logic_columns[] = $logic->name;
		// Unset fields that aren't part of the original Form creation inputs
		foreach ($fields as $key => $field)
			if (in_array($field->name, $default_fields) || in_array($field->name, $logic_columns))
				unset($fields[$key]);
		// Load Departments dropdown
		$this->load->model('departments_model');
		$data['departments'] = $this->departments_model->get_departments_dropdown($this->session->userdata('user_company'));
		// Define precisions dropdown
		$data['precisions'] = array(
			1	=> ".1",
			2	=> ".01",
			3	=> ".001",
			4	=> ".0001",
			5	=> ".00001",
			6	=> ".000001",
			7	=> ".0000001",
			8	=> ".00000001"
		);
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "Edit {$form->name} Form";
		$data['form'] = $form;
		$data['fields'] = $fields;
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('forms/edit', $data);
		$this->load->view('includes/footer', $data);
	}

	public function form_deactivate($form_id) {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Set validation rules
		$this->form_validation->set_rules('confirm', 'Confirmation', 'required');
		// Based on validation, load views or attempt to deactivate the Form
		if ($this->form_validation->run() === FALSE) {
			// Set the flash data error message if there is an error
			$data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Provide non-loaded data for views
			$data['title'] = "Deactivate {$form->name} Form";
			$data['form'] = $form;
			// Load views
			$this->load->view('includes/header', $data);
			$this->load->view('forms/deactivate', $data);
			$this->load->view('includes/footer', $data);
		} else {
			// Check if the deactivation was confirmed
			if ($this->input->post('confirm') == 'yes') {
				$company_db->where('id', $form->id);
				$deactivated = $company_db->update('forms', array( 'active' => 0 ));
				if (!$deactivated) {
					// Report failure
					$this->session->set_flashdata('message', "Form could not be deactivated");
				} else {
					// Add the Form deactivation to the Event Log
					$this->load->model('nvp_codes_model');
					$data = array(
						'user'			=> $this->session->userdata('user_id'),
						'event'			=> "{$form->name} deactivated",
						'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Form Deactivated'),
						'date'			=> date('Y-m-d H:i:s')
					);
					$company_db->insert('event_log', $data);
					// Report success
					$this->session->set_flashdata('message', "Form deactivated");
				}
			}
			// Redirect to the Department's page
			redirect ("main/department/{$this->session->userdata('current_department')}");
		}
	}

	public function form_import($form_id) {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Attempt to load the User's Company's database
		if (!$company_db = $this->load->database($this->session->userdata('user_db'), TRUE))
			return show_error("Could not load the company database.");
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Define default fields
		$data['default_fields'] = array( 'id', 'insert_date', 'creator', 'form_name', 'department_id', 'signature', 'quality_check_date' );
		// Load logic columns (to avoid showing in the form)
		$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
		$data['logic_columns'] = array();
		foreach ($logics as $logic)
			$data['logic_columns'][] = $logic->name;
		// Load the Form's field data
		$company_db->where('form', $form->id)
				   ->order_by('form_seq');
		$data['fields'] = $company_db->get('form_metadata')->result();
		// Provide non-loaded data for views
		$data['title'] = "Import into {$form->name} Form";
		$data['form'] = $form;
		$data['import_data'] = NULL;
		$data['results'] = NULL;
		$data['errors'] = NULL;
		$data['ready_for_save'] = FALSE;
		$data['message'] = NULL;
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('forms/import', $data);
		$this->load->view('includes/footer', $data);
	}

	public function handle_form_import($form_id) {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Attempt to load the User's Company's database
		if (!$company_db = $this->load->database($this->session->userdata('user_db'), TRUE))
			return show_error("Could not load the company database.");
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Load Form's field data
		$company_db->where('form', $form->id)
				   ->order_by('form_seq');
		$fields = $company_db->get('form_metadata')->result();
		// Strip out carrage returns from MS servers
		$import_data = $this->input->post('import_data');
		$import_data = str_replace(chr(13), '', $import_data);
		$import_data = str_replace('“', '', $import_data);
		$import_data = str_replace('”', '"', $import_data);
		$import_data = str_replace('‟', '"', $import_data);
		// Split the data into an array of line arrays
		$line_array = explode(chr(10), $import_data);
		// Define default fields
		$default_fields = array(
			'id',
			'insert_date',
			'creator',
			'form_name',
			'department_id',
			'signature',
			'quality_check_date'
		);
		// Load logic columns (to avoid showing in the form)
		$logics = $company_db->get_where('form_logic', array( 'form' => $form->id ))->result();
		$logic_columns = array();
		foreach ($logics as $logic)
			$logic_columns[] = $logic->name;
		// Loop through each field of the Form to set line array indexes (ignoring default fields)
		foreach ($fields as $field)
			if (!in_array($field->name, $default_fields) || !in_array($field->name, $default_fields))
				$field_ndx[$field->name] = $this->get_array_index($field->name, $line_array[0]);
		// Loop through line arrays to load import data into an array
		$line_count = 0;
		$results = $errors = array();
		foreach ($line_array as $key => $value) {
			// Convert the line array into an array of values
			$linein = explode(chr(9), $value);
			// Skip the header line when parsing
			if ($line_count > 0) {
				// Empty row for new values
				$row = array();
				// Loop through each field of the Form and fields to the row (ignoring default fields)
				foreach ($fields as $field)
					if (!in_array($field->name, $default_fields))
						$row[$field->name] = $this->SQLEncode($this->get_linein($linein, $field_ndx[$field->name]));
				// Creator of each entry is the User importing the data
				$row['creator'] = $this->session->userdata('user_id');
				// Loop through each field of the Form to check for errors
				foreach ($fields as $field) {
					// Skip default fields and logic columns
					if (!in_array($field->name, $default_fields) && !in_array($field->name, $logic_columns)) {
						// Prepare errors for this field
						$error = '';
						// Ensure empty string is null
						if ($row[$field->name] == "")
							$row[$field->name] = NULL;
						// Catch unaccepted empty values
						if ($row[$field->name] == NULL && $field->required)
							$error .= "Input cannot be empty. ";
						// Skip validation for accepted empty values
						if ($row[$field->name] == NULL && !$field->required)
							continue;
						// Constrain input by the field's max length if applicable
						if ($field->max_length && $field->max_length < strlen($row[$field->name]))
							$error .= "Input longer than field's max length. ";
						// Constrain DATETIMEs
						if ($field->type == 'datetime' && !$this->validate_date($row[$field->name]))
							$error .= "Input not a proper date. Format is mm/dd/YY. ";
						// Constrain INTs to is_integer
						if ($field->type == 'int' && !is_numeric($row[$field->name]))
							$error .= "Input not an integer. ";
						// Constrain DECIMALs to is_numeric
						if ($field->type == 'decimal' && !is_numeric($row[$field->name]))
							$error .= "Input not a decimal. ";
						// Constrain values of dropdowns to match the possible values
						if ($field->type == 'var' && $field->default == 'DROPDOWN') {
							$company_db->select('display');
							$valid_values = $company_db->get_where('dropdown_nvp', array( 'form' => $form->id, 'context' => $field->name ))->result_array();
							if (!in_array($row[$field->name], $valid_values))
								$error .= "Input not a valid dropdown value. ";
						}
						// Save any issues that occurred in the Errors array
						if ($error != '')
							$errors[] = "Error(s) at entry {$line_count}, field {$field->name}: {$error}";
						// If the field is a DateTime, format it for database entry
						if ($field->type == 'datetime')
							$row[$field->name] = date("Y-m-d H:i:00", strtotime($row[$field->name]));
					}
				}
				// Save the entry in results
				$results[] = $row;
			}
			// Increment line count for skipping header
			$line_count++;
		}
		// Check whether to save changes or load views again
		if ($this->input->post('commit_changes')) {
			// Prevent saving if there are errors
			if ($errors) {
				$this->session->set_flashdata('message', "Cannot save - errors present");
				redirect ("main/form_import/{$form_id}", 'refresh');
			}
			// Decode the image
			$signature = $this->input->post('signature');
			$signature = str_replace('data:image/png;base64,', '', $signature);
			$signature = str_replace(' ', '+', $signature);
			$signature = base64_decode($signature);
			// Loop through the results and insert each entry
			foreach ($results as $result) {
				// Add the signature and date to each entry
				$result['signature'] = $signature;
				$result['insert_date'] = date('Y-m-d H:i:s');
				// Calculate any logic columns
				foreach ($logics as $logic) {
					// Decide whether to use a field or constant for logic
					if ($logic->constant)
						$field2 = (float) $logic->field2;
					else
						$field2 = $result[$logic->field2];
					// Calculate the field's value using whatever operation is saved, or NULL if one of the input values is null
					if ($result[$logic->field1] == NULL || $field2 == NULL) {
						$value = NULL;
					} else {
						switch($logic->operation) {
							case '+':
								$value = $result[$logic->field1] + $field2;
								break;
							case '-':
								$value = $result[$logic->field1] - $field2;
								break;
							case '*':
								$value = $result[$logic->field1] * $field2;
								break;
							case '/':
								$value = $result[$logic->field1] / $field2;
								break;
						}
					}
					$result[$logic->name] = $value;
				}
				$company_db->insert($form->map, $result);
			}
			// Add the new Form entry to the Event Log
			$this->load->model('nvp_codes_model');
			$data = array(
				'user'			=> $this->session->userdata('user_id'),
				'event'			=> "Data imported into {$form->name}",
				'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Form Data Imported'),
				'date'			=> date('Y-m-d H:i:s')
			);
			$company_db->insert('event_log', $data);
			// Report success and redirect to the Form's Department
			$this->session->set_flashdata('message', "Form entries successfully imported");
			redirect ("main/department/{$this->session->userdata('current_department')}");
		} else {
			$data['message'] = NULL;
		}
		// Provide non-loaded data for views
		$data['title'] = "Import into {$form->name} Form";
		$data['form'] = $form;
		$data['import_data'] = $import_data;
		$data['results'] = $results;
		$data['errors'] = $errors;
		$data['ready_for_save'] = TRUE;
		$data['fields'] = $fields;
		$data['default_fields'] = $default_fields;
		$data['logic_columns'] = $logic_columns;
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('forms/import', $data);
		$this->load->view('includes/footer', $data);
	}

	/**************************************************************** Private */
	private function SQLEncode($the_string) {
		return str_replace("'", "''", $the_string);
	}

	private function get_array_index($the_field_name, $the_array) {
		$linein = explode(chr(9), $the_array);
		foreach ($linein as $key => $value)
			if (str_replace(' ', '_', strtoupper(trim($value))) == str_replace(' ', '_', strtoupper(trim($the_field_name))))
				return $key;
		return NULL;
	}

	private function get_linein($the_data, $the_index) {
		if (is_null($the_index)) {
			return NULL;
		} else {
			if ($the_index < count($the_data))
				return $the_data[$the_index];
			else
				return NULL;
		}
	}

	private function validate_date($date) {
		$array = explode('/', $date, 3);
		if (count($array) == 3)
			return checkdate($array[0], $array[1], $array[2]);
		else
			return FALSE;
	}
}
