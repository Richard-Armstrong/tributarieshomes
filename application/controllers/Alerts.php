<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alerts extends MY_Controller {
	function __construct() {
		parent::__construct();
		// Check if the User has access to these functions
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Check if the User is using the base Company
		if (is_base_company($this->session->userdata('user_company')))
			return show_error("You need to select a company first.");
	}

	public function index() {
		redirect ('/');
	}

	public function list($form_id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Load the Form's alerts
		$data['alerts'] = $company_db->get_where('form_alerts', array( 'form' => $form->id ))->result_array();
		// Replace Yes/No fields with Yes/No
		$this->load->model('nvp_codes_model');
		for ($i = 0; $i < count($data['alerts']); $i++)
			$data['alerts'][$i]['onetime'] = $this->nvp_codes_model->get_nvp_display('Yes_No', $data['alerts'][$i]['onetime']);
		for ($i = 0; $i < count($data['alerts']); $i++)
			$data['alerts'][$i]['onentry'] = $this->nvp_codes_model->get_nvp_display('Yes_No', $data['alerts'][$i]['onentry']);
		// Replace the User IDs with their names
		$this->load->library('ion_auth');
		for ($i = 0; $i < count($data['alerts']); $i++) {
			// Ifs to prevent id 0 causing errors
			if ($data['alerts'][$i]['primary']) {
				$primary = $this->ion_auth->user($data['alerts'][$i]['primary']);
				$data['alerts'][$i]['primary'] = "{$primary->last_name}, {$primary->first_name}";
			} else {
				$data['alerts'][$i]['primary'] = "";
			}
			if ($data['alerts'][$i]['secondary']) {
				$secondary = $this->ion_auth->user($data['alerts'][$i]['secondary']);
				$data['alerts'][$i]['secondary'] = "{$secondary->last_name}, {$secondary->first_name}";
			} else {
				$data['alerts'][$i]['secondary'] = "";
			}
			if ($data['alerts'][$i]['creator']) {
				$creator = $this->ion_auth->user($data['alerts'][$i]['creator']);
				$data['alerts'][$i]['creator'] = "{$creator->last_name}, {$creator->first_name}";
			} else {
				$data['alerts'][$i]['creator'] = "";
			}
		}
		// Set the flash data error message if there is an error
		$data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Provide non-loaded data for views
		$data['title'] = "{$form->name} Form Alerts";
		$data['form'] = $form;
		// Load views
		$this->load->view("includes/header", $data);
		$this->load->view("alerts/list", $data);
		$this->load->view("includes/footer", $data);
	}

	public function add($form_id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Set validation rules
		$this->form_validation->set_rules('primary', 'Primary Target', 'required');
		// Based on validation, load views or create new Form Alert
		if ($this->form_validation->run() === FALSE) {
			// Load Company Users dropdown
			$this->load->model('users_model');
			$data['users'] = $this->users_model->get_user_id_array($this->session->userdata('user_company'), 'Please Select', 0);
			// Set the flash data error message if there is an error
			$data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Provide non-loaded data for views
			$data['title'] = "Add Alert to {$form->name}";
			$data['form'] = $form;
			// Load views
			$this->load->view("includes/header", $data);
			$this->load->view("alerts/add", $data);
			$this->load->view("includes/footer", $data);
		} else {
			// Pull form data and set defaults for missing fields where applicable
			$frequency = $this->input->post('frequency');
			$quota = $this->input->post('quota');
			$primary = $this->input->post('primary');
			$secondary = $this->input->post('secondary');
			$list_of_days = $this->input->post('days[]');
			$time = $this->input->post('time');
			$onetime = $this->input->post('onetime');
			$onentry = $this->input->post('onentry');
			if (!$onetime)
				$onetime = 0;
			if (!$onentry)
				$onentry = 0;
			// Prepare list of days as a string
			$days = "";
			foreach ($list_of_days as $day_index)
				$days .=  $day_index . ",";
			// Prepare data to create new Form Alert
			$data = array(
				'form'		=> $form->id,
				'frequency'	=> $frequency,
				'quota'		=> $quota,
				'primary'	=> $primary,
				'secondary'	=> $secondary,
				'days'		=> $days,
				'time'		=> $time,
				'onetime'	=> $onetime,
				'onentry'	=> $onentry,
				'creator'	=> $this->session->userdata('user_id')
			);
			// Attempt to create the new Form Alert
			if (!$company_db->insert('form_alerts', $data)) {
				// Report failure and redirect to the Department
				$this->session->set_flashdata('message', "Alert unable to be created");
				redirect ("main/department/{$this->session->userdata('current_department')}");
			} else {
				// Add the Form alert creation to the Event Log
				$this->load->model('nvp_codes_model');
				$data = array(
					'user'			=> $this->session->userdata('user_id'),
					'event'			=> "Alert added to {$form->name} Form",
					'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Form Alert Created'),
					'date'			=> date('Y-m-d H:i:s')
				);
				$company_db->insert('event_log', $data);
				// Report success and redirect to the Department
				$this->session->set_flashdata('message', "Alert created");
				redirect ("main/department/{$this->session->userdata('current_department')}");
			}
		}
	}

	public function edit($form_id, $alert_id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Set validation rules
		$this->form_validation->set_rules('primary', 'Primary Target', 'required');
		// Based on validation, load views or update the Form Alert
		if ($this->form_validation->run() === FALSE) {
			// Load the Form Alert's record
			$data['alert'] = $company_db->get_where('form_alerts', array( 'id' => $alert_id ))->row();
			// Format the alert's days as an array for the view
			$data['days'] = explode(',', $data['alert']->days);
			// Load Company Users dropdown
			$this->load->model('users_model');
			$data['users'] = $this->users_model->get_user_id_array($this->session->userdata('user_company'), 'Please Select', 0);
			// Set the flash data error message if there is an error
			$data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Provide non-loaded data for views
			$data['title'] = "Edit Alert of {$form->name}";
			$data['form'] = $form;
			// Load views
			$this->load->view("includes/header", $data);
			$this->load->view("alerts/edit", $data);
			$this->load->view("includes/footer", $data);
		} else {
			// Pull form data and set defaults for missing fields where applicable
			$frequency = $this->input->post('frequency');
			$quota = $this->input->post('quota');
			$primary = $this->input->post('primary');
			$secondary = $this->input->post('secondary');
			$list_of_days = $this->input->post('days[]');
			$time = $this->input->post('time');
			$onetime = $this->input->post('onetime');
			$onentry = $this->input->post('onentry');
			if (!$onetime)
				$onetime = 0;
			if (!$onentry)
				$onentry = 0;
			// Prepare list of days as a string
			$days = "";
			foreach ($list_of_days as $day_index)
				$days .=  $day_index . ",";
			// Prepare data to update the Form Alert
			$data = array(
				'form'		=> $form->id,
				'frequency'	=> $frequency,
				'quota'		=> $quota,
				'primary'	=> $primary,
				'secondary'	=> $secondary,
				'days'		=> $days,
				'time'		=> $time,
				'onetime'	=> $onetime,
				'onentry'	=> $onentry
			);
			// Attempt to update the Form Alert
			$company_db->where('id', $alert_id);
			if (!$company_db->update('form_alerts', $data)) {
				// Report failure and redirect to the Department
				$this->session->set_flashdata('message', "Alert unable to be updated");
				redirect ("main/department/{$this->session->userdata('current_department')}");
			} else {
				// Add the Form alert update to the Event Log
				$this->load->model('nvp_codes_model');
				$data = array(
					'user'			=> $this->session->userdata('user_id'),
					'event'			=> "Alert of {$form->name} Form edited",
					'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Form Alert Edited'),
					'date'			=> date('Y-m-d H:i:s')
				);
				$company_db->insert('event_log', $data);
				// Report success and redirect to the Department
				$this->session->set_flashdata('message', "Alert updated");
				redirect ("main/department/{$this->session->userdata('current_department')}");
			}
		}
	}

	public function delete($form_id, $alert_id) {
		// Load the User's Company's database
		$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
		// Attempt to pull the Form's record from the Form's id
		if (!$form = $company_db->get_where('forms', array( 'id' => $form_id, 'active' => 1 ))->row())
			return show_error("Form not found.");
		// Attempt to delete the Form Alert
		if (!$company_db->delete('form_alerts', array( 'id' => $alert_id ))) {
			// Report failure and redirect to the Department
			$this->session->set_flashdata('message', 'Alert unable to be deleted');
			redirect ("main/department/{$this->session->userdata('current_department')}");
		} else {
			// Report success and redirect to the Department
			$this->session->set_flashdata('message', 'Alert deleted');
			redirect ("main/department/{$this->session->userdata('current_department')}");
		}
	}
}
