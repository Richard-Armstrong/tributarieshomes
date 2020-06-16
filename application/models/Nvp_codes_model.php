<?php
class NVP_Codes_Model extends MY_Model {
	// Define protected attributes
	public $protected_attributes = array('id');
	public $_table = 'nvp_codes';
	public $primary_key = 'id';

	/**
	 * Loads the code values for dropdowns.
	 * NOTE: This needs to be in a library somewhere rather than replicated.
	 *
	 * @access	public
	 * @param	none
	 * @return	none
	 */
	public function get_nvp_display($the_context, $the_value) {
		$this->db->where('context', $the_context);
		$this->db->where('theValue', $the_value);
		$q = $this->db->get('nvp_codes');
		if ($q->num_rows() > 0) {
			$data = $q->result();
			return $data[0]->display;
		} else {
			return NULL;
		}
	}

	/**
	 * Loads the code values for dropdowns.
	 *
	 * @access	public
	 * @param	none
	 * @return	none
	 */
	public function getCodeValues($theContext, $header_data = NULL, $header_value = NULL) {
		if ($header_data != NULL)
			$data[$header_data] = $header_value;

		$this->db->where('context', $theContext);
		$this->db->order_by('seq');
		$q = $this->db->get('nvp_codes');
		if ($q->num_rows() > 0) {
			$dropdowns = $q->result();
			foreach ($dropdowns as $dropdown)
				$data[$dropdown->theValue] = $dropdown->display;

			return $data;
		} else {
			return NULL;
		}
	}

	/**
	 * Loads the code displays for dropdowns. DRA 4/18/19
	 *
	 * @access	public
	 * @param	none
	 * @return	none
	 */
	public function get_nvp_display_value_array($theContext, $header_data = NULL, $header_value = NULL) {
		if ($header_data != NULL) {
			$data[$header_data] = $header_value;
		}

		$this->db->where('context', $theContext);
		$this->db->order_by('seq');
		$q = $this->db->get('nvp_codes');
		if ($q->num_rows() > 0) {
			$dropdowns = $q->result();
			foreach ($dropdowns as $dropdown) {
				$data[$dropdown->display] = $dropdown->theValue;
			}

			return $data;
		} else {
			return NULL;
		}
	}

	public function get_nvp_code($theContext, $display) {
		$this->db->where('context', $theContext);
		$this->db->where('display', $display);
		$q = $this->db->get('nvp_codes');
		if ($q->num_rows() > 0) {
			return $q->row()->theValue;
		} else {
			return NULL;
		}
	}

	/**
	 * Returns nvp value data for a dropdown with code supplied header
	 *
	 * @access public
	 * @param theContext
	 * @param default_display
	 * @param default_value
	 *
	 */
	public function get_code_value_dropdown($the_context, $default_display = "", $default_value = "") {
		$data = $this->getCodeValues($the_context);
		if ($default_value <> "") {
			$data[$default_value] = $default_display;
		}

		return $data;
	}

	/**
	 * Returns nvp value data for a dropdown with code supplied header
	 *
	 * @access public
	 * @param theContext
	 * @param default_display
	 * @param default_value
	 *
	 */
	public function get_code_display_dropdown($the_context, $default_display, $default_value) {
		$data = $this->getCodeDisplays($the_context);
		$data[$default_value] = $default_display;

		return $data;
	}

	/**
	 * Loads the code display for dropdowns.
	 *
	 * @access	public
	 * @param	none
	 * @return	none
	 */
	public function getCodeDisplays($theContext) {
		$this->db->where('context', $theContext);
		$this->db->order_by('seq');
		$q = $this->db->get('nvp_codes');
		if ($q->num_rows() > 0) {
			$dropdowns = $q->result();
			foreach ($dropdowns as $dropdown) {
				$data[$dropdown->display] = $dropdown->display;
			}

			return $data;
		} else {
			return NULL;
		}
	}

	/**
	 * Loads the code values for dropdowns.
	 *
	 * @access	public
	 * @param	none
	 * @return	none
	 */
	public function get_context_list() {
		$this->db->distinct();
		$this->db->select('context');
		$q = $this->db->get('nvp_codes');

		if ($q->num_rows() > 0) {
			$dropdowns = $q->result();
			foreach ($dropdowns as $dropdown) {
				$data[$dropdown->context] = $dropdown->context;
			}

			return $data;
		} else {
			return NULL;
		}
	}

	public function get_full_context_array($the_context) {
		$this->db->select('display, theValue, altValue');
		$this->db->where('context',$the_context);
		$this->db->order_by('seq');
		$res = $this->db->get('nvp_codes');

		return $res->result_array();
	}
}
