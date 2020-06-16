<?php
class Access_Model extends MY_Model {
	// Define protected attributes
	public $protected_attributes = array('id');
	public $_table = 'access';
	public $primary_key = 'id';

	public function get_access_export($company_id) {
		$sql = "SELECT C.guid, C.name
				FROM access A
				LEFT JOIN companies C ON A.access_to=C.id
				WHERE A.company={$company_id}";

		$q = $this->db->query($sql);
		if ($q->num_rows() > 0)
			return $q->result();
		else
			return NULL;
	}

	public function get_accessible($company_id) {
		$sql = "SELECT access_to
				FROM access A
				WHERE company={$company_id}";

		$q = $this->db->query($sql);
		if ($q->num_rows() > 0) {
			$result = array();
			foreach ($q->result() as $row)
				$result[] = $row->access_to;
			return $result;
		} else
			return NULL;
	}
}
