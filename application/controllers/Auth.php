<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->library('ion_auth');
		$this->load->helper('language');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');
	}

	/*
	public function test_email() {
		$this->load->model('users_model');
		$user = $this->users_model->get($this->session->userdata('user_id'));
		$this->load->library('email');

		$config['protocol'] = 'SMTP';
		$config['smtp_host'] = 'smtp.sparkpostmail.com';
		$config['smtp_user'] = 'SMTP_Injection';
		$config['smtp_pass'] = '3c72f548-d579-4c56-a04c-5126990ae253';
		$config['smtp_crypto'] = 'tls';
		$config['smtp_port'] = '587';
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");

		for ($i = 0; $i < 10; $i++) {
			$this->email->from('support@bluwaveanalytics.com', 'BluWave Forms');
			$this->email->to($user->email);
			$this->email->subject('Testing Email');
			$this->email->message("This is test email number {$i}.");
			$this->email->send(FALSE);
		}

		echo $this->email->print_debugger();
		exit();
	}
	*/

	public function index() {
		// Check if the User is logged in
		if (!$this->ion_auth->logged_in())
			redirect ('auth/login');
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Set the flash data error message if there is an error
		$this->data['message'] = validation_errors()
			? validation_errors()
			: $this->session->flashdata('message');
		// Load the list of Users
		$this->ion_auth_model->order_by('last_name','asc');
		$users = $this->ion_auth->users()->result();
		foreach ($users as $index => $user)
			if ($this->session->userdata('user_company') != $user->company)
				unset($users[$index]);
		$this->data['users'] = $users;
		// Load the Users' Departments
		foreach ($this->data['users'] as $k => $user)
			$this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
		// Load the display name of the Users' levels
		$this->load->model('nvp_codes_model');
		foreach ($this->data['users'] as $k => $user)
			$this->data['users'][$k]->level = $this->nvp_codes_model->get_nvp_display('User_Levels', $user->level);
		// Load views
		$this->load->view('includes/admin_header', $this->data);
		$this->load->view('auth/index', $this->data);
		$this->load->view('includes/admin_footer', $this->data);
	}

	public function login() {
		// Set validation rules
		$this->form_validation->set_rules('identity', str_replace(':', '', $this->lang->line('login_identity_label')), 'required');
		$this->form_validation->set_rules('password', str_replace(':', '', $this->lang->line('login_password_label')), 'required');
		// Based on validation, try to log in or reload the page
		if ($this->form_validation->run() === TRUE) {
			// Pull form data
			$identity = $this->input->post('identity');
			$password = $this->input->post('password');
			$remember = (bool) $this->input->post('remember');
			// Try to log the User in and decide where to go from there
			if ($this->ion_auth->login($identity, $password, $remember)) {
				// Find the User's Company's database info
				$this->load->model('companies_model');
				$user = $this->ion_auth->user();
				$company = $this->companies_model->get($user->company);
				$user_departments = $this->ion_auth->get_users_groups($user->id)->result();
				// Set userdata
				$this->session->set_userdata('is_logged_in', 1);
				$this->session->set_userdata('user_name', "{$user->first_name} {$user->last_name}");
				$this->session->set_userdata('user_level', $user->level);
				$this->session->set_userdata('user_db', db_config($company->db));
				$this->session->set_userdata('user_company', $company->id);
				$this->session->set_userdata('user_groups', $user_departments);
				// Keep track of the Company's Departments for the header
				$this->ion_auth->where('company', $company->id);
				$this->session->set_userdata('departments', $this->ion_auth->groups()->result());
				// Keep track of the Company's name for Superuser header
				if (is_superuser($this->session->userdata('user_level')))
					$this->session->set_userdata('company_name', $company->name);
				// Single Department employees redirect to that Department, otherwise, home page
				if (!is_company_manager($user->level) && count($user_departments) == 1 &&
					$user_departments[0]->name != 'administration' && $user_departments[0]->name != 'unassigned')
					redirect ("main/department/{$user_departments[0]->id}");
				else
					redirect ('/admin', 'refresh');
			} else {
				// Set flashdata message and reload the page
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect ('auth/login', 'refresh');
			}
		} else {
			// Set the flash data error message if there is an error
			$this->data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Set view form data
			$this->data['identity'] = array(
				'name'			=> 'identity',
				'id'			=> 'identity',
				'class'			=> 'input-block-level',
				'placeholder'	=> 'Email address'
			);
			$this->data['password'] = array(
				'name'			=> 'password',
				'id'			=> 'password',
				'class'			=> 'input-block-level',
				'placeholder'	=> 'Password'
			);
			// Load the page
			$this->load->view('auth/login', $this->data);
		}
	}

	public function logout() {
		// Log the User out
		$this->ion_auth->logout();
		// Redirect to the login page
		$this->session->set_flashdata('message', $this->ion_auth->messages());
		redirect ('/');
	}

	public function change_password() {
		// Check if the User is logged in
		if (!$this->ion_auth->logged_in())
			redirect ('auth/login');
		// Set validation rules
		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules(
			'new',
			$this->lang->line('change_password_validation_new_password_label'),
			"required|" .
			"min_length[{$this->config->item('min_password_length', 'ion_auth')}]|" .
			"max_length[{$this->config->item('max_password_length', 'ion_auth')}]|" .
			"matches[new_confirm]"
		);
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');
		// Based on validation, load views or try to change the User's password
		if ($this->form_validation->run() === FALSE) {
			// Set the flash data error message if there is an error
			$this->data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Set view form data
			$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
			$this->data['old_password'] = array(
				'name' => 'old',
				'id'   => 'old',
				'type' => 'password'
			);
			$this->data['new_password'] = array(
				'name'    => 'new',
				'id'      => 'new',
				'type'    => 'password',
				'pattern' => '^.{'.$this->data['min_password_length'].'}.*$'
			);
			$this->data['new_password_confirm'] = array(
				'name'    => 'new_confirm',
				'id'      => 'new_confirm',
				'type'    => 'password',
				'pattern' => '^.{'.$this->data['min_password_length'].'}.*$'
			);
			$this->data['user_id'] = array(
				'name'  => 'user_id',
				'id'    => 'user_id',
				'type'  => 'hidden',
				'value' => $this->ion_auth->user()->id
			);
			// Load views
			$this->load->view('includes/admin_header', $this->data);
			$this->load->view('auth/change_password', $this->data);
			$this->load->view('includes/admin_footer', $this->data);
		} else {
			// Load the User's identity
			$identity = $this->session->userdata('identity');
			// Pull form data
			$old_password = $this->input->post('old');
			$new_password = $this->input->post('new');
			// Attempt to change the User's password and decide where to go depending on success or failure
			if ($this->ion_auth->change_password($identity, $old_password, $new_password)) {
				// On success, log the User out
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$this->logout();
			} else {
				// On failure, reload the page
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect ('auth/change_password', 'refresh');
			}
		}
	}

	public function forgot_password() {
		// Set validation rules by checking whether identity is username or email
		$identity_column = $this->config->item('identity', 'ion_auth');
		if ($identity_column != 'email')
			$this->form_validation->set_rules('identity', $this->lang->line('forgot_password_identity_label'), 'required');
		else
			$this->form_validation->set_rules('identity', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
		// Based on validation, load views or attempt to email an activation code to the User
		if ($this->form_validation->run() === FALSE) {
			$this->data['type'] = $identity_column;
			// setup the input
			$this->data['identity'] = array(
				'name' => 'identity',
				'id' => 'identity'
			);
			// Define the identity label by checking whether identity is username or email
			if ($identity_column != 'email')
				$this->data['identity_label'] = $this->lang->line('forgot_password_identity_label');
			else
				$this->data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
			// Set any errors
			$this->data['message'] = validation_errors()
				? validation_errors()
				: $this->session->flashdata('message');
			// Load views
			$this->load->view('includes/admin_header', $this->data);
			$this->load->view('auth/forgot_password', $this->data);
			$this->load->view('includes/admin_footer', $this->data);
		} else {
			// Load the User's identity
			$identity = $this->ion_auth->where($identity_column, $this->input->post('identity'))->users()->row();
			// Throw an error if an identity was not found
			if (empty($identity)) {
				// Set the error message based on whether the identity type is 'email' or not
        		if ($identity_column != 'email')
            		$this->ion_auth->set_error('forgot_password_identity_not_found');
				else
					$this->ion_auth->set_error('forgot_password_email_not_found');
				// Reload the page
                $this->session->set_flashdata('message', $this->ion_auth->errors());
        		redirect ("auth/forgot_password", 'refresh');
            }
			// Decide where to go depending on success or failure of sending an activation code
			if ($this->ion_auth->forgotten_password($identity->{$identity_column})) {
				// On success, go to the login page
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect ("auth/login", 'refresh');
			} else {
				// On failure, reload the page
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect ("auth/forgot_password", 'refresh');
			}
		}
	}

	public function reset_password($code = NULL) {
		// Throw an error if a code is not provided
		if (!$code)
			show_404();
		// Find the User associated with the code
		$user = $this->ion_auth->forgotten_password_check($code);
		// Check if the User was found
		if ($user) {
			// Set validation rules
			$this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
			$this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');
			// Based on validation, load views or try to reset the User's password
			if ($this->form_validation->run() === FALSE) {
				// Set the flash data error message if there is an error
				$this->data['message'] = validation_errors()
					? validation_errors()
					: $this->session->flashdata('message');
				// Set view form data
				$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
				$this->data['new_password'] = array(
					'name' => 'new',
					'id'   => 'new',
					'type' => 'password',
					'pattern' => '^.{'.$this->data['min_password_length'].'}.*$'
				);
				$this->data['new_password_confirm'] = array(
					'name'    => 'new_confirm',
					'id'      => 'new_confirm',
					'type'    => 'password',
					'pattern' => '^.{'.$this->data['min_password_length'].'}.*$'
				);
				$this->data['user_id'] = array(
					'name'  => 'user_id',
					'id'    => 'user_id',
					'type'  => 'hidden',
					'value' => $user->id
				);
				$this->data['csrf'] = $this->_get_csrf_nonce();
				$this->data['code'] = $code;
				// Load views
				$this->load->view('includes/admin_header', $this->$data);
				$this->load->view('auth/reset_password', $this->data);
				$this->load->view('includes/admin_footer', $this->$data);
			} else {
				// Check if the request is valid - csrf, matching User ids
				if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id')) {
					// If not valid, clear the code and throw an error
					$this->ion_auth->clear_forgotten_password_code($code);
					show_error($this->lang->line('error_csrf'));
				} else {
					// Load the User's identity
					$identity = $user->{$this->config->item('identity','ion_auth')};
					// Attempt to reset the User's password and decide where to go upon success or failure of resetting the password
					if ($this->ion_auth->reset_password($identity, $this->input->post('new'))) {
						// On success, redirect to the login page
						$this->session->set_flashdata('message', $this->ion_auth->messages());
						redirect ("auth/login", 'refresh');
					} else {
						// On failure, reload the page
						$this->session->set_flashdata('message', $this->ion_auth->errors());
						redirect ("auth/reset_password/{$code}", 'refresh');
					}
				}
			}
		} else {
			// If the code is invalid, send them to the forgotten password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect ("auth/forgot_password", 'refresh');
		}
	}

	public function activate($id, $code = FALSE) {
		// Check if this request came from an email or the Users list - ensure permissions to activate
		$target_level = $this->ion_auth->user($id)->level;
		if ($code !== FALSE) {
			// Activate using the code if accessed from an email
			$activation = $this->ion_auth->activate($id, $code);
		} elseif (compare_user_level($this->session->userdata('user_level'), $target_level, TRUE)) {
			// Activate if the User's level matches or exceeds the User being activated
			$activation = $this->ion_auth->activate($id);
		}
		// Decide where to redirect based on the success or failure of activation
		if ($activation) {
			// On success, redirect to Auth - refresh for Admins, login for email activation
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("auth", 'refresh');
		} else {
			// On failure, the email activation redirects to the forgotten password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect("auth/forgot_password", 'refresh');
		}
	}

	public function deactivate($id = NULL) {
		// Check if the User is logged in
		if (!$this->ion_auth->logged_in())
			redirect ('auth/login');
		// Make sure the ID is a proper int via casting
		$id = (int) $id;
		// Determine the User level of the User to be deactivated
		$target_level = $this->ion_auth->user($id)->level;
		// Check if the User has permission to deactivate the target
		if (!compare_user_level($this->session->userdata('user_level'), $target_level, TRUE))
			return show_error('You lack permission to access this page.');
		// Set validation rules
		$this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
		$this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');
		// Based on validation, load views or attempt to deactivate the User
		if ($this->form_validation->run() === FALSE) {
			// Insert a csrf check
			$this->data['csrf'] = $this->_get_csrf_nonce();
			$this->data['user'] = $this->ion_auth->user($id);
			// Load views
			$this->load->view('includes/admin_header', $this->data);
			$this->load->view('auth/deactivate_user', $this->data);
			$this->load->view('includes/admin_footer', $this->data);
		} else {
			// Check if the deactivation was confirmed
			if ($this->input->post('confirm') == 'yes') {
				// Check if the request is valid - csrf, matching User ids
				if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
					show_error($this->lang->line('error_csrf'));
				// Deactivate the User
				$this->ion_auth->deactivate($id);
			}
			// Redirect to the Users page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect ('auth', 'refresh');
		}
	}

	public function create_user() {
		// Check if the User is logged in
		if (!$this->ion_auth->logged_in())
			redirect ('auth/login');
		// Check if the User has access to this page
		if (!is_company_manager($this->session->userdata('user_level')))
			return show_error("You lack permission to access this page.");
		// Load the config values for tables to abstract table names
        $tables = $this->config->item('tables', 'ion_auth');
		// Decide which column is to be used as the identity of the User
        $identity_column = $this->config->item('identity', 'ion_auth');
        // Set validation rules
        $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required');
        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required');
        if ($identity_column !== 'email') {
            $this->form_validation->set_rules(
				'identity',
				$this->lang->line('create_user_validation_identity_label'),
				"required|is_unique[{$tables['users']}.{$identity_column}]"
			);
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email');
        } else {
            $this->form_validation->set_rules(
				'email',
				$this->lang->line('create_user_validation_email_label'),
				"required|valid_email|is_unique[{$tables['users']}.email]"
			);
        }
        $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'trim');
        $this->form_validation->set_rules(
			'password',
			$this->lang->line('create_user_validation_password_label'),
			"required|" .
			"min_length[{$this->config->item('min_password_length', 'ion_auth')}]|" .
			"max_length[{$this->config->item('max_password_length', 'ion_auth')}]|" .
			"matches[password_confirm]"
		);
        $this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');
		// If validation is successful, load data to attempt to register the new User
        if ($this->form_validation->run() === TRUE) {
            $email    = strtolower($this->input->post('email'));
            $identity = ($identity_column === 'email')
				? $email
				: $this->input->post('identity');
            $password = $this->input->post('password');
            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name'  => $this->input->post('last_name'),
                'company'    => $this->input->post('company'),
                'phone'      => $this->input->post('phone')
            );
			// Prevent setting a lower user level than the current User's
			if ($this->input->post('user_level') < $this->session->userdata('user_level'))
				$additional_data['level'] = $this->session->userdata('user_level');
			else
				$additional_data['level'] = $this->input->post('user_level');
			// If the User lacks the sufficient level, set the Company to the User's own
			if (!is_superuser($this->session->userdata('user_level')))
				$additional_data['company'] = $this->ion_auth->user()->company;
        }
		// Prevent Company = 'Please Select' by Superusers
		if (isset($additional_data) && $additional_data['company'] == 0) {
			// Refresh the page
            $this->session->set_flashdata('message', "Must select a company.");
            redirect ("auth/create_user", 'refresh');
		}
		// Attempt to register the new User on passing validation
        if ($this->form_validation->run() === TRUE && $id = $this->ion_auth->register($identity, $password, $email, $additional_data)) {
			// Pull the groups the User is to be part of - happens after the User is created
			$groupData = $this->input->post('groups');
			// Ensure groups have been chosen before attempting to update
			if (isset($groupData) && !empty($groupData)) {
				// Clear the User's groups before updating
				$this->ion_auth->remove_from_group('', $id);
				// Add the User to each posted group
				foreach ($groupData as $grp)
					$this->ion_auth->add_to_group($grp, $id);
			}
			// Load the database of the Company the User is being added to
			$this->load->model('companies_model');
			$company = $this->companies_model->get($additional_data['company']);
			// Log the User creation if the Company has a database
			if ($company->db) {
				$db_info = db_config($company->db);
				$company_db = $this->load->database($db_info, TRUE);
				// Add the creation of the new User to that User's Company's Event Log
				$this->load->model('nvp_codes_model');
				$data = array(
					'user'			=> $this->session->userdata('user_id'),
					'event'			=> "New User {$email} created",
					'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'User Created'),
					'date'			=> date('Y-m-d H:i:s')
				);
				$company_db->insert('event_log', $data);
			}
			// Redirect to the Users list
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect ("auth", 'refresh');
        } else {
            // Set the flash data error message if there is an error
            $this->data['message'] = validation_errors()
				? validation_errors()
				: $this->ion_auth->errors()
					? $this->ion_auth->errors()
					: $this->session->flashdata('message');
			// Load Companies dropdown
			$this->load->model('companies_model');
			$this->data['companies'] = $this->companies_model->get_companies_dropdown('Please Select', 0);
			// Load list of Departments for the User's Company only
			$groups = $this->ion_auth->groups()->result_array();
			foreach ($groups as $index => $group)
				if ($group['company'] != $this->session->userdata('user_company') && $group['company'] != BLUWAVE)
					unset($groups[$index]);
			$this->data['groups'] = $groups;
			// Load User level dropdown
			$this->load->model('nvp_codes_model');
			$user_levels = $this->nvp_codes_model->getCodeValues('User_Levels');
			// Remove User levels above the actor's level
			foreach ($user_levels as $index => $level)
				if ($this->session->userdata('user_level') > $index)
					unset($user_levels[$index]);
			$this->data['user_levels'] = $user_levels;
			// Load views
			$this->load->view('includes/admin_header', $this->data);
			$this->load->view('auth/create_user', $this->data);
			$this->load->view('includes/admin_footer', $this->data);
        }
    }

	public function edit_user($id = NULL) {
		// Check if the User is logged in
		if (!$this->ion_auth->logged_in())
			redirect ('auth/login');

			if ($id == NULL ) {
				$id = $this->session->userdata('user_id');
			}
		// Pull the User to update
		$user = $this->ion_auth->user($id);
		// Check if the User has permission to edit this User
		if (!compare_user_level($this->session->userdata('user_level'), $user->level) && !$this->ion_auth->user()->id == $id)
			return show_error('You lack permission to access this page.');
		// Set validation rules
		$this->form_validation->set_rules('email', $this->lang->line('edit_user_validation_email_label'), 'required|valid_email');
		$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'required');
		$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'required');
		$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'required');
		// Check for $_POST data before moving on to csrf validation and form validation
		if (isset($_POST) && !empty($_POST)) {
			// Check if the request is valid - csrf, matching User ids
			if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
				show_error($this->lang->line('error_csrf'));
			// Add extra validation rules if the password was changed
			if ($this->input->post('password')) {
				$this->form_validation->set_rules(
					'password',
					$this->lang->line('edit_user_validation_password_label'),
					"required|min_length[{$this->config->item('min_password_length', 'ion_auth')}]|" .
					"max_length[{$this->config->item('max_password_length', 'ion_auth')}]|" .
					"matches[password_confirm]"
				);
				$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
			}
			// Check if validation was passed
			if ($this->form_validation->run() === TRUE) {
				$data = array(
					'email'			=> $this->input->post('email'),
					'first_name'	=> $this->input->post('first_name'),
					'last_name'		=> $this->input->post('last_name'),
					'company'		=> $this->input->post('company'),
					'phone'			=> $this->input->post('phone')
				);
				// Update the password if it was posted and the User is at least an Account Manager or the User being edited
				if ($this->input->post('password') && (is_account_manager($this->session->userdata('user_level')) || $this->ion_auth->user()->id == $id))
					$data['password'] = $this->input->post('password');
				// If the User lacks the sufficient level, set the Company to the User's own
				if (!is_superuser($this->session->userdata('user_level')))
					$data['company'] = $this->ion_auth->user()->company;
				// Only allow updating notification settings if superuser or editing own User
				if (is_superuser($this->session->userdata('user_level')) || $this->ion_auth->user()->id == $id) {
					$data['notify_sms'] = $this->input->post('notify_sms');
					$data['notify_email'] = $this->input->post('notify_email');
				}
				// Only allow updating groups if User matches or exceeds the targetted User level
				if (compare_user_level($this->session->userdata('user_level'), $user->level, TRUE) && is_company_manager($this->session->userdata('user_level'))) {
					// Pull the groups the User is to be part of
					$groupData = $this->input->post('groups');
					// Ensure groups have been chosen before attempting to update
					if (isset($groupData) && !empty($groupData)) {
						// Clear the User's groups before updating
						$this->ion_auth->remove_from_group('', $id);
						// Add the User to each posted group
						foreach ($groupData as $grp)
							$this->ion_auth->add_to_group($grp, $id);
					}
					// Attempt to update the User's level and decide messages based on the success or failure
					if ($this->ion_auth->update_user_level($id, $this->input->post('user_level')))
						$this->session->set_flashdata('message', $this->ion_auth->messages());
				    else
					    $this->session->set_flashdata('message', $this->ion_auth->errors());
				}
				// Attempt to update the User and decide messages based on the success or failure
				if ($this->ion_auth->update($user->id, $data))
				    $this->session->set_flashdata('message', $this->ion_auth->messages());
			    else
				    $this->session->set_flashdata('message', $this->ion_auth->errors());
				// Keep track of password changes
				if (isset($data['password']) && !empty($data['password']) && $data['company'] != BLUWAVE) {
					// Load the database of the Company of the User being edited
					$this->load->model('companies_model');
					$db_info = db_config($this->companies_model->get($data['company'])->db);
					$company_db = $this->load->database($db_info, TRUE);
					// Log the password change
					$this->load->model('nvp_codes_model');
					$data = array(
						'user'			=> $this->session->userdata('user_id'),
						'event'			=> "User {$data['email']}'s password changed by admin",
						'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Admin Password Change'),
						'date'			=> date('Y-m-d H:i:s')
					);
					$company_db->insert('event_log', $data);
				}
				// Redirect to the Users list if an admin, base url if not
				if (is_company_manager($this->session->userdata('user_level')))
					redirect('auth', 'refresh');
				else
					redirect('/', 'refresh');
			}
		}
		// Load User level dropdown
		$this->load->model('nvp_codes_model');
		$user_levels = $this->nvp_codes_model->getCodeValues('User_Levels');
		// Remove User levels above the actor's level
		foreach ($user_levels as $index => $level)
			if ($this->session->userdata('user_level') > $index)
				unset($user_levels[$index]);
		$this->data['user_levels'] = $user_levels;
		// Set the flash data error message if there is an error
		$this->data['message'] = validation_errors()
			? validation_errors()
			: $this->ion_auth->errors()
				? $this->ion_auth->errors()
				: $this->session->flashdata('message');
		// Load Companies dropdown
		$this->load->model('companies_model');
		$this->data['companies'] = $this->companies_model->get_companies_dropdown();
		// Load list of Departments for the User's Company only
		$groups = $this->ion_auth->groups()->result_array();
		foreach ($groups as $index => $group)
			if ($group['company'] != $this->session->userdata('user_company') && $group['company'] != BLUWAVE)
				unset($groups[$index]);
		// Load list of Departments of the User being edited for the User's Company only
		$currentGroups = $this->ion_auth->get_users_groups($id)->result();
		// Provide non-loaded data for views
		$this->data['user'] = $user;
		$this->data['groups'] = $groups;
		$this->data['currentGroups'] = $currentGroups;
		$this->data['csrf'] = $this->_get_csrf_nonce();
		// Load views
		$this->load->view('includes/admin_header', $this->data);
		$this->load->view('auth/edit_user', $this->data);
		$this->load->view('includes/admin_footer', $this->data);
	}

	private function _get_csrf_nonce() {
		// Generate a csrf
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}

	private function _valid_csrf_nonce() {
		// Return whether or not the csrf is valid
		if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
			$this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
