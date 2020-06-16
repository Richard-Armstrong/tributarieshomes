<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);

class Maintenance extends MY_Controller {
	public function index() {
		redirect ('/');
	}

	/************************************************************** Companies */
	public function companies() {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Load Companies list
		$this->load->model('companies_model');
		$this->db->order_by('name');
		$data['records'] = $this->companies_model->get_all();
		// Set the flash data error message if there is an error
		$data['message'] = (validation_errors()
			? validation_errors()
			: $this->session->flashdata('message'));
		// Provide non-loaded data for views
		$data['title'] = 'Companies';
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("admin/companies/list", $data);
		$this->load->view("includes/footer", $data);
	}

	public function add_company() {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Set the flash data error message if there is an error
		$data['message'] = (validation_errors()
			? validation_errors()
			: $this->session->flashdata('message'));
		// Provide non-loaded data for views
		$data['title'] = "Add Company";
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("admin/companies/add", $data);
		$this->load->view("includes/footer", $data);
	}

	public function edit_company($company_id) {
		// Check if the User has access to this page - Superuser or Account Manager accessing own Company
		if (!is_superuser($this->session->userdata('user_level')) &&
			!(is_account_manager($this->session->userdata('user_level')) &&
				$this->session->userdata('user_company') == $company_id))
			return show_error("You lack permission to access this page.");
		// Check if the base Company is being edited
		if (is_base_company($company_id))
			return show_error("The base company may not be edited.");
		// Set validation rules
		$this->form_validation->set_rules('company_name', 'Company Name', 'required|max_length[' . COMPANY_NAME_MAX_LENGTH . ']');
		// Based on validation, load views or create new Company
		if ($this->form_validation->run() === FALSE) {
			// Load Company record
			$this->load->model('companies_model');
			$data['record'] = $this->companies_model->get($company_id);
			// Load array of Company IDs the Company has API access to
			$this->load->model('access_model');
			$data['access_to'] = $this->access_model->get_accessible($company_id);
			// Load list of Companies with databases for editing access
			$data['companies'] = $this->companies_model->get_many_by("db IS NOT NULL");
			// Set the flash data error message if there is an error
			$data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Provide non-loaded data for views
			$data['title'] = 'Edit Company';
			// Load views
			$this->load->view("includes/header", $data);
			$this->load->view("admin/companies/edit", $data);
			$this->load->view("includes/footer", $data);
		} else {
			// Pull form data
			$company_name = trim($this->input->post('company_name'));
			// Attempt to update the Company and decide where to go and what to report based on success or failure
			$this->load->model('companies_model');
			if ($this->companies_model->update($company_id, array( 'name' => $company_name ))) {
				// Edit the Company's access table if edited by a Superuser
				if (is_superuser($this->session->userdata('user_level'))) {
					$this->load->model('access_model');
					$this->access_model->delete_by(array( 'company' => $company_id ));
					$access_to = $this->input->post('companies');
					foreach ($access_to as $accessible)
						$this->access_model->insert(array( 'company' => $company_id, 'access_to' => $accessible ));
				}
				// Find the Company's database name
				$db_name = $this->companies_model->get($company_id)->db;
				// Load the Company's database
				$company_db = $this->load->database(db_config($db_name), TRUE);
				// Add the creation of the new Department to the Event Log
				$this->load->model('nvp_codes_model');
				$data = array(
					'user'			=> $this->session->userdata('user_id'),
					'event'			=> "Company {$this->input->post('company_name')} edited",
					'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Company Edited'),
					'date'			=> date('Y-m-d H:i:s')
				);
				$company_db->insert('event_log', $data);
				$this->session->set_flashdata('message', "Company edited");
				if (is_superuser($this->session->userdata('user_level')))
					redirect ('maintenance/companies');
				else
					redirect ('/');
			} else {
				// Reload the page
				$this->session->set_flashdata('message', "Unable to edit Company");
				redirect ("maintenance/edit_company/{$company_id}", 'refresh');
			}
		}
	}

	public function deactivate_company($company_id) {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the Company is the base Company
		if (is_base_company($company_id))
			return show_error('You cannot deactivate the base Company.');
		// Attempt to deactivate the Company
		$this->load->model('companies_model');
		$deactivated = $this->companies_model->update($company_id, array( 'active' => 0 ));
		// Decide where to go and what to report based on the success of deactivating the Company
		if ($deactivated) {
			// Redirect to the Companies list
			$this->session->set_flashdata('message', "Company deactivated");
			redirect ('maintenance/companies');
		} else {
			// Reload the page
			$this->session->set_flashdata('message', "Unable to deactivate Company");
			redirect ("maintenance/edit_company/{$company_id}", 'refresh');
		}
	}

	public function activate_company($company_id) {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Attempt to activate the Company
		$this->load->model('companies_model');
		$activated = $this->companies_model->update($company_id, array( 'active' => 1 ));
		// Decide where to go and what to report based on the success of activating the Company
		if ($activated) {
			// Redirect to the Companies list
			$this->session->set_flashdata('message', "Company activated");
			redirect ('maintenance/companies');
		} else {
			// Reload the page
			$this->session->set_flashdata('message', "Unable to activate Company");
			redirect ("maintenance/edit_company/{$company_id}", 'refresh');
		}
	}

	/************************************************************ Departments */
	public function departments() {
		// Check if the User has access to this page
		if (!is_account_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Load Departments list
		$this->load->model('departments_model');
		$this->db->order_by('name');
		$this->db->where('company', $this->session->userdata('user_company'));
		$data['records'] = $this->departments_model->get_all();
		// Set the flash data error message if there is an error
		$data['message'] = (validation_errors()
			? validation_errors()
			: $this->session->flashdata('message'));
		// Provide non-loaded data for views
		$data['title'] = 'Departments';
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("admin/departments/list", $data);
		$this->load->view("includes/footer", $data);
	}

	public function add_department() {
		// Check if the User has access to this page
		if (!is_account_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Set validation rules
		$this->form_validation->set_rules('name', 'Department Name', 'required|alpha_dash');
		// Based on form validation, load the page or create the new Department
		if ($this->form_validation->run() === FALSE) {
			// Set the flash data error message if there is an error
			$data['message'] = (validation_errors()
				? validation_errors()
				: $this->session->flashdata('message'));
			// Provide non-loaded data for views
			$data['title'] = 'Add Department';
			// Load views
			$this->load->view("includes/header", $data);
			$this->load->view("admin/departments/add", $data);
			$this->load->view("includes/footer", $data);
		} else {
			// Pull form data
			$group_name = trim($this->input->post('name'));
			$group_description = trim($this->input->post('description'));
			$group_company = $this->session->userdata('user_company');
			// Create the new Department
			$this->load->library('ion_auth');
			$this->ion_auth->create_group($group_name, $group_description, $group_company);
			// Reload the Departments for the header
			$this->ion_auth->where('company', $this->session->userdata('user_company'));
			$this->session->set_userdata('departments', $this->ion_auth->groups()->result());
			// Avoid attempting to log events for the base Company
			if (!is_base_company($this->session->userdata('user_company'))) {
				// Load the User's Company's database
				$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
				// Add the creation of the new Department to the Event Log
				$this->load->model('nvp_codes_model');
				$data = array(
					'user'			=> $this->session->userdata('user_id'),
					'event'			=> "New Department {$group_name} created",
					'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Department Created'),
					'date'			=> date('Y-m-d H:i:s')
				);
				$company_db->insert('event_log', $data);
			}
			// Redirect to the Departments list
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect ("maintenance/departments");
		}
	}

	public function edit_department($id) {
		// Check if the User has access to this page
		if (!is_account_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Redirect to the Departments list if given no ID
		if (!$id || empty($id))
			redirect ("maintenance/departments");
		// Check if a non-Superuser is altering the base groups
		if (!is_superuser($this->session->userdata('user_level')) && ($id == ADMINISTRATION || $id == UNASSIGNED))
			return show_error("You lack permission to access this page.");
		// Check if this Department is part of the User's Company
		if (!in_company($this->session->userdata('user_company'), $id))
			return show_error("The chosen department is not part of your company.");
		// Set validation rules
		$this->form_validation->set_rules('name', 'Department Name', 'required|alpha_dash');
		// Based on form validation, load the page or create the new Department
		if ($this->form_validation->run() === FALSE) {
			// Load the Department's record
			$this->load->model('departments_model');
			$data['record'] = $this->departments_model->get($id);
			// Set the flash data error message if there is an error
			$data['message'] = (validation_errors()
				? validation_errors()
				: $this->session->flashdata('message'));
			// Provide non-loaded data for views
			$data['title'] = 'Edit Department';
			// Load views
			$this->load->view("includes/header", $data);
			$this->load->view("admin/departments/edit", $data);
			$this->load->view("includes/footer", $data);
		} else {
			// Pull form data
			$group_name = trim($this->input->post('name'));
			$group_description = trim($this->input->post('description'));
			$group_company = $this->session->userdata('user_company');
			// Attempt to update the group and set flashdata message based on the success or failure of the update
			$this->load->library('ion_auth');
			if ($this->ion_auth->update_group($id, $group_name, $group_description, $group_company))
				$this->session->set_flashdata('message', "Department saved");
			else
				$this->session->set_flashdata('message', $this->ion_auth->errors());
			// Reload the Departments for the header
			$this->ion_auth->where('company', $this->session->userdata('user_company'));
			$this->session->set_userdata('departments', $this->ion_auth->groups()->result());
			// Avoid attempting to log events for the base Company
			if (!is_base_company($this->session->userdata('user_company'))) {
				// Load the User's Company's database
				$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
				// Add the creation of the new Department to the Event Log
				$this->load->model('nvp_codes_model');
				$data = array(
					'user'			=> $this->session->userdata('user_id'),
					'event'			=> "Department {$group_name} edited",
					'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Department Edited'),
					'date'			=> date('Y-m-d H:i:s')
				);
				$company_db->insert('event_log', $data);
			}
			// Redirect to the Departments list
			redirect ("maintenance/departments");
		}
	}

	public function delete_department($id) {
		// Check if the User has access to this page
		if (!is_account_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Redirect to the Departments list if given no ID
		if (!$id || empty($id))
			redirect ("maintenance/departments");
		// Check if a non-Superuser is altering the base groups
		if ($id == ADMINISTRATION || $id == UNASSIGNED)
			return show_error("You cannot delete the default departments.");
		// Check if this Department is part of the User's Company
		if (!in_company($this->session->userdata('user_company'), $id))
			return show_error("The chosen department is not part of your company.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Remove all Users from this Department
		$this->load->library('ion_auth');
		$users = $this->ion_auth->users($id)->result();
		foreach ($users as $user) {
			$this->ion_auth->remove_from_group($id, $user->id);
			// If the User is part of no Department after removal, add them to Unassigned
			if (!$this->ion_auth->get_users_groups($user->id)->result())
				$this->ion_auth->add_to_group(UNASSIGNED, $user->id);
		}
		// Attempt to delete the Department and report the result
		$this->load->model('departments_model');
		if ($this->departments_model->delete($id))
			$this->session->set_flashdata('message', "Department deleted");
		else
			$this->session->set_flashdata('message', "Department deletion failed");
		// Reload the Departments for the header
		$this->ion_auth->where('company', $this->session->userdata('user_company'));
		$this->session->set_userdata('departments', $this->ion_auth->groups()->result());
		// Avoid attempting to log events for the base Company
		if (!is_base_company($this->session->userdata('user_company'))) {
			// Log the deletion of the Department
			$this->load->model('nvp_codes_model');
			$data = array(
				'user'			=> $this->session->userdata('user_id'),
				'event'			=> "Department {$group_name} deleted",
				'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Department Deleted'),
				'date'			=> date('Y-m-d H:i:s')
			);
			$company_db->insert('event_log', $data);
		}
		// Redirect to the Departments list
		redirect ("maintenance/departments");
	}

	/********************************************************* Change Company */
	public function change_company() {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Load Companies dropdown
		$this->load->model('companies_model');
		$data['companies'] = $this->companies_model->get_companies_dropdown();
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("admin/change_company", $data);
		$this->load->view("includes/footer", $data);
	}

	/****************************************************** Deactivated Forms */
	public function deactivated_forms() {
		// Check if the User has permission to view the deactivated Forms
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Load the list of all inactive Forms
		$data['forms'] = $company_db->get_where('forms', array( 'active' => 0 ))->result();
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Load views
		$this->load->view('includes/header', $data);
		$this->load->view('forms/deactivated_forms', $data);
		$this->load->view('includes/footer', $data);
	}

	public function form_reactivate($form_id) {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's name
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 0 ))->row())
			return show_error("Form not found.");
		// Set validation rules
		$this->form_validation->set_rules('confirm', 'Confirmation', 'required');
		// Based on validation, load views or attempt to reactivate the Form
		if ($this->form_validation->run() === FALSE) {
			// Set the flash data error message if there is an error
			$data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Provide non-loaded data for views
			$data['title'] = "Reactivate {$form->name} Form";
			$data['form_id'] = $form_id;
			// Load views
			$this->load->view('includes/header', $data);
			$this->load->view('forms/reactivate', $data);
			$this->load->view('includes/footer', $data);
		} else {
			// Check if the deactivation was confirmed
			if ($this->input->post('confirm') == 'yes') {
				// Reactivate the Form
				$company_db->where('id', $form_id);
				if (!$company_db->update('forms', array( 'active' => 1 ))) {
					// Report failure
					$this->session->set_flashdata('message', "Form could not be reactivated");
				} else {
					// Add the Form reactivation to the Event Log
					$this->load->model('nvp_codes_model');
					$data = array(
						'user'			=> $this->session->userdata('user_id'),
						'event'			=> "{$form->name} reactivated",
						'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Form Reactivated'),
						'date'			=> date('Y-m-d H:i:s')
					);
					$company_db->insert('event_log', $data);
					// Report success
					$this->session->set_flashdata('message', "Form reactivated");
				}
			}
			// Redirect to the deactivated Forms page
			redirect ("maintenance/deactivated_forms");
		}
	}

	/************************************************************** Event Log */
	public function event_log() {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's current Company
		$this->load->model('companies_model');
		$company = $this->companies_model->get($this->session->userdata('user_company'));
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Define Event filters
		if ($this->input->post('user'))
			$company_db->where('user', $this->input->post('user'));
		if ($this->input->post('start'))
			$company_db->where("DATE_FORMAT(date, '%m/%d/%Y') >=", $this->input->post('start'));
		if ($this->input->post('end'))
			$company_db->where("DATE_FORMAT(date, '%m/%d/%Y') <=", $this->input->post('end'));
		if ($this->input->post('event_type') && $this->input->post('event_type') != -1)
			$company_db->where('event_type', $this->input->post('event_type'));
		// Show (up to) the last 500 Events
		$company_db->order_by('date DESC');
		$company_db->limit(500);
		$data['events'] = $company_db->get('event_log')->result();
		// Load array of [user_id]->user_name to convert ids to names
		$this->load->model('users_model');
		$users_array = $this->users_model->get_user_id_array();
		// Replace User IDs in events with the Users' names
		for ($i = 0; $i < count($data['events']); $i++) {
			if ($data['events'][$i]->user)
				$data['events'][$i]->user = $users_array[$data['events'][$i]->user];
			else
				$data['events'][$i]->user = 'System';
		}
		// Load a list of Users within the Company to filter by
		$data['users'] = $this->users_model->get_user_id_array($this->session->userdata('user_company'), 'All', 0);
		// Load the list of Event Types to filter by
		$this->load->model('nvp_codes_model');
		$data['event_types'] = $this->nvp_codes_model->getCodeValues('Event_Types', -1, "All");
		// Set the flash data error message if there is an error
		$data['message'] = (validation_errors()
			? validation_errors()
			: $this->session->flashdata('message'));
		// Provide non-loaded data for views
		$data['title'] = "Event Log for {$company->name}";
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("admin/event_log", $data);
		$this->load->view("includes/footer", $data);
	}

	public function view_event_initials($entry_id) {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Attempt to load the User's Company's database
		if (!$company_db = $this->load->database($this->session->userdata('user_db'), TRUE))
			return show_error("Could not load the company database.");
		// Load the Form entry
		$data['form'] = $company_db->get_where('event_log', array( 'id' => $entry_id ))->row();
		// Load the page
		$this->load->view('forms/initials', $data);
	}

	/************************************************************** NVP Codes */
	public function nvp_codes($current_context = 'Yes_No') {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Load dropdown of all contexts
		$this->load->model("nvp_codes_model");
		$data['context_list'] = $this->nvp_codes_model->get_context_list();
		// Load the data for the current context
		$this->db->where('context', $current_context);
		$this->db->order_by('seq');
		$data['nvp_data'] = $this->nvp_codes_model->get_all();
		// Provide non-loaded data for views
		$data['title'] ='Edit NVP Codes';
		$data['current_context'] = $current_context;
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("admin/nvp_codes", $data);
		$this->load->view("includes/footer", $data);
	}

	public function handle_nvp_form() {
		// Check if the User has access to this page
		if (!is_superuser($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Pull form data
		$the_action = $this->input->post('the_action');
		$nvp_codes_id = $this->input->post('recordSelected');
		// Load the data to update the Name Value Pair
		$data = array (
				'context'	=> $this->input->post('Update_1'),
				'seq'		=> $this->input->post('Update_2'),
				'display'	=> $this->input->post('Update_3'),
				'theValue'	=> $this->input->post('Update_4'),
				'altValue'	=> $this->input->post('Update_5')
		);
		// Insert, update, or delete based on the action chosen in the view
		$this->load->model('nvp_codes_model');
		if ($the_action == "Create")
			$this->nvp_codes_model->insert($data);
		elseif ($the_action == "Save")
			$this->nvp_codes_model->update($nvp_codes_id, $data, TRUE);
		elseif ($the_action == "Delete")
			$this->nvp_codes_model->delete($nvp_codes_id);
		// Reload the page with the specified context
		redirect("maintenance/nvp_codes/{$this->input->post('Update_1')}", 'refresh');
	}

	/***************************************************************** Serach */
	public function search() {
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Define search filter if provided
		$tables = $names = array(); // Holds the arrays of results for each Form with a match
		if ($search_string = trim($this->input->post('search'))) {
			// Load array of [user_id]->user_name to convert ids to names
			$this->load->model('users_model');
			$users_array = $this->users_model->get_user_id_array();
			// Pull list of the Company's Forms
			$forms = $company_db->get_where('forms', array( 'active' => 1 ))->result();
			// Loop through the Company's Forms
			foreach ($forms as $form) {
				// Pull the list of the Form's fields
				$company_db->where('form', $form->id)
						   ->order_by('form_seq');
				$fields = $company_db->get('form_metadata')->result();
				$has_string_field = FALSE; // Flag to prevent showing Forms without any valid fields (and thus no WHERE clauses)
				// Loop through the fields to find varchars or text fields to match against the search value
				foreach ($fields as $field) {
					if ($field->type == 'varchar' || $field->type == 'text' || $field->type == 'dropdown') {
						$company_db->or_where("{$field->name} LIKE '%{$company_db->escape_like_str($search_string)}%' ESCAPE '!'");
						$has_string_field = TRUE;
					}
				}
				// Grab the result of the search and save it if not empty
				if ($has_string_field && $result = $company_db->get($form->map)->result()) {
					// Remove signatures, replace User IDs with names
					foreach ($result as $result_row) {
						$result_row->creator = $users_array[$result_row->creator];
						unset($result_row->signature);
					}
					$tables[$form->id] = $result;
					$names[$form->id] = $form->name;
				}
			}
		}
		// Provide the search results (or lack thereof) to the view
		$data['tables'] = $tables;
		$data['names'] = $names;
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "Company Search";
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("admin/company_search", $data);
		$this->load->view("includes/footer", $data);
	}

	/********************************************************** Miscellaneous */
	public function php_info() {
		phpInfo();
	}
}
