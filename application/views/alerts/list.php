
<div class="content">
	<div class="container-fluid">
		<h1>
			<?= $title ?>
			<a href="<?= base_url("index.php/alerts/add/{$form->id}") ?>" class="btn btn-primary">Add Alert</a>
		</h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Frequency</th>
							<th>Quota</th>
							<th>Primary</th>
							<th>Secondary</th>
							<th>One Time?</th>
							<th>On Entry?</th>
							<th>Creator</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						<?php
						foreach ($alerts as $alert) {
							echo "<tr>";
							echo "<td>{$alert['frequency']}</td>";
							echo "<td>{$alert['quota']}</td>";
							echo "<td>{$alert['primary']}</td>";
							echo "<td>{$alert['secondary']}</td>";
							echo "<td>{$alert['onetime']}</td>";
							echo "<td>{$alert['onentry']}</td>";
							echo "<td>{$alert['creator']}</td>";
							echo "<td>";
							echo "<a href='" . base_url("index.php/alerts/edit/{$form->id}/{$alert['id']}") . "' class='btn btn-primary btn-sm'>Edit</a>";
							echo "</td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
