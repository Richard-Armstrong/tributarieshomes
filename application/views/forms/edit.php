
<script>
	<?php if (count($dropdowns)) : ?>
		var dropdowns = <?= json_encode($dropdowns) ?>;
	<?php else : ?>
		var dropdowns = [];
	<?php endif ?>

	function open_modal(id) {
		var table_html;
		// Use the existing dropdown data to populate the modal
		table_html = "";
		for (var i = 0; i < dropdowns[id].length; i++) {
			table_html += "<tr>";
			table_html += "<td><input type='text' class='form-control' name='dropdown_options[" + i + "]' placeholder='Option' value='" + dropdowns[id][i] + "'/></td>";
			table_html += "<td><button type='button' class='btn btn-primary btn-sm' onclick='delete_option(" + i + ")'>Delete</button></td>";
			table_html += "</tr>";
		}
		document.getElementById('modal_count').value = dropdowns[id].length - 1;
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
			tds[0].firstChild.name = 'dropdown_options[' + String(x) + ']';
			tds[1].firstChild.setAttribute('onclick', 'delete_option(' + String(x) + ')');
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

	async function try_submit() {
		var data = {};
		data['form_id'] = <?= $form->id ?>;
		data['form_name'] = document.getElementById('form_name').value;
		data['subtitle'] = document.getElementById('subtitle').value;
		data['departments'] = getSelectValues(document.getElementsByName('departments[]')[0]);
		data['required'] = getCheckValues(document.querySelectorAll('[name^="required"]'));
		data['dropdowns'] = dropdowns;

		const response = await fetch('<?= base_url("index.php/api/form_edit") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers:{
				'Content-Type': 'application/json'
			}
		});

		const myJson = await response.json(); // extract JSON from the http response

		alert(myJson);

		if (myJson == 'Form updated.') {
			if (<?= $this->session->userdata('current_department') ? $this->session->userdata('current_department') : 'null' ?>)
				window.location.href = '<?= base_url("index.php/main/department/{$this->session->userdata('current_department')}") ?>';
			else
				window.location.href = '<?= base_url("index.php/") ?>';
		}
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

	// Return array of checkbox values
	function getCheckValues(array) {
		var result = {};
		for (var i = 0; i < array.length; i++)
			result[array[i].value] = array[i].checked;
		return result;
	}
</script>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("main/edit_form/{$form->id}") ?>

		<div class="card">
			<div class="card-body">
				<table>
					<tr>
						<td>Departments with Access</td>
						<td><?= form_multiselect('departments[]', $departments, explode(',', $form->department)) ?></td>
					</tr>
				</table>

				<table class="table table-bordered" id="fields_table">
					<tr>
						<td style="width:16.6%;">Form Name</td>
						<td colspan=3><input type="text" class="form-control" id="form_name" placeholder="Form Name"
							value="<?= htmlspecialchars($form->name) ?>"/></td>
					</tr>

					<tr>
						<td style="width:16.6%;">Form Subtitle</td>
						<td colspan=3><input type="text" class="form-control" id="subtitle" placeholder="Subtitle (Optional)"
							value="<?= htmlspecialchars($form->subtitle) ?>"/></td>
					</tr>

					<?php foreach ($fields as $field) : ?>
						<tr>
							<td style="width:16.6%">
								<input type="text" class="form-control" value="<?= htmlspecialchars($field->name) ?>" DISABLED/>
							</td>

							<td><?= $field->type ?></td>

							<?php if ($field->type == 'dropdown') : ?>
								<td>
									<button type="button" class="btn btn-primary btn-sm" onclick="open_modal(<?= $field->id ?>)">Edit Dropdown</button>
								</td>
							<?php elseif ($field->type == 'varchar') : ?>
								<td>
									<input type="text" class="form-control" value="<?= $field->max_length ?>" DISABLED/>
								</td>
							<?php elseif ($field->type == 'decimal') : ?>
								<td>
									<?= form_dropdown("", $precisions, $field->precision, 'DISABLED') ?>
								</td>
							<?php else : ?>
								<td></td>
							<?php endif ?>

							<td>Required? <input type="checkbox" name="required[<?= $field->id ?>]" value="<?= $field->id ?>"<?= $field->required ? " CHECKED" : "" ?>/></td>
						</tr>
					<?php endforeach ?>
				</table>

				<button type="button" class="btn btn-primary" onclick="try_submit()">Save Changes</button>
			</div>
		</div>

		<?= form_close() ?>
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
