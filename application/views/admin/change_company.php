
<script>
	async function change_company() {
		var e = document.getElementById('company');
		var company = e[e.selectedIndex].value;

		var data = {'company' : company};
		const response = await fetch('<?= base_url("index.php/api/change_company") ?>', {
			method: 'POST',
			body: JSON.stringify(data),
			headers:{
				'Content-Type': 'application/json'
			}
		});

		const myJson = await response.json(); // extract JSON from the http response

		location.reload();
	}
</script>

<div class="content">
	<div class="container-fluid">
		<h1>Change Company</h1>

		<div id="infoMessage"><br><?= $this->session->flashdata('message') ?></div>

		<div class="card">
			<div class="card-body">
				<?php
				if (is_superuser($this->session->userdata('user_level'))) {
					echo "<div>";
						echo "<h4>Use this dropdown to change your company for this session!</h4>";
						echo form_dropdown('company', $companies, $this->session->userdata('user_company'), 'id="company" onchange="change_company()"');
					echo "</div>";
				}
				?>
			</div>
		</div>
	</div>
</div>
