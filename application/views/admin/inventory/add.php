
<script>
	async function try_submit() {
		// Decide data to send
		var data = {};
		data['inv_name'] = document.getElementById('inv_name').value;
		data['inv_directory'] = document.getElementById('inv_directory').value;
		data['inv_description'] = document.getElementById('inv_description').value;
		data['inv_desc_short'] = document.getElementById('inv_desc_short').value;
		data['active_flag'] = document.getElementById('active_flag').value;
		data['seq'] = document.getElementById('seq').value;
		data['landing_image'] = document.getElementById('landing_image').value;
		data['flythru_link'] = document.getElementById('flythru_link').value;



		// Send data to the API
		const response = await fetch('<?= base_url("index.php/api/inventory_add") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers:{
				'Content-Type': 'application/json'
			}
		});
alert(response);

		// Grab the API's response
		const myJson = await response.json(); // extract JSON from the http response
		// Report the response
		alert(myJson);
		// Return to the Companies list upon success
		if (myJson == 'Inventory created.') {
			window.location.href = '<?= base_url("index.php/admin/inventory") ?>';
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
				<div class="form-group">
					<label class="form-control-label">Name</label>
					<input type="text" class="form-control" id="inv_name" placeholder="Inventory Name"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Directory</label>
					<input type="text" class="form-control" id="inv_directory" placeholder="Directory"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Description</label>
					<input type="text" class="form-control" id="inv_description" placeholder="Description"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Short Description</label>
					<input type="text" class="form-control" id="inv_desc_short" placeholder="Short"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Landing Image</label>
					<input type="text" class="form-control" id="landing_image" placeholder="Landing Image"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Flythru Link</label>
					<input type="text" class="form-control" id="flythru_link" placeholder="Flythru"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Sequence</label>
					<input type="text" class="form-control" id="seq" placeholder="Sequence"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Active?</label>
					<input type="Checkbox" class="form-control" id="active_flag" CHECKED/>
				</div>

				<button type="button" class="btn btn-primary" onclick="try_submit()">Save</button>
			</div>
		</div>
	</div>
</div>
