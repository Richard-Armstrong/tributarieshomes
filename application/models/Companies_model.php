<?php
class Companies_Model extends MY_Model {
	// Define protected attributes
	public $protected_attributes = array('id');
	public $_table = 'companies';
	public $primary_key = 'id';

	public function get_companies_dropdown($header_text = NULL, $header_value = NULL) {
		$data = NULL;
		if ($header_text) {
			$data[$header_value] = $header_text;
		}

		$this->db->order_by('name');
		$q = $this->db->get('companies');
		if ($q->num_rows() > 0) {
			$dropdowns = $q->result();
			foreach ($dropdowns as $dropdown) {
				$data[$dropdown->id] = $dropdown->name;
			}
		}

		return $data;
	}
}
