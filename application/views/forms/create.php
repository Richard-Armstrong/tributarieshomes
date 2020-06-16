
<script>
	/***************************************************************** Fields */
	<?php if (!$fields) : ?>
		var count = 2; // Keep track of the number of added fields (plus the two initial rows)
	<?php else : ?>
		var count = <?= count($fields) ?> + 2;
	<?php endif ?>

	var default_fields = <?= json_encode($default_fields) ?>;
	var field_types = <?= json_encode($field_types) ?>;
	var precision_choices = <?= json_encode($precisions) ?>;

	function add_field() {
		var table, row, cell1, cell2, cell3, cell4, cell5, cell2HTML;
		// Find the table
		table = document.getElementById('fields_table');
		// Add a row at the end of the table
		row = table.insertRow(-1);
		// Initialize each cell of the row
		cell1 = row.insertCell(0);
		cell2 = row.insertCell(1);
		cell3 = row.insertCell(2);
		cell4 = row.insertCell(3);
		cell5 = row.insertCell(4);
		// Generate the types dropdown
		cell2HTML = "<select name='types[" + count + "]' onchange='change_row(" + count + ")'>";
		for (const [key, option] of Object.entries(field_types))
			cell2HTML += "<option value='" + key + "'>" + option + "</option>";
		cell2HTML += "</select>";
		// Define the contents of each cell
		cell1.innerHTML = "<input type='text' class='form-control' name='names[" + count + "]' placeholder='Field Name'/>";
		cell2.innerHTML = cell2HTML;
		cell3.innerHTML = "<input type='text' class='form-control' name='lengths[" + count + "]' placeholder='Max Field Length (optional)'/>";
		cell4.innerHTML = "Required? <input type='checkbox' name='required[" + count + "]'/>";
		cell5.innerHTML = "<button type='button' class='btn btn-primary btn-sm' onclick='delete_row(" + count + ")'>Delete</button>";
		// Increment the row count
		count++;
	}

	function change_row(id) {
		var table, trs, row, tds, field_select, type, cell3HTML;
		var type_cell = 1;
		var option_cell = 2;
		// Find the table row to alter
		table = document.getElementById('fields_table');
		trs = table.getElementsByTagName('tr');
		row = trs[id];
		// Grab each cell of the row
		tds = row.getElementsByTagName('td');
		// Find the row's new Field Type
		field_select = tds[type_cell].getElementsByTagName('select')[0];
		type = field_select[field_select.selectedIndex].value;
		// Show/Hide options based on type
		if (type == 'DROPDOWN') {
			cell3HTML = "<button type='button' class='btn btn-primary btn-sm' onclick='open_modal(" + id + ")'>Edit Dropdown</button>";
		} else if (type == 'VARCHAR') {
			cell3HTML = "<input type='text' class='form-control' name='lengths[" + id + "]' placeholder='Max Field Length (optional)'/>";
		} else if (type == 'DECIMAL') {
			cell3HTML = "<select class='form-control' name='precisions[" + id + "]'>";
			for (const [index, value] of Object.entries(precision_choices))
				cell3HTML += "<option value='" + index + "'>" + value + "</option>";
			cell3HTML += "</select>";
		} else {
			cell3HTML = "";
		}
		// Set the new cell contents
		tds[option_cell].innerHTML = cell3HTML;
		// If the newly selected type is not a dropdown, unset dropdowns[] at the index in case it was a dropdown before
		if (type != 'DROPDOWN')
			dropdowns[id] = null;
	}

	function delete_row(row) {
		var table, trs, x, tds, y;
		var id = parseInt(row);
		table = document.getElementById('fields_table');
		// Delete the row and decrement the row count
		table.deleteRow(id);
		count--;
		// Reorder all rows below the deleted row
		trs = table.getElementsByTagName('tr');
		for (x = id; x < trs.length; x++) {
			tds = trs[x].getElementsByTagName('td');
			// Reorder names field
			tds[0].firstElementChild.name = 'names[' + String(x) + ']';
			// Reorder types field
			tds[1].firstElementChild.name = 'types[' + String(x) + ']';
			tds[1].firstElementChild.setAttribute('onchange', 'change_row(' + String(x) + ')');
			// Reorder options field
			if (tds[2].firstElementChild && tds[2].firstElementChild.tagName == 'INPUT')
				tds[2].firstElementChild.name = 'lengths[' + String(x) + ']';
			if (tds[2].firstElementChild && tds[2].firstElementChild.tagName == 'SELECT')
				tds[2].firstElementChild.name = 'precisions[' + String(x) + ']';
			// Reorder required field
			tds[3].firstElementChild.name = 'required[' + String(x) + ']';
			// Reorder delete button
			tds[4].firstElementChild.setAttribute('onclick', 'delete_row(' + String(x) + ')');
		}
		// Reorder the dropdown array
		for (y = id; y < dropdowns.length - 1; y++)
			dropdowns[y] = dropdowns[y + 1];
		// Remove the old final index
		dropdowns.splice(y, 1);
	}

	/*************************************************************** Dropdown */
	<?php if (count($dropdowns)) : ?>
		var dropdowns = <?= json_encode($dropdowns) ?>;
	<?php else : ?>
		var dropdowns = [];
	<?php endif ?>

	function open_modal(id) {
		var table_html;
		// Check if there is already a dropdown for this field
		if (typeof dropdowns[id] !== 'undefined' && dropdowns[id] != null && dropdowns[id].length) {
			// Use the existing dropdown data to populate the modal
			table_html = "";
			for (var i = 0; i < dropdowns[id].length; i++) {
				table_html += "<tr>";
				table_html += "<td><input type='text' class='form-control' name='dropdown_options[" + i + "]' placeholder='Option' value='" + dropdowns[id][i] + "'/></td>";
				table_html += "<td><button type='button' class='btn btn-primary btn-sm' onclick='delete_option(" + i + ")'>Delete</button></td>";
				table_html += "</tr>";
			}
			document.getElementById('modal_count').value = dropdowns[id].length - 1;
		} else {
			// Initialize the modal table
			table_html  = "<tr>";
			table_html += "<td><input type='text' class='form-control' name='dropdown_options[0]' placeholder='Option'/></td>";
			table_html += "<td><button type='button' class='btn btn-primary btn-sm' onclick='delete_option(0)'>Delete</button></td>";
			table_html += "</tr>";
			document.getElementById('modal_count').value = 0;
		}
		document.getElementById('modal_table').innerHTML = table_html;
		// Update Modal with the row's ID
		document.getElementById('modal_id').value = id;
		// Show the dropdown modal
		$('#dropdown_modal').modal('show');
	}

	function add_option() {
		var table, row, cell1, cell2, option_count;
		// Find the table
		table = document.getElementById('modal_table');
		// Add a row at the end of the table
		row = table.insertRow(-1);
		// Initialize each cell of the row
		cell1 = row.insertCell(0);
		cell2 = row.insertCell(1);
		// Increment the current option count
		document.getElementById('modal_count').value++;
		// Find the current option count
		option_count = document.getElementById('modal_count').value;
		cell1.innerHTML = "<input type='text' class='form-control' name='dropdown_options[" + option_count + "]' placeholder='Option'/>";
		cell2.innerHTML = "<button type='button' class='btn btn-primary btn-sm' onclick='delete_option(" + option_count + ")'>Delete</button>";
	}

	function delete_option(count) {
		var table, trs, x, tds;
		var id = parseInt(count);
		table = document.getElementById('modal_table');
		// Delete the row and decrement the option count
		table.deleteRow(id);
		document.getElementById('modal_count').value--;
		// Reorder all rows below the deleted row
		trs = table.getElementsByTagName('tr');
		for (x = id; x < trs.length; x++) {
			tds = trs[x].getElementsByTagName('td');
			tds[0].firstElementChild.name = 'dropdown_options[' + String(x) + ']';
			tds[1].firstElementChild.setAttribute('onclick', 'delete_option(' + String(x) + ')');
		}
	}

	function finish_dropdown() {
		var id = document.getElementById('modal_id').value;
		var drop_fields;
		if (!(drop_fields = getDropValues(document.querySelectorAll('[name^="dropdown_options"]'))))
			return false;
		if (drop_fields.length < 1) { // Does not prevent making a dropdown with one empty option
			alert('At least one dropdown option is required to save a dropdown.');
			return false;
		}

		dropdowns[id] = drop_fields;
		// Hide the dropdown modal
		$('#dropdown_modal').modal('hide');
	}

	// Return array of index correct values for the dropdown
	function getDropValues(array) {
		var result = [];
		var option_count = document.getElementById('modal_count').value;

		for (var i = 0; i <= option_count; i++) {
			if (document.getElementsByName('dropdown_options[' + i + ']')[0].value.length <= <?= DROPDOWN_OPTION_MAX_LENGTH ?>) {
				result[i] = document.getElementsByName('dropdown_options[' + i + ']')[0].value;
			} else {
				alert('Option ' + i + ' is longer than the max option length of <?= DROPDOWN_OPTION_MAX_LENGTH ?>.');
				return false;
			}
		}

		return result;
	}

	/************************************************************* Submission */
	async function try_submit() {
		if (!check_defaults()) {
			alert('One or more of your field names is a default name - id, creator, insert_date, form_name, department_id, signature');
			return false;
		}

		if (!check_duplicates()) {
			alert('You have two or more fields with the same name');
			return false;
		}

		var tmp;
		for (var i = 0; i < count; i++) {
			if (tmp = document.getElementsByName('types[' + i + ']')[0]) {
				if (tmp[tmp.selectedIndex].value == 'DROPDOWN' && !(typeof dropdowns[i] !== 'undefined' && dropdowns[i] != null && dropdowns[i].length > 0)) {
					alert(document.getElementsByName('names[' + i + ']')[0].value + "'s dropdown has no values");
					return false;
				}
			}
		}

		var data = {};
		data['form_name'] = document.getElementById('form_name').value;
		data['subtitle'] = document.getElementById('subtitle').value;
		data['departments'] = getSelectValues(document.getElementsByName('departments[]')[0]);
		names = getValues(document.querySelectorAll('[name^="names"]'), 'names');
		types = getValues(document.querySelectorAll('[name^="types"]'), 'types');
		lengths = getValues(document.querySelectorAll('[name^="lengths"]'), 'lengths');
		precisions = getValues(document.querySelectorAll('[name^="precisions"]'), 'precisions');
		required = getValues(document.querySelectorAll('[name^="required"]'), 'required')

		var fields = [];
		for (var i = 2; i < names.length; i++) {
			fields[i - 2] = {};
			fields[i - 2]['name'] = names[i];
			fields[i - 2]['type'] = types[i];
			fields[i - 2]['length'] = lengths[i];
			fields[i - 2]['precision'] = precisions[i];
			fields[i - 2]['required'] = required[i];
		}
		data['fields'] = fields;
		data['dropdowns'] = dropdowns;

		const response = await fetch('<?= base_url("index.php/api/form_create") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers:{
				'Content-Type': 'application/json'
			}
		});

		const myJson = await response.json(); // extract JSON from the http response

		alert(myJson);

		if (myJson == 'Form created.') {
			if (<?= $this->session->userdata('current_department') ? $this->session->userdata('current_department') : 'null' ?>)
				window.location.href = '<?= base_url("index.php/main/department/{$this->session->userdata('current_department')}") ?>';
			else
				window.location.href = '<?= base_url("index.php/") ?>';
		}
	}

	function check_defaults() {
		var table, trs, i, row, td, input;
		// Grab all the rows
		table = document.getElementById('fields_table');
		trs = table.getElementsByTagName('tr');
		// Loop through rows and check if any field names match a default field name
		for (i = 1; i < trs.length; i++) {
			row = trs[i];
			td = row.getElementsByTagName('td')[0];
			input = td.firstElementChild;
			// If the field name matches a default field name...
			if (default_fields.indexOf(input.value) != -1)
				return false;
		}

		return true;
	}

	function check_duplicates() {
		var table, trs, i, row, td, input, fields;
		// Grab all the rows
		table = document.getElementById('fields_table');
		trs = table.getElementsByTagName('tr');
		// Loop through rows and check if any field names match a default field name
		var fields = [];
		for (i = 2; i < trs.length; i++) {
			row = trs[i];
			td = row.getElementsByTagName('td')[0];
			input = td.firstElementChild;
			// If the field name matches an existing field name
			if (fields.indexOf(input.value) != -1)
				return false;
			fields.push(input.value);
		}

		return true;
	}

	// Return an array of the selected option values
	// select is an HTML select element
	function getSelectValues(select) {
		var result = [];
		var options = select && select.options;
		var opt;

		for (var i = 0, iLen = options.length; i < iLen; i++) {
			opt = options[i];
			if (opt.selected) {
				result.push(opt.value || opt.text);
			}
		}

		return result;
	}

	// Return array of index correct values for the given type
	function getValues(array, type) {
		var result = [];
		var tmp;

		for (var i = 1; i < count; i++) {
			if (type == 'types' || type == 'precisions') {
				// Types is a dropdown, so pull the value at the selected index
				if (document.getElementsByName(type + '[' + i + ']')[0]) {
					tmp = document.getElementsByName(type + '[' + i + ']')[0];
					result[i] = tmp[tmp.selectedIndex].value;
				} else {
					result[i] = null;
				}
			} else if (type == 'required') {
				// Required is a checkbox, check if it is checked
				if (document.getElementsByName(type + '[' + i + ']')[0]) {
					result[i] = document.getElementsByName(type + '[' + i + ']')[0].checked;
				} else {
					result[i] = null;
				}
			} else {
				// Everything else is an input field, just pull the value
				if (document.getElementsByName(type + '[' + i + ']')[0]) {
					result[i] = document.getElementsByName(type + '[' + i + ']')[0].value;
				} else {
					result[i] = null;
				}
			}
		}

		return result;
	}
</script>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<table>
					<tr>
						<td>Departments with Access</td>
						<td><?= form_multiselect('departments[]', $departments,
							$duplicate_departments ? $duplicate_departments : $this->session->userdata('current_department')) ?></td>
					</tr>
				</table>

				<table class="table table-bordered" id="fields_table">
					<tr>
						<td style="width:16.6%"><label class="control-label">Form Name</label></td>
						<td colspan=4><input type="text" class="form-control" id="form_name"
							placeholder="Form Name"<?= $form_name ? 'value="' . htmlspecialchars($form_name) . '"' : "" ?>/></td>
					</tr>

					<tr>
						<td style="width:16.6%"><label class="control-label">Form Subtitle</label></td>
						<td colspan=4><input type="text" class="form-control" id="subtitle"
							placeholder="Subtitle (Optional)"<?= $subtitle ? 'value="' . htmlspecialchars($subtitle) . '"' : "" ?>/></td>
					</tr>

					<?php if ($fields) : ?>
						<?php $count = 2; ?>
						<?php foreach ($fields as $field) : ?>
							<tr>
								<td style="width:16.6%">
									<input type="text" class="form-control" name="names[<?= $count ?>]"
										placeholder="Field Name" value="<?= htmlspecialchars($field->name) ?>"/>
								</td>

								<td>
									<?= form_dropdown("types[{$count}]", $field_types, strtoupper($field->type), "onchange='change_row({$count})'") ?>
								</td>

								<?php if ($field->type == 'dropdown') : ?>
									<td>
										<button type="button" class="btn btn-primary btn-sm" onclick="open_modal(<?= $count ?>)">Edit Dropdown</button>
									</td>
								<?php elseif ($field->type == 'varchar') : ?>
									<td>
										<input type="text" class="form-control" name="lengths[<?= $count ?>]"
											placeholder="Max Field Length (optional)" value="<?= $field->max_length ?>"/>
									</td>
								<?php elseif ($field->type == 'decimal') : ?>
									<td>
										<?= form_dropdown("precisions[{$count}]", $precisions, $field->precision) ?>
									</td>
								<?php else : ?>
									<td></td>
								<?php endif ?>

								<td>Required? <input type="checkbox" name="required[<?= $count ?>]"<?= $field->required ? " CHECKED" : "" ?>/></td>

								<td>
									<button type="button" class="btn btn-primary btn-sm" onclick="delete_row(<?= $count ?>)">Delete</button>
								</td>
							</tr>
							<?php $count++ ?>
						<?php endforeach ?>
					<?php endif ?>
				</table>

				<div align="CENTER">
					<button type="button" class="btn btn-primary" onclick="add_field()">Add a field</button>
				</div>

				<button type="button" class="btn btn-primary" onclick="try_submit()">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="dropdown_modal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Dropdown Options</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>

			<input type="hidden" id="modal_count"/>
			<input type="hidden" id="modal_id"/>

			<div class="modal-body">
				<table class="table table-bordered" id="modal_table"></table>

				<div align="CENTER">
					<button type="button" class="btn btn-primary" onclick="add_option()">Add Option</button>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="finish_dropdown()">Finish</button>
			</div>
		</div>
	</div>
</div>
