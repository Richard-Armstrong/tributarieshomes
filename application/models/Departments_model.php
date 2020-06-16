<?php
class Departments_Model extends MY_Model {
	// Define protected attributes
	public $protected_attributes = array('id');
	public $_table = 'departments';
	public $primary_key = 'id';

	public function get_departments_dropdown($company_id, $header_text = NULL, $header_value = NULL) {
		$data = NULL;
		if ($header_text) {
			$data[$header_value] = $header_text;
		}

		$this->db->order_by('name');
		$this->db->where('company', $company_id);
		$q = $this->db->get('departments');
		if ($q->num_rows() > 0) {
			$dropdowns = $q->result();
			foreach ($dropdowns as $dropdown) {
				$data[$dropdown->id] = $dropdown->name;
			}
		}

		return $data;
	}

	public function get_departments_export($company_id) {
		$sql = "SELECT id, name
				FROM departments
				WHERE company={$company_id}";

		$q = $this->db->query($sql);
		if ($q->num_rows() > 0)
			return $q->result();
		else
			return NULL;
	}
}
