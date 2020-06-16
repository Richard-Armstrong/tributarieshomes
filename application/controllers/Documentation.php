<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documentation extends CI_Controller {
	/*********************************************************** Main Landing */
	public function index() {
		// Load view
		$this->load->view('landings/documentation');
	}
}
