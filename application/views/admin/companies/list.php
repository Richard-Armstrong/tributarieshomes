
<div class="content">
	<div class="container-fluid">
		<h1>
			<?= $title ?>
			<a href="<?= base_url("index.php/maintenance/add_company") ?>" class="btn btn-primary">Add Company</a>
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
							<th>Database</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
					<?php
					foreach ($records as $row) {
						echo '<tr>';
							if ($row->active)
								echo "<td>" . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . "</td>";
							else
								echo "<td>" . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . " (INACTIVE)</td>";
							echo "<td>" . htmlspecialchars($row->db, ENT_QUOTES, 'UTF-8') . "</td>";
							echo '<td><a href="' . base_url("index.php/maintenance/edit_company/{$row->id}") . '" class="btn btn-primary btn-sm">Edit</a></td>';
						echo '</tr>';
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
