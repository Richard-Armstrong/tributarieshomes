
<script>
	function confirm_delete() {
		if (confirm("Are you sure you wish to delete this department?")) {
			window.location.href = "<?= base_url("index.php/maintenance/delete_department/{$record->id}") ?>";
		}
	}
</script>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("maintenance/edit_department/{$record->id}") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Department Name</label>
					<input type="text" class="form-control" name="name" value="<?= $record->name ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Description</label>
					<input type="text" class="form-control" name="description" value="<?= $record->description ?>"/>
				</div>

				<button type="submit" class="btn btn-primary">Save Department</button>

				<button type="button" class="btn btn-primary" onclick="confirm_delete()">Delete Department</button>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
