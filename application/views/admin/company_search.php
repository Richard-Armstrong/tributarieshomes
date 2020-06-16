
<script>
	function initials(id, form_id) {
		window.open('<?= base_url("index.php/main/view_initials/") ?>' + form_id + '/' + id, '', "width=500 height=250");
	}
</script>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("maintenance/search") ?>

		<div class="card">
			<div class="card-body table-wrapper">
				<input type="text" name="search" placeholder="Search"/>
				<button type="submit" class="btn btn-primary">Search</button>

				<br>

				<?php foreach ($tables as $form_id => $table) : ?>
					<h3><?= $names[$form_id] ?></h3>

					<table class="table table-bordered">
						<thead>
							<tr>
								<?php for ($i = 1; $i < count(array_keys(get_object_vars($table[0]))); $i++) : ?>
									<th><?= array_keys(get_object_vars($table[0]))[$i] ?></th>
								<?php endfor ?>
								<th>Initials</th>
							</tr>
						</thead>

						<tbody>
							<?php foreach ($table as $row) : ?>
								<tr>
									<?php for ($i = 1; $i < count(array_keys(get_object_vars($table[0]))); $i++) : ?>
										<td><?= $row->{array_keys(get_object_vars($table[0]))[$i]} ?></td>
									<?php endfor ?>
									<td><button type="button" class="btn btn-primary btn-sm" onclick="initials(<?= $row->id ?>, <?= $form_id ?>)">View</button></td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				<?php endforeach ?>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
