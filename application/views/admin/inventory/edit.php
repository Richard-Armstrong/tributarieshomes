
<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("admin/edit_inventory/{$record->id}") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Inventory Name</label>
					<input type="text" class="form-control" id="inv_name" name="inv_name" placeholder="Inventory Name"
						value="<?= htmlspecialchars($record->inv_name, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Directory</label>
					<input type="text" class="form-control" id="inv_directory" name="inv_directory" placeholder="Directory"
					value="<?= htmlspecialchars($record->inv_directory, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Descriptio</label>
					<input type="text" class="form-control" id="bio_companies" name="inv_description" placeholder="Description"
					value="<?= htmlspecialchars($record->inv_description, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Desc Short</label>
					<input type="text" class="form-control" id="inv_desc_short" name="inv_desc_short" placeholder="Image URL"
					value="<?= htmlspecialchars($record->inv_desc_short, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Landing Image</label>
					<input type="text" class="form-control" id="landing_image" name="landing_image" placeholder="Landing Image"
					value="<?= htmlspecialchars($record->landing_image, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Flythru Link</label>
					<input type="text" class="form-control" id="flythru_link" name="flythru_link" placeholder="Flythru Link"
					value="<?= htmlspecialchars($record->flythru_link, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Sequence</label>
					<input type="text" class="form-control" id="seq" name="seq" placeholder="Sequence"
					value="<?= htmlspecialchars($record->seq, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Active?</label>
					<input type="checkbox" class="form-control" id="active_flag" name="active_flag" <?=$active_flag?>/>
				</div>

				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</div>
</div>
