
<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("admin/edit_bio/{$record->bio_id}") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Bio Name</label>
					<input type="text" class="form-control" id="bio_name" name="bio_name" placeholder="Bio Name"
						value="<?= htmlspecialchars($record->bio_name, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Job Title</label>
					<input type="text" class="form-control" id="bio_title" name="bio_title" placeholder="Job Title"
					value="<?= htmlspecialchars($record->bio_title, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Companies</label>
					<input type="text" class="form-control" id="bio_companies" name="bio_companies" placeholder="Companies"
					value="<?= htmlspecialchars($record->bio_companies, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Image</label>
					<input type="text" class="form-control" id="bio_image" name="bio_image" placeholder="Image URL"
					value="<?= htmlspecialchars($record->bio_image, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Sequence</label>
					<input type="text" class="form-control" id="bio_seq" name="bio_seq" placeholder="Sequence"
					value="<?= htmlspecialchars($record->bio_seq, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Bio Text</label>
					<input type="text" class="form-control" id="bio_description" name="bio_description" placeholder="Bio Text"
					value="<?= htmlspecialchars($record->bio_description, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</div>
</div>
