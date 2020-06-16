<?php
class Inventory_Model extends MY_Model {
	// Define protected attributes
	public $protected_attributes = array('id');
	public $_table = 'Inventory';
	public $primary_key = 'id';

	public function get_active_inventory() {
		$query = $this->db->select('id, inv_name,inv_directory, inv_description,landing_image,flythru_link,inv_desc_short, seq')
                  ->where('active_flag', 1)
                  ->order_by('seq')
                  ->get('inventory');
		return($query->result());
	}

}
