
<script>
	function add_column(constant = false) {
		if (constant) {
			document.getElementById('constant').value = 1;
			document.getElementById('add_field2').style.display = 'none';
			document.getElementById('add_field2_constant').style.display = '';
		} else {
			document.getElementById('constant').value = 0;
			document.getElementById('add_field2').style.display = '';
			document.getElementById('add_field2_constant').style.display = 'none';
		}

		$('#add_modal').modal('show');
	}

	async function finish_add() {
		var data = {};
		data['report_id'] = <?= $report->id ?>;
		data['field_name'] = document.getElementById('add_field_name').value;
		data['constant'] = document.getElementById('constant').value;
		data['field1'] = document.getElementById('add_field1').options[document.getElementById('add_field1').selectedIndex].text;
		data['operation'] = document.getElementById('add_operation').options[document.getElementById('add_operation').selectedIndex].text;
		data['field2'] = document.getElementById('add_field2').options[document.getElementById('add_field2').selectedIndex].text;
		data['field2_constant'] = document.getElementById('add_field2_constant').value;

		const response = await fetch('<?= base_url("index.php/reports_api/add_logic") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers:{
				'Content-Type': 'application/json'
			}
		});

		const myJson = await response.json(); // extract JSON from the http response

		alert(myJson);

		if (myJson == "Column added.")
			window.location.reload(true);
	}

	async function delete_column(id) {
		if (confirm("This will delete this calculated column from the report! Do you wish to do this?")) {
			var data = {};
			data['report_id'] = <?= $report->id ?>;
			data['id'] = id;
			const response = await fetch('<?= base_url("index.php/reports_api/delete_logic") ?>', {
				method: 'POST',
				body: JSON.stringify(data),
				headers:{
					'Content-Type': 'application/json'
				}
			});

			const myJson = await response.json(); // extract JSON from the http response

			alert(myJson);

			if (myJson == "Column deleted.")
				window.location.reload(true);
		}
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
				<button type="button" class="btn btn-primary" onclick="add_column()">Add Logic Column</button>
				<button type="button" class="btn btn-primary" onclick="add_column(true)">Add Constant Logic Column</button>

				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Field Name</th>
							<th>Field 1</th>
							<th>Operation</th>
							<th>Field 2</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						<?php foreach ($records as $record) : ?>
							<tr>
								<td><?= $record->name ?></td>
								<td><?= $record->logic_field1 ?></td>
								<td><?= $record->logic_operation ?></td>
								<td><?= $record->logic_field2 ?></td>
								<td><button type="button" class="btn btn-primary btn-xs" onclick="delete_column(<?= $record->id ?>)">Delete</button></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="add_modal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Add Logic Column</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>

			<div class="modal-body">
				<table class="table table-bordered">
					<input type="hidden" name="constant" id="constant" value="0"/>

					<tr>
						<td>Field Name</td>
						<td><input type="text" class="form-control" id="add_field_name"/></td>
					</tr>

					<tr>
						<td>First Field</td>
						<td><?= form_dropdown('add_field1', $numeric_fields, '', 'class="form-control" id="add_field1"') ?></td>
					</tr>

					<tr>
						<td>Operation</td>
						<td><?= form_dropdown('add_operation', $operations, '', 'class="form-control" id="add_operation"') ?></td>
					</tr>

					<tr>
						<td>Second Field</td>
						<td>
							<?= form_dropdown('add_field2', $numeric_fields, '', 'class="form-control" id="add_field2"') ?>
							<input type="text" class="form-control" style="display:none;" name="add_field2_constant" id="add_field2_constant" placeholder="Constant"/>
						</td>
					</tr>
				</table>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="finish_add()">Add Column</button>
			</div>
		</div>
	</div>
</div>
