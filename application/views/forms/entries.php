
<script>
	var order = '<?= $order ?>';
	var direction = '<?= $direction ?>';

	var default_fields = <?= json_encode($default_fields) ?>;
	var logic_columns = <?= json_encode($logic_columns) ?>;

	$(document).ready(function() {
		<?php foreach ($fields as $field) : ?>
			<?php if ($field->type == 'datetime') : ?>
				$('[name^="<?= $field->name ?>"]').datepicker({ format: 'mm/dd/yyyy' });
			<?php endif ?>
		<?php endforeach ?>
	});

	function change_order(field) {
		if (field == order) {
			// Toggle ASC/DESC
			if (direction == 'DESC')
				direction = 'ASC';
			else
				direction = 'DESC';
		} else {
			order = field;
			direction = 'DESC';
		}
		// Use the API to finish changing the order
		sort_filter();
	}

	async function sort_filter() {
		var select;
		var data = {};
		data['form_id'] = <?= $form->id ?>;
		data['order'] = order;
		data['direction'] = direction;
		data['operator'] = {};
		data['start'] = {};
		data['end'] = {};
		// Generated code below
		<?php
		foreach ($fields as $field) {
			// Don't try to send signatures
			if ($field->name != 'signature') {
				if ($field->name == 'creator') {
					echo "select = document.getElementsByName('{$field->name}')[0];";
					echo "data['{$field->name}'] = select[select.selectedIndex].value;";
				} elseif ($field->type == 'dropdown') {
					echo "data['{$field->name}'] = $('#{$field->name}').val();";
				} elseif ($field->type == 'text' || $field->type == 'varchar') {
					echo "data['{$field->name}'] = document.getElementsByName('{$field->name}')[0].value;";
				} elseif ($field->type == 'int' || $field->type == 'decimal') {
					echo "select = document.getElementsByName('{$field->name}_operator')[0];";
					echo "data['operator']['{$field->name}'] = select[select.selectedIndex].value;";
					echo "data['{$field->name}'] = document.getElementsByName('{$field->name}')[0].value;";
				} elseif ($field->type == 'datetime') {
					echo "if (document.getElementsByName('{$field->name}_start')[0].value)";
					echo "data['start']['{$field->name}'] = document.getElementsByName('{$field->name}_start')[0].value;";
					echo "if (document.getElementsByName('{$field->name}_end')[0].value)";
					echo "data['end']['{$field->name}'] = document.getElementsByName('{$field->name}_end')[0].value;";
				}
			}
		}
		?>

		const response = await fetch('<?= base_url("index.php/api/form_entries") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers: { 'Content-Type': 'application/json' }
		});

		const myJson = await response.json();

		if (myJson[0] != '[' && myJson[0] != '{') { // Log strings
			console.log(myJson);
		} else {
			// Parse the JSON for easier handling
			var json = JSON.parse(myJson);
			// Update the count of entries
			if (json.length == 1)
				document.getElementById('entry_count').innerHTML = json.length + " entry found.";
			else
				document.getElementById('entry_count').innerHTML = json.length + " entries found.";
			// Rebuild table's original columns with sorted and filtered data
			var newHTML = '';
			for (var i = 0; i < json['entries'].length; i++) {
				newHTML += "<tr>";
				// Add all fields before Initials button
				for (field of json['fields']) {
					if (json['entries'][i][field] == null) {
						<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
							if (default_fields.indexOf(field) || logic_columns.indexOf(field)) {
								newHTML += "<td></td>";
							} else {
								newHTML += "<td>";
								newHTML += "<span class='edit'></span>";
								newHTML += "<a href='javascript:open_edit(" + json['entries'][i]['id'] + "," + json['entries'][i][field] + ", \"\")'";
									newHTML += " class='edit pointer display-none'></a>";
								newHTML += "</td>";
							}
						<?php else : ?>
							newHTML += "<td></td>";
						<?php endif ?>
					} else {
						<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
							if (default_fields.indexOf(field) || logic_columns.indexOf(field)) {
								newHTML += "<td>" + json['entries'][i][field] + "</td>";
							} else {
								newHTML += "<td>";
								newHTML += "<span class='edit'>" + json['entries'][i][field] + "</span>";
								newHTML += "<a href='javascript:open_edit(" + json['entries'][i]['id'] + ",\"" + field + "\", \"" + json['entries'][i][field] + "\")'";
									newHTML += " class='edit pointer display-none'>" + json['entries'][i][field] + "</a>";
								newHTML += "</td>";
							}
						<?php else : ?>
							newHTML += "<td>" + json['entries'][i][field] + "</td>";
						<?php endif ?>
					}
				}
				// Add View button after the rest
				newHTML += "<td><button type='button' class='btn btn-primary btn-sm' onclick='initials(" + json['entries'][i]['id'] + ")'>View</button></td>";
				newHTML += "</tr>";
			}
			document.getElementById('tBody').innerHTML = newHTML;
		}
	}

	function hide(div, toggle) {
		var img, html;
		img = document.getElementById(div + '_img');

		if (toggle == 'hide') {
			// Replace the Hide image with the Show image
			html = '<img src="<?= base_url("img/Show.png") ?>" width="25" height="25" onClick="javascript:hide(\'' + div + '\', \'show\');">';
			img.innerHTML = html;
			document.getElementById(div).style.display = "none";
		} else { // Toggle == 'show'
			// Replace the Show image with the Hide image
			html = '<img src="<?= base_url("img/Hide.png") ?>" width="25" height="25" onClick="javascript:hide(\'' + div + '\', \'hide\');">';
			img.innerHTML = html;
			document.getElementById(div).style.display = "";
		}
	}

	function initials(id) {
		window.open('<?= base_url("index.php/main/view_initials/{$form->id}/") ?>' + id, '', "width=500 height=250");
	}

	<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
		$(document).ready(function() {
			var wrapper = document.getElementById("signature-pad");
			var canvas = wrapper.querySelector("canvas");

			canvas.height = 200;
			canvas.width = 464;
		});

		function sign_off(edit = false) {
			if (edit) {
				document.getElementById('sig_submit').style.display = 'none';
				document.getElementById('edit_sig_submit').style.display = '';
			} else {
				document.getElementById('sig_submit').style.display = '';
				document.getElementById('edit_sig_submit').style.display = 'none';
			}
			$('#sigModal').modal('show');
		}

		function try_submit(dataURL) {
			document.getElementById('quality_check').value = 1;
			document.getElementById('signature').innerHTML = dataURL;
			document.edit_form.submit();
		}

		async function move_column(id, direction, node) {
			var data = {};
			data['id'] = id;
			data['direction'] = direction;

			const response = await fetch('<?= base_url("index.php/api/reorder_entries") ?>', {
				method: 'POST',
				body: JSON.stringify(data),
				headers: { 'Content-Type': 'application/json' }
			});

			const myJson = await response.json();

			if (myJson == 'Column moved.') {
				// Grab the TH of the column being moved
				var main_th = node.parentNode.parentNode;
				// Grab the full table header and find which index the TH is at
				var thead = document.getElementById('tHead');
				var x = 0;
				while (thead.cells[x] != main_th)
					x++;
				// Find the index of the TH being swapped with our main_th
				var y;
				if (direction == 'left')
					y = x - 1;
				else
					y = x + 1;
				// Swap the headers
				if (direction == 'left')
					main_th.parentNode.insertBefore(main_th, main_th.previousElementSibling);
				else
					main_th.parentNode.insertBefore(main_th.nextElementSibling, main_th);
				// Swap each row's cells
				var trs = document.getElementById('tBody').getElementsByTagName('tr');
				var tmp;
				for (var i = 0; i < trs.length; i++) {
					tmp = trs[i].getElementsByTagName('td')[x].innerHTML;
					trs[i].getElementsByTagName('td')[x].innerHTML = trs[i].getElementsByTagName('td')[y].innerHTML;
					trs[i].getElementsByTagName('td')[y].innerHTML = tmp;
				}
			} else {
				alert(myJson);
			}
		}

		function toggle_editing() {
			$(".edit").toggleClass("display-none");
		}

		var cur_id;
		var cur_property;
		<?php if (isset($dropdowns)) : ?>
			var dropdowns = <?= json_encode($dropdowns) ?>;
		<?php endif ?>

		function open_edit(id, property, old_value) {
			cur_id = id;
			cur_property = property;
			var editHTML = '';
			if (typeof dropdowns == "undefined" || typeof dropdowns[property] == "undefined" || dropdowns[property] == null) {
				editHTML += "<input type='text' class='form-control' id='edit_value' value='" + old_value + "'/>";
			} else {
				editHTML += "<select class='form-control' id='edit_value'>";
				for (const [key, option] of Object.entries(dropdowns[property])) {
					if (option == old_value)
						editHTML += "<option value='" + key + "' SELECTED>" + option + "</option>";
					else
						editHTML += "<option value='" + key + "'>" + option + "</option>";
				}
				editHTML += "</select>";
			}
			document.getElementById('edit_body').innerHTML = editHTML;
			$('#edit_modal').modal('show');
		}

		async function edit_entry(dataURL) {
			var data = {};
			data['form_id'] = <?= $form->id ?>;
			data['entry_id'] = cur_id;
			data['property'] = cur_property;
			if (typeof dropdowns == "undefined" || typeof dropdowns[cur_property] == "undefined" || dropdowns[cur_property] == null) {
				data['value'] = document.getElementById('edit_value').value;
			} else {
				var e = document.getElementById('edit_value');
				data['value'] = e.options[e.selectedIndex].text;
			}
			data['signature'] = dataURL;

			const response = await fetch('<?= base_url("index.php/api/edit_entry") ?>', {
				method: 'POST',
				body: JSON.stringify(data),
				headers: { 'Content-Type': 'application/json' }
			});

			const myJson = await response.json();

			if (myJson == 'Entry updated.') {
				window.location.reload();
			} else {
				alert(myJson);
			}
		}
	<?php endif ?>
</script>

<style>
	td { height: 50px; }
	td a {
		display: block;
		width: 100%;
		height: 100%;
	}
</style>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("main/form_entries/{$form->id}", array( 'name' => 'edit_form' )) ?>

		<input type="hidden" name="quality_check" id="quality_check" value="0"/>

		<div class="card">
			<div class="card-body">
				<h3>Filters
					<a href="#" id="Filter_img"><img src="<?= base_url("img/Show.png") ?>" width="25" height="25" onClick="javascript:hide('Filter', 'show');"></a>
					<button type="button" class="btn btn-primary" onclick="sort_filter()">Filter</button>
					<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
						<button type="button" class="btn btn-primary" onclick="sign_off()">Quality Check</button>
						<button type="button" class="btn btn-primary" onclick="toggle_editing()">Edit</button>
						<textarea name="signature" id="signature" style="display:none;"></textarea>
					<?php endif ?>
				</h3>
				<div id="Filter" class="table-wrapper" style="display:none;">
					<table class="table table-bordered">
						<tr>
							<?php
							// Don't show signature as a filter field
							foreach ($fields as $field)
								if ($field->name != 'signature')
									echo "<th>" . ucfirst(str_replace('_', ' ', $field->name)) . "</th>";
							?>
						</tr>

						<tr>
							<?php
							// Don't show signature as a filter field
							foreach ($fields as $field) {
								if ($field->name != 'signature') {
									if ($field->name == 'creator') // Special case to avoid 'int' below
										echo "<td>" . form_dropdown($field->name, $users) . "</td>";
									else {
										switch ($field->type) {
											case 'int':
												echo "<td>";
												echo form_dropdown("{$field->name}_operator", $operators);
												echo "<input type='text' class='form-control' name='{$field->name}'/>";
												echo "</td>";
												break;
											case 'decimal': // Includes Currency
												echo "<td>";
												echo form_dropdown("{$field->name}_operator", $operators);
												echo "<input type='text' class='form-control' name='{$field->name}'/>";
												echo "</td>";
												break;
											case 'varchar':
												echo "<td><input type='text' class='form-control' name='{$field->name}'/></td>";
												break;
											case 'dropdown':
												echo "<td>" . form_multiselect($field->name . '[]', $dropdowns[$field->name], '', "id='{$field->name}'") . "</td>";
												break;
											case 'datetime':
												echo "<td>";
												echo "<input type='text' class='form-control' name='{$field->name}_start' placeholder='Start' autocomplete='off'/>";
												echo "<input type='text' class='form-control' name='{$field->name}_end' placeholder='End' autocomplete='off'/>";
												echo "</td>";
												break;
											case 'text':
												echo "<td><input type='text' class='form-control' name='{$field->name}'/></td>";
												break;
										}
									}
								}
							}
							?>
						</tr>
					</table>
				</div>

				<h4 align="CENTER" id="entry_count"><?= (count($entries) == 1) ? "1 entry found." : count($entries) . " entries found." ?></h4>

				<div class="table-wrapper">
					<table class="table table-bordered">
						<thead>
							<tr id="tHead">
								<?php foreach ($fields as $field) : ?>
									<?php if ($field->name != 'signature') : ?>
										<th style="text-align:center;">
											<b>
												<a href="javascript:change_order('<?= $field->name ?>')"><?= ucfirst(str_replace('_', ' ', $field->name)) ?></a>
											</b>

											<br>

											<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
												<span style="float:left;" class="edit pointer display-none">
													<i class="fa fa-angle-left" onclick="move_column(<?= $field->id ?>, 'left', this)"></i>
												</span>

												<span style="float:right;" class="edit pointer display-none">
													<i class="fa fa-angle-right" onclick="move_column(<?= $field->id ?>, 'right', this)"></i>
												</span>
											<?php endif ?>
										</th>
									<?php endif ?>
								<?php endforeach ?>
								<th><b>Signature</b></th>
							</tr>
						</thead>

						<tbody id="tBody">
							<?php foreach ($entries as $row) : ?>
								<tr>
									<?php foreach ($fields as $field) : ?>
										<?php if ($field->name != 'signature') : ?>
											<td>
												<?php if (is_company_manager($this->session->userdata('user_level')) && !in_array($field->name, $default_fields)
													&& !in_array($field->name, $logic_columns)) : ?>
													<span class="edit">
														<?= $row->{$field->name} ?>
													</span>

													<a href="javascript:open_edit(<?= $row->id ?>, '<?= $field->map ?>', '<?= $row->{$field->name} ?>')" class="edit pointer display-none">
														<?= $row->{$field->name} ?>
													</a>
												<?php else : ?>
													<?= $row->{$field->name} ?>
												<?php endif ?>
											</td>
										<?php endif ?>
									<?php endforeach ?>
									<td><button type="button" class="btn btn-primary btn-sm" onclick="initials(<?= $row->id ?>)">View</button></td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>

<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
	<div class="modal fade" id="edit_modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Edit Entry</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<div class="modal-body" id="edit_body">

				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="javascript:sign_off(true)">Sign Off</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="sigModal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content" id="signature-pad">
				<div class="modal-header">
					<h4 class="modal-title">Initials</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<div class="modal-body">
					<div class="signature-pad--body">
						<canvas></canvas>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-action="clear">Clear</button>
					<button type="button" class="btn btn-secondary" data-action="undo">Undo</button>
					<button type="button" class="btn btn-primary" id="sig_submit" data-action="save">Submit</button>
					<button type="button" class="btn btn-primary" id="edit_sig_submit" data-action="editSave">Submit</button>
				</div>
			</div>
		</div>
	</div>

	<link rel="stylesheet" type="text/css" href="<?= base_url("css/signature-pad.css") ?>">

	<script type="text/javascript" src="<?= base_url("js/signature_pad.umd.js") ?>"></script>
	<script type="text/javascript" src="<?= base_url("js/signature_pad.app2.js") ?>"></script>
<?php endif ?>
