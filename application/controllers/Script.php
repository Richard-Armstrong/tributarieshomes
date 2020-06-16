<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Script extends CI_Controller {
	public function alert_script($key = 0) {
		if ($key == '12Pxx9Yy') {
			// Load helper for sending email/sms notifications
			$this->load->helper('my_notification');
			// Load User model
			$this->load->model('users_model');
			// Pull the current time and save it for comparisons
			$day_of_the_week = date('N');
			$current_time_string = date('g:i A');
			$current_time = new DateTime();
			// Load all Companies
			$this->load->model('companies_model');
			$companies = $this->companies_model->get_all();
			// Loop through the list of Companies
			foreach ($companies as $company) {
				// Ignore Companies without databases and move on
				if (is_base_company($company->id))
					continue;
				// Load the Company's database
				$company_db = $this->load->database(db_config($company->db), TRUE);
				// Pull list of Form alerts that are time-based
				$alerts = $company_db->get_where('form_alerts', array( 'onentry' => 0 ))->result();
				// Loop through the Company's Form alerts
				foreach ($alerts as $alert) {
					// Skip alerts that aren't set for this time and day
					$days = explode(',', $alert->days);
					if ($current_time_string != $alert->time || !in_array($day_of_the_week, $days))
						continue;
					// Load the alert's Form
					$form = $company_db->get_where('forms', array( 'id' => $alert->form ))->row();
					// Pull the timestamps of the last QUOTA entries of the alert's Form
					$company_db->select('insert_date');
					$company_db->order_by('insert_date', 'DESC');
					$company_db->limit($alert->quota);
					$entry_dates = $company_db->get($form->map)->result_array();
					if ($entry_dates)
						$oldest_entry = new DateTime(array_slice($entry_dates, -1)[0]['insert_date']); // Grabbing the -1th entry prevents indexing issues
					else
						$oldest_entry = (new DateTime())->setTimestamp(0);
					// Find the difference between the current_time and the oldest_entry
					$difference = date_diff($current_time, $oldest_entry, TRUE); // TRUE for absolute (forced positive) value
					// Check if the difference in days exceeds FREQUENCY
					if ($difference->d > $alert->frequency) {
						echo "Alert for {$form->name} form sent.\n"; // Add Alerts being sent to the log
						// Create the subject and message to be sent
						$subject = "{$form->name} Form Alert";
						$message = "The {$form->name} Form has not received at least {$alert->quota} entries within {$alert->frequency} days.";
						// Notify the proper recipient based on number of alerts activated in a row
						if ($alert->repeated < ALERT_REPEAT_LIMIT)
							notify_account($this->users_model->get($alert->primary), $subject, $message);
						else
							notify_account($this->users_model->get($alert->secondary), $subject, $message);
						// Delete one time alerts upon notifying or increment repeated alert notifications count
						$company_db->where('id', $alert->id);
						if ($alert->onetime)
							$company_db->delete('form_alerts');
						else
							$company_db->update('form_alerts', array( 'repeated' => $alert->repeated++ ));
					} else
						$company_db->update('form_alerts', array( 'repeated' => 0 ), array( 'id' => $alert->id )); // End the streak of repeated alert notifications
				}
			}
			// Log the script running in /var/log/cronlog
			echo date('Y-m-d H:i:s') . ": Alert Script ran.\n";
			echo "*************************************\n";
		}
	}

	public function report_script($key = 0) {
		if ($key == '12Pxx9Yy') {
			// Pull the current time and save it for comparisons
			$day_of_the_week = date('N');
			$day_of_the_month = date('j');
			$current_time_string = date('g:i A');
			$last_month = (new DateTime())->modify('first day of last month')->format('Y-m-d H:i:00');
			$last_week = (new DateTime())->sub(new DateInterval('P7D'))->format('Y-m-d H:i:00');
			$last_day = (new DateTime())->sub(new DateInterval('P1D'))->format('Y-m-d H:i:00');
			$yesterday = (new DateTime())->sub(new DateInterval('P1D'))->format('Y-m-d 23:59:59');
			// Load Report operation types
			$this->load->model('nvp_codes_model');
			$number_operations = $this->nvp_codes_model->getCodeValues('Report_Operations_Number');
			$date_operations = $this->nvp_codes_model->getCodeValues('Report_Operations_Date');
			// Load all Companies
			$this->load->model('companies_model');
			$companies = $this->companies_model->get_all();
			// Loop through the list of Companies
			foreach ($companies as $company) {
				// Ignore Companies without databases and move on
				if (is_base_company($company->id))
					continue;
				// Load the Company's database
				$company_db = $this->load->database(db_config($company->db), TRUE);
				// Pull list of Reports
				$reports = $company_db->get_where('reports', array( 'active' => 1 ))->result(); // Don't run inactive Reports
				// Loop through the Company's Reports
				foreach ($reports as $report) {
					// Check whether to skip running the Report based on its type and run time
					if (!$report->static) { // Standard Report
						if ($report->type == $this->nvp_codes_model->get_nvp_code('Report_Types', 'Daily')) {
							if ($current_time_string != $report->time)
								continue;
							$after_this_time = $last_day;
						} elseif ($report->type == $this->nvp_codes_model->get_nvp_code('Report_Types', 'Weekly')) {
							if ($current_time_string != $report->time || $day_of_the_week != $report->day)
								continue;
							$after_this_time = $last_week;
						} else { // Type == 'Monthly'
							if ($current_time_string != $report->time || $day_of_the_month != 1) // Monthly Reports run on the first of each month
								continue;
							$after_this_time = $last_month;
						}
					} else { // Static Reports
						if ($current_time_string != '12:00 AM') // Static Reports run at midnight only
							continue;
						$after_this_time = $last_day;
					}
					// Load the Report's fields
					$report_fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result();
					// Load new Form entries if Report is Static
					if ($report->static) {
						$form_id = $company_db->get_where('form_metadata', array( 'id' => $report_fields[0]->field ))->row()->form; // All fields of a Static Report share Form
						$form_map = $company_db->get_where('forms', array( 'id' => $form_id ))->row()->map;
						// SELECT the ID
						$query = "SELECT id";
						// Loop through the Report's fields to SELECT the Form's fields
						foreach ($report_fields as $report_field) {
							$query .= ", {$company_db->get_where('form_metadata', array( 'id' => $report_field->field ))->row()->map}"; // SELECT each Form field's map
							$query .= " AS {$report_field->map}"; // Alias the field as the Report field's map
						}
						$query .= " FROM {$form_map}";
						// Only pull entries from before yesterday at 23:59:59 and after the last script run (roughly 24 hrs)
						$query .= " WHERE `insert_date` <= '{$yesterday}'";
						$query .= " AND `insert_date` > '{$after_this_time}'";
						// Check against the condition if it exists
						if ($report_fields[0]->condition_option) {
							$condition_field_map = $company_db->get_where('form_metadata', array( 'id' => $report_fields[0]->condition_field ))->row()->map;
							$query .= " AND `{$condition_field_map}` = '{$report_fields[0]->condition_option}'"; // Only pull entries with the condition option
						}
						// Load the Form entries that match our criteria
						$entries = $company_db->query($query)->result();
						// Add each entry to the Static Report
						foreach ($entries as $entry)
							$company_db->insert($report->map, $entry);
						// Don't bother reporting on the Report running in logs or event log - happens for every Static Report at the same time
					} else {
						// Loop through the Report's fields
						$data = array();
						foreach ($report_fields as $report_field) {
							if ($report_field->field && $report_field->operation) { // Standard Report fields
								// Load the metadata of the field to operate on
								$operated_field = $company_db->get_where('form_metadata', array( 'id' => $report_field->field ))->row();
								// Load the operated field's Form
								$form = $company_db->get_where('forms', array( 'id' => $operated_field->form ))->row();
								// Find the Form's field map for 'insert_date'
								$insert_date_map = $company_db->get_where('form_metadata', array( 'form' => $form->id, 'name' => 'insert_date' ))->row()->map;
								// Check against the condition if it exists
								if ($report_field->condition_option) {
									$condition_field_map = $company_db->get_where('form_metadata', array( 'id' => $report_field->condition_field ))->row()->map;
									$company_db->where($condition_field_map, $report_field->condition_option); // Only pull entries with the condition option
								}
								// Decide which operation to run based on the operated field's type and the operation id
								$company_db->where("{$insert_date_map} > '{$after_this_time}'"); // Only pull entries after the appropriate time
								if ($operated_field->type == 'int' || $operated_field->type == 'decimal') {
									switch ($number_operations[$report_field->operation]) {
										case "Summation":
											$company_db->select_sum($operated_field->map, 'res');
											break;
										case "Average":
											$company_db->select_avg($operated_field->map, 'res');
											break;
										case "Minimum":
											$company_db->select_min($operated_field->map, 'res');
											break;
										case "Maximum":
											$company_db->select_max($operated_field->map, 'res');
											break;
									}
								} elseif ($operated_field->type == 'datetime') {
									switch ($date_operations[$report_field->operation]) {
										case "Earliest Date":
											$company_db->select_min($operated_field->map, 'res');
											break;
										case "Latest Date":
											$company_db->select_max($operated_field->map, 'res');
											break;
									}
								}
								// Add the calculated value to the data to insert
								$data[$report_field->map] = $company_db->get($form->map)->row()->res;
								// Force NULL numbers to be 0 instead
								if ($report_field->type == 'decimal' && $data[$report_field->map] == NULL)
									$data[$report_field->map] = 0;
							} elseif ($report_field->logic_operation) { // Report Logic fields
								$field1 = $data[$report_field->logic_field1];
								// Decide whether to use a field or constant for logic
								if ($report_field->constant)
									$field2 = (float) $report_field->logic_field2;
								else
									$field2 = $data[$report_field->logic_field2];
								// Calculate the field's value using whatever operation is saved, or NULL if one of the input values is null
								if ($field1 === NULL || $field2 === NULL) {
									$value = NULL;
								} else {
									switch ($report_field->logic_operation) {
										case '+':
											$value = $field1 + $field2;
											break;
										case '-':
											$value = $field1 - $field2;
											break;
										case '*':
											$value = $field1 * $field2;
											break;
										case '/':
											$value = $field1 / $field2;
											break;
									}
								}
								// Save the caculated result
								$data[$report_field->map] = $value;
							} else { // Field is 'Insert Date'
								$data[$report_field->map] = date('Y-m-d H:i:00');
								$date_map = $report_field->map;
							}
						}
						// Don't create rows with no data whatsoever
						if ($this->is_array_empty($data, $date_map))
							continue;
						// Insert the new Report entry
						$company_db->insert($report->map, $data);

						// Log the event, based on whether the Report is public or private
						if ($report->private) {
							$data = array(
								'user'			=> 0,
								'event'			=> "Private Report {$report->id} run",
								'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Private Report Run'),
								'date'			=> date('Y-m-d H:i:s')
							);
							$company_db->insert('event_log', $data);
						} else {
							$data = array(
								'user'			=> 0,
								'event'			=> "Report {$report->name} run",
								'event_type'	=> $this->nvp_codes_model->get_nvp_code('Event_Types', 'Report Run'),
								'date'			=> date('Y-m-d H:i:s')
							);
							$company_db->insert('event_log', $data);
						}
						echo "{$report->name} Report ran.\n"; // Log any Reports running
					}
				}
			}
			// Log the script running in /var/log/cronlog
			echo date('Y-m-d H:i:s') . ": Report Script ran.\n";
			echo "*************************************\n";
		}
	}

	private function is_array_empty($array, $date_map) {
		$found = FALSE;
		foreach ($array as $key => $element)
			if ($element && $key != $date_map)
				$found = TRUE;
		return !$found;
	}

	public function test_script($key = 0, $id = 0) {
		if ($key == 'daniel') {
			// Set the starting date to run at
			$set_date = "2019-11-1 21:00:00";
			// Create DateTimes for the date to start and end
			$end_date = new DateTime($set_date);
			$start_date = (new DateTime($set_date))->sub(new DateInterval('P1D'));
			// Load Report operation types
			$this->load->model('nvp_codes_model');
			$number_operations = $this->nvp_codes_model->getCodeValues('Report_Operations_Number');
			$date_operations = $this->nvp_codes_model->getCodeValues('Report_Operations_Date');
			// Load the Company's database
			$company_db = $this->load->database($this->session->userdata('user_db'), TRUE);
			// Pull the Report to run
			$report = $company_db->get_where('reports', array( 'id' => $id, 'active' => 1 ))->row();
			// Load the Report's fields
			$report_fields = $company_db->get_where('report_fields', array( 'report' => $report->id ))->result();
			// Loop through the days to run
			for ($i = 1; $i < 29; $i++) { // Set the number of days to run reports on
				// Loop through the Report's fields
				$data = array();
				foreach ($report_fields as $report_field) {
					// Don't try to operate on null fields
					if ($report_field->field && $report_field->operation) {
						// Load the metadata of the field to operate on
						$operated_field = $company_db->get_where('form_metadata', array( 'id' => $report_field->field ))->row();
						// Load the operated field's Form
						$form = $company_db->get_where('forms', array( 'id' => $operated_field->form ))->row();
						// Find the Form's field map for 'insert_date'
						$insert_date_map = $company_db->get_where('form_metadata', array( 'form' => $form->id, 'name' => 'insert_date' ))->row()->map;
						// Check against the condition if it exists
						if ($report_field->condition_option) {
							$condition_field_map = $company_db->get_where('form_metadata', array( 'id' => $report_field->condition_field ))->row()->map;
							$company_db->where($condition_field_map, $report_field->condition_option); // Only pull entries with the condition option
						}
						// Decide which operation to run based on the operated field's type and the operation id
						$company_db->where("`{$insert_date_map}` >= '{$start_date->format('Y-m-d H:i:00')}'"); // Only pull entries after the appropriate time
						$company_db->where("`{$insert_date_map}` < '{$end_date->format('Y-m-d H:i:00')}'"); // Only pull entries before the appropriate time
						if ($operated_field->type == 'int' || $operated_field->type == 'decimal') {
							switch ($number_operations[$report_field->operation]) {
								case "Summation":
									$company_db->select_sum($operated_field->map, 'res');
									break;
								case "Average":
									$company_db->select_avg($operated_field->map, 'res');
									break;
								case "Minimum":
									$company_db->select_min($operated_field->map, 'res');
									break;
								case "Maximum":
									$company_db->select_max($operated_field->map, 'res');
									break;
							}
						} elseif ($operated_field->type == 'datetime') {
							switch ($date_operations[$report_field->operation]) {
								case "Earliest Date":
									$company_db->select_min($operated_field->map, 'res');
									break;
								case "Latest Date":
									$company_db->select_max($operated_field->map, 'res');
									break;
							}
						}
						// Add the calculated value to the data to insert
						$data[$report_field->map] = $company_db->get($form->map)->row()->res;
					} elseif ($report_field->logic_operation) {
						$field1 = $data[$report_field->logic_field1];
						// Decide whether to use a field or constant for logic
						if ($report_field->constant)
							$field2 = (float) $report_field->logic_field2;
						else
							$field2 = $data[$report_field->logic_field2];
						// Calculate the field's value using whatever operation is saved, or NULL if one of the input values is null
						if ($field1 == NULL || $field2 == NULL) {
							$value = NULL;
						} else {
							switch ($report_field->logic_operation) {
								case '+':
									$value = $field1 + $field2;
									break;
								case '-':
									$value = $field1 - $field2;
									break;
								case '*':
									$value = $field1 * $field2;
									break;
								case '/':
									$value = $field1 / $field2;
									break;
							}
						}
						// Save the caculated result
						$data[$report_field->map] = $value;
					} else { // Field is 'Insert Date'
						$data[$report_field->map] = $end_date->format('Y-m-d H:i:00');
						$date_map = $report_field->map;
					}
				}
				$company_db->set($data);
				echo $company_db->get_compiled_insert($report->map, FALSE);
				echo "<br>";
				// Don't create rows with no data whatsoever
				if (!$this->is_array_empty($data, $date_map)) {
					// Insert the new Report entry
					$company_db->insert($report->map);
					echo "{$report->name} Report ran.<br>"; // Log any Reports running
				}
				// Clear out the current query if we didn't insert the data
				$company_db->reset_query();
				// Increment the DateTimes
				$end_date->add(new DateInterval('P1D'));
				$start_date->add(new DateInterval('P1D'));
			}
			// Log the script running
			echo date('Y-m-d H:i:s') . ": Report Script ran.<br>";
			echo "*************************************<br>";
		}
	}

	/*
	public function sql_script($key = 0) {
		if ($key == 'daniel') {
			//$field = array(
			//	'context'	=> 'Event_Types',
			//	'seq'		=> 29,
			//	'display'	=> 'Report Entry Edited',
			//	'theValue'	=> 29,
			//	'altValue'	=> ''
			//);

			//$this->db->insert('nvp_codes', $field);

			$sql = "SELECT db
					FROM companies
					WHERE db IS NOT NULL";

			$databases = $this->db->query($sql)->result();

			foreach ($databases as $database) {
				$company_db = $this->load->database(db_config($database->db), TRUE);
				$company_forge = $this->load->dbforge($company_db, TRUE);

				//$fields = array(
				//	'id' => array(
				//		'type'				=> 'INT',
				//		'constraint'		=> 11,
				//		'unsigned'			=> TRUE,
				//		'null'				=> FALSE,
				//		'auto_increment'	=> TRUE
				//	)
				//);

				//$company_forge->add_field($fields);
				//$company_forge->add_key('id', TRUE);
				//$company_forge->create_table('form_metadata');

				$fields = array(
					'condition_field' => array(
						'type'			=> 'INT',
						'unsigned'		=> TRUE,
						'null'			=> TRUE,
						'after'			=> 'operation'
					)
				);

				$company_forge->add_column('report_fields', $fields);

				$fields = array(
					'condition_option' => array(
						'type'			=> 'VARCHAR',
						'constraint'	=> 100,
						'null'			=> TRUE,
						'after'			=> 'condition_field'
					)
				);

				$company_forge->add_column('report_fields', $fields);

				$sql = "SELECT *
						FROM reports";

				$report_tables = $company_db->query($sql)->result();

				foreach ($report_tables as $report)
					$company_db->update('report_fields', array( 'condition_field' => $report->condition_field, 'condition_option' => $report->condition_option ),
						array( 'report' => $report->id ));

				// Remove conditions from Reports (now in Report Fields)
				$company_forge->drop_column('reports', 'condition_field');
				$company_forge->drop_column('reports', 'condition_option');
			}

			echo "SQL ran";
			exit();
		}
	}
	*/
}
