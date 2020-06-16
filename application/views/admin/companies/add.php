
<script>
	async function try_submit() {
		// Decide data to send
		var data = {};
		data['company_name'] = document.getElementById('company_name').value;
		data['company_db'] = document.getElementById('company_db').value;
		// Send data to the API
		const response = await fetch('<?= base_url("index.php/api/company_add") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers:{
				'Content-Type': 'application/json'
			}
		});
		// Grab the API's response
		const myJson = await response.json(); // extract JSON from the http response
		// Report the response
		alert(myJson);
		// Return to the Companies list upon success
		if (myJson == 'Company created.') {
			window.location.href = '<?= base_url("index.php/maintenance/companies") ?>';
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
					<label class="form-control-label">Company Name</label>
					<input type="text" class="form-control" id="company_name" placeholder="Company Name"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Company Database</label>
					<input type="text" class="form-control" id="company_db" placeholder="Company Database Name"/>
				</div>

				<div class="form-group">
					<label class="form-control-label"> No Database - For API users (instead of Forms)</label>
					<input type="checkbox" name="no_db" value="1"/>
				</div>

				<button type="button" class="btn btn-primary" onclick="try_submit()">Save</button>
			</div>
		</div>
	</div>
</div>
