
<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("maintenance/add_department") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Department Name</label>
					<input type="text" class="form-control" name="name"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Description</label>
					<input type="text" class="form-control" name="description"/>
				</div>

				<button type="submit" class="btn btn-primary">Create Department</button>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
