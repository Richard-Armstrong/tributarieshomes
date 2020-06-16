<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('db_config')) {
	function db_config($db) {
		$config['hostname'] = DB_HOSTNAME;
		$config['username'] = DB_USERNAME;
		$config['password'] = DB_PASSWORD;
		$config['database'] = $db;
		$config['dbdriver'] = "mysqli";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";

		return $config;
	}
}
