
<div class="content">
	<div class="container-fluid">
		<h1>
			<?= $title ?>
			<a href="<?= base_url("index.php/admin/add_bio") ?>" class="btn btn-primary">Add Bio</a>
		</h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<table class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>Name</th>
							<th>Description</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
					<?php
					if (isset($records)) {
						foreach ($records as $row) {
							echo '<tr>';
								echo "<td>" . htmlspecialchars($row->bio_name, ENT_QUOTES, 'UTF-8') . "</td>";
								echo "<td>" . htmlspecialchars($row->bio_description, ENT_QUOTES, 'UTF-8') . "</td>";
								echo '<td><a href="' . base_url("index.php/admin/edit_bio/{$row->bio_id}") . '" class="btn btn-primary btn-sm">Edit</a></td>';
							echo '</tr>';
						}
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
