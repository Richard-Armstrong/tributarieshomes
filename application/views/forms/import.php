
<script>
	<?php if (!$ready_for_save) : ?>
		function toggle_header() {
			var item = document.getElementById('header_layout');
			var button = document.getElementById('ShowHeader');

			if (item.style.display == 'none') {
				item.style.display = 'block';
				button.value = 'Hide Header';
			} else {
				item.style.display = 'none';
				button.value = 'Show Header';
			}
		}
	<?php elseif ($ready_for_save && $results <> NULL) : ?>
		$(document).ready(function() {
			var wrapper = document.getElementById("signature-pad");
			var canvas = wrapper.querySelector("canvas");

			canvas.height = 200;
			canvas.width = 464;
		});

		function sign_off() {
			$('#sigModal').modal('show');
		}

		function try_submit(dataURL) {
			document.getElementById('signature').innerHTML = dataURL;
			document.edit_form.submit();
		}
	<?php endif ?>
</script>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("main/handle_form_import/{$form->id}", array( 'name' => 'edit_form' )) ?>

		<div class="card">
			<div class="card-body">
				<?php if (!$ready_for_save) : // Table for inputting data to be imported ?>
					<input type="hidden" name="commit_changes" value="0">

					<table>
						<tr><td colspan=2><h2>Enter Form Data to Import Below</h2></td></tr>
						<tr><td colspan=2><textarea name="import_data" rows=15 cols=100 width=200><?= $import_data ?></textarea></td></tr>
						<tr>
							<td align=CENTER>
								<input type=submit class="btn btn-primary" value="Import"/>
							</td>

							<td align=CENTER>
								<input type=button class="btn btn-primary" value="Show Header" id="ShowHeader" onclick="toggle_header()">
							</td>
						</tr>
					</table>

					<div id="header_layout" style="display:none;">
						<h4>Note: Columns can be in any order.</h4>
						<table class="table table-striped table-bordered">
							<tr>
							<?php
							// Skip default fields and logic columns
							foreach ($fields as $field)
								if (!in_array($field->name, $default_fields) && !in_array($field->name, $logic_columns))
									echo "<th>{$field->name}</th>";
							?>
							</tr>
						</table>
					</div>
				<?php else : // Replace table with Save button and hidden textarea ?>
					<div style="display:none;"><textarea name="import_data" rows=10 cols=100 width=200><?= $import_data ?></textarea></div>
					<input type="hidden" name="commit_changes" value="1"/>
					<textarea name="signature" id="signature" style="display:none;"></textarea>
					<?php if (!$errors && $results <> NULL) : ?>
						<input type="button" class="btn btn-primary" value="Sign Off" onclick="sign_off()"/>
					<?php endif ?>
				<?php endif // else ?>

				<?php if ($ready_for_save && $results <> NULL) : ?>
					<?php if ($errors) : ?>
						<?php foreach ($errors as $error) : ?>
							<p><?= $error ?></p>
						<?php endforeach ?>
					<?php endif // $errors ?>

					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<?php foreach ($fields as $field) : ?>
									<?php if (!in_array($field->name, $default_fields) && !in_array($field->name, $logic_columns)) : ?>
										<th><?= $field->name ?></th>
									<?php endif ?>
								<?php endforeach ?>
							</tr>
						</thead>

						<tbody>
						<?php
						foreach ($results as $row) {
							echo "<tr>";
							foreach ($row as $key => $value)
								if (!in_array($key, array( 'id', 'creator', 'signature' )) && !in_array($key, $logic_columns))
									echo "<td>{$value}</td>";
							echo "</tr>";
						}
						?>
						</tbody>
					</table>
				<?php elseif ($ready_for_save) : ?>
					<div><h3>No records were found.</h3></div>
				<?php endif // elseif ?>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>

<?php if ($ready_for_save && $results <> NULL) : ?>
	<div class="modal fade" id="sigModal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content" id="signature-pad">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Initials</h4>
				</div>

				<div class="modal-body">
					<div class="signature-pad--body">
						<canvas></canvas>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-action="clear">Clear</button>
					<button type="button" class="btn btn-secondary" data-action="undo">Undo</button>
					<button type="button" class="btn btn-primary" data-action="save">Submit</button>
				</div>
			</div>
		</div>
	</div>

	<link rel="stylesheet" type="text/css" href="<?= base_url("css/signature-pad.css") ?>">

	<script type="text/javascript" src="<?= base_url("js/signature_pad.umd.js") ?>"></script>
	<script type="text/javascript" src="<?= base_url("js/signature_pad.app.js") ?>"></script>
<?php endif ?>
