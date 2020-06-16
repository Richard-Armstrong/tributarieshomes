<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('in_department')) {
	function in_department($user_groups, $department_id) {
		$groups = array();
		foreach ($user_groups as $user_group)
			$groups[] = $user_group->id;
		return in_array($department_id, $groups);
	}
}

if (!function_exists('in_company')) {
	function in_company($company_id, $department_id) {
		$instance =& get_instance();
		$instance->load->model('departments_model');
		return $instance->departments_model->get($department_id)->company == $company_id;
	}
}

if (!function_exists('compare_user_level')) {
	function compare_user_level($user_level, $target_user_level, $equal = FALSE) {
		if ($equal)
			return $user_level <= $target_user_level;
		else
			return $user_level < $target_user_level;
	}
}

if (!function_exists('is_superuser')) {
	function is_superuser($user_level) {
		return $user_level <= SUPERUSER;
	}
}

if (!function_exists('is_account_manager')) {
	function is_account_manager($user_level) {
		return $user_level <= ACCOUNT_MANAGER;
	}
}

if (!function_exists('is_company_manager')) {
	function is_company_manager($user_level) {
		return $user_level <= COMPANY_MANAGER;
	}
}

if (!function_exists('is_base_company')) {
	function is_base_company($company_id) {
		$instance =& get_instance();
		$instance->load->model('companies_model');
		$company = $instance->companies_model->get($company_id);
		return $company->db == NULL;
	}
}

if (!function_exists('company_has_departments')) {
	function company_has_departments($company_id) {
		$instance =& get_instance();
		$instance->load->library('ion_auth');
		$instance->ion_auth->where('company', $company_id);
		if ($instance->ion_auth->groups()->result())
			return TRUE;
		else
			return FALSE;
	}
}

if (!function_exists('can_access_form')) {
	function can_access_form($user_level, $user_groups, $form_departments) {
		if (is_company_manager($user_level))
			return TRUE;
		$departments = explode(',', $form_departments);
		foreach ($user_groups as $group)
			if (in_array($group->id, $departments))
				return TRUE;
		return FALSE;
	}
}
