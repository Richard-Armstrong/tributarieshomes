
<?php if (!$report->static) : ?>
<script>
	function run_report(entry_id) {
		window.location = "<?= base_url("index.php/reports/rerun/{$report->id}/") ?>" + entry_id;
	}

	$(document).ready(function() {
		var wrapper = document.getElementById("signature-pad");
		var canvas = wrapper.querySelector("canvas");

		canvas.height = 200;
		canvas.width = 464;
	});

	function sign_off() {
		$('#sigModal').modal('show');
	}

	function toggle_editing() {
		$(".edit").toggleClass("display-none");
	}

	var cur_id;
	var cur_property;

	function open_edit(id, property, old_value) {
		cur_id = id;
		cur_property = property;
		document.getElementById('edit_value').value = old_value;
		$('#edit_modal').modal('show');
	}

	async function try_submit(dataURL) {
		var data = {};
		data['report_id'] = <?= $report->id ?>;
		data['entry_id'] = cur_id;
		data['property_id'] = cur_property;
		data['value'] = document.getElementById('edit_value').value;
		data['signature'] = dataURL;

		const response = await fetch('<?= base_url("index.php/reports_api/edit_entry") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers: { 'Content-Type': 'application/json' }
		});

		const myJson = await response.json();

		alert(myJson);

		if (myJson == 'Entry updated.')
			window.location.reload();
	}
</script>
<?php endif ?>

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

		<div class="card">
			<div class="card-body">
				<div class="table-wrapper">
					<?php if (!$report->static) : ?>
						<button type="button" class="btn btn-primary" onclick="toggle_editing()">Edit</button>
					<?php endif ?>

					<table class="table table-bordered">
						<thead>
							<tr>
								<?php foreach ($fields as $field) : ?>
									<th><?= $field->name ?></th>
								<?php endforeach ?>

								<?php if (!$report->static) : ?>
									<th></th>
								<?php endif ?>
							</tr>
						</thead>

						<tbody>
							<?php foreach ($entries as $entry) : ?>
								<tr>
									<?php foreach ($fields as $field) : ?>
										<td>
											<?php if (!in_array($field->name, $default_fields) && !in_array($field->name, $logic_columns)) : ?>
												<span class="edit">
													<?= $entry->{$field->map} ?>
												</span>

												<a href="javascript:open_edit(<?= $entry->id ?>, '<?= $field->id ?>', '<?= $entry->{$field->map} ?>')"
													class="edit pointer display-none">
													<?= $entry->{$field->map} ?>
												</a>
											<?php else : ?>
												<?= $entry->{$field->map} ?>
											<?php endif ?>
										</td>
									<?php endforeach ?>

									<?php if (!$report->static) : ?>
										<td><button type="button" onclick="run_report(<?= $entry->id ?>)">Re-run</button></td>
									<?php endif ?>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if (!$report->static) : ?>
	<div class="modal fade" id="edit_modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Edit Entry</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<div class="modal-body" id="edit_body">
					<input type="text" class="form-control" id="edit_value" placeholder="New Value"/>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="javascript:sign_off()">Sign Off</button>
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
				</div>
			</div>
		</div>
	</div>

	<link rel="stylesheet" type="text/css" href="<?= base_url("css/signature-pad.css") ?>">

	<script type="text/javascript" src="<?= base_url("js/signature_pad.umd.js") ?>"></script>
	<script type="text/javascript" src="<?= base_url("js/signature_pad.app.js") ?>"></script>
<?php endif ?>
