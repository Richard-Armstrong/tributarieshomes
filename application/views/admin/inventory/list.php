
<div class="content">
	<div class="container-fluid">
		<h1>
			<?= $title ?>
			<a href="<?= base_url("index.php/admin/add_inventory") ?>" class="btn btn-primary">Add Inventory</a>
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
								echo "<td>" . htmlspecialchars($row->inv_name, ENT_QUOTES, 'UTF-8') . "</td>";
								echo "<td>" . htmlspecialchars($row->inv_description, ENT_QUOTES, 'UTF-8') . "</td>";
								echo '<td';
								if ($row->active_flag) {
									echo ' style="background-color:#00ff99;"';
								}
								echo '><a href="' . base_url("index.php/admin/edit_inventory/{$row->id}") . '" class="btn btn-primary btn-sm">Edit</a></td>';
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
