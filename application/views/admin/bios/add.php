
<script>
	async function try_submit() {
		// Decide data to send
		var data = {};
		data['bio_name'] = document.getElementById('bio_name').value;
		data['bio_title'] = document.getElementById('bio_title').value;
		data['bio_companies'] = document.getElementById('bio_companies').value;
		data['bio_image'] = document.getElementById('bio_image').value;
		data['bio_seq'] = document.getElementById('bio_seq').value;
		data['bio_description'] = document.getElementById('bio_description').value;

		// Send data to the API
		const response = await fetch('<?= base_url("index.php/api/bio_add") ?>', {
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
		if (myJson == 'Bio created.') {
			window.location.href = '<?= base_url("index.php/admin/bios") ?>';
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
					<input type="text" class="form-control" id="bio_name" placeholder="Bio Name"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Job Title</label>
					<input type="text" class="form-control" id="bio_title" placeholder="Job Title"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Companies</label>
					<input type="text" class="form-control" id="bio_companies" placeholder="Companies"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Image</label>
					<input type="text" class="form-control" id="bio_image" placeholder="Image URL"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Sequence</label>
					<input type="text" class="form-control" id="bio_seq" placeholder="Sequence"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Bio Text</label>
					<input type="text" class="form-control" id="bio_description" placeholder="Bio Text"/>
				</div>

				<button type="button" class="btn btn-primary" onclick="try_submit()">Save</button>
			</div>
		</div>
	</div>
</div>
