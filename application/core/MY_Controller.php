<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);
/**
 * A base model to allow for separation between public and private controllers
 */
class MY_Controller extends CI_Controller {
	function __construct() {
		parent::__construct();
		if ($this->session->userdata('is_logged_in') == '')
			redirect ("auth/login");
	}

	public function is_post() {
		return $_SERVER['REQUEST_METHOD'] == 'POST' ? TRUE : FALSE;
	}
}
