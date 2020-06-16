
<script>
	$(document).ready(function() {
		<?php
		foreach ($fields as $field) {
			if (!in_array($field->name, $default_fields) && $field->type == 'datetime') {
				// Fixes for field names in JQuery
				$name = str_replace("/", "\\\\/", $field->name);
				$name = str_replace("&", '\\\\&', $name);
				echo "$(\"#{$name}\").datepicker({ format: 'mm/dd/yyyy' });";
			}
		}
		?>

		var wrapper = document.getElementById("signature-pad");
		var canvas = wrapper.querySelector("canvas");

		canvas.height = 200;
		canvas.width = 464;
	});

	function sign_off() {
		$('#sigModal').modal('show');
	}

	async function try_submit(dataURL) {
		var data = {};
		data['form_id'] = <?= $form->id ?>;
		data['signature'] = dataURL;
		<?php
		foreach ($fields as $field) {
			if (!in_array($field->name, $default_fields) && !in_array($field->name, $logic_columns)) {
				if ($field->type == 'dropdown') {
					echo "var e = document.getElementById('{$field->name}');";
					echo "data['{$field->name}'] = e[e.selectedIndex].value;";
				} else {
					echo "data['{$field->name}'] = document.getElementById('{$field->name}').value;";
				}
			}
		}
		?>

		const response = await fetch('<?= base_url("index.php/api/form_submit") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers:{
				'Content-Type': 'application/json'
			}
		});

		const myJson = await response.json(); // extract JSON from the http response

		alert(myJson);

		if (myJson == 'Form submitted.') {
			$('#sigModal').modal('hide');
			window.location.reload(false);
		}
	}
</script>

<link rel="stylesheet" type="text/css" href="<?= base_url("css/signature-pad.css") ?>">

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($form->subtitle) : ?>
			<h3><?= $form->subtitle ?></h3>
		<?php endif ?>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<table class="table table-bordered">
					<?php
					foreach ($fields as $field) {
						// Skip default fields and logic columns
						if (!in_array($field->name, $default_fields) && !in_array($field->name, $logic_columns)) {
							echo "<tr>";
								echo "<td style='width:16.6%'><label class='control-label'>" . str_replace('_', ' ', $field->name) . "</label></td>";
								switch ($field->type) {
									case 'varchar':
										echo "<td class='col-sm-10'><input type='text' class='form-control' id='{$field->name}' placeholder='Text'/></td>";
										break;
									case 'dropdown':
										echo "<td class='col-sm-10'>" . form_dropdown($field->name, $dropdowns[$field->name], '', "id='{$field->name}'") . "</td>";
										break;
									case 'text':
										echo "<td class='col-sm-10'><textarea id='{$field->name}'></textarea></td>";
										break;
									case 'datetime':
										echo "<td class='col-sm-10'><input type='text' class='form-control' id='{$field->name}' autocomplete='off'/></td>";
										break;
									case 'int':
										echo "<td class='col-sm-10'><input type='text' class='form-control' id='{$field->name}' placeholder='Integer'/></td>";
										break;
									case 'decimal':
										echo "<td class='col-sm-10'><input type='text' class='form-control' id='{$field->name}' placeholder='Decimal'/></td>";
										break;
								}
							echo "</tr>";
						}
					}
					?>
				</table>

				<button type="submit" class="btn btn-primary" onclick="sign_off()">Sign Off</button>
			</div>
		</div>
	</div>
</div>

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

<script type="text/javascript" src="<?= base_url("js/signature_pad.umd.js") ?>"></script>
<script type="text/javascript" src="<?= base_url("js/signature_pad.app.js") ?>"></script>
