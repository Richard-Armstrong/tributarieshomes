
<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("maintenance/edit_company/{$record->id}") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Company Name</label>
					<input type="text" class="form-control" name="company_name" placeholder="Company Name"
						value="<?= htmlspecialchars($record->name, ENT_QUOTES, 'UTF-8') ?>"/>
				</div>

				<?php if (is_superuser($this->session->userdata('user_level'))) : ?>
					<div class="form-group">
						<label class="form-control-label">Company Database</label>
						<input type="text" class="form-control" value="<?= htmlspecialchars($record->db, ENT_QUOTES, 'UTF-8') ?>" DISABLED/>
					</div>
				<?php endif // superuser ?>

				<div class="form-group">
					<label class="form-control-label">Company GUID</label>
					<input type="text" class="form-control" value="<?= $record->guid ?>" DISABLED/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Company API Key</label>
					<input type="text" class="form-control" value="<?= $record->api_key ?>" DISABLED/>
				</div>

				<?php if (is_superuser($this->session->userdata('user_level'))) : ?>
						<div class="form-group">
							<h4>Able to Access via API:</h4>
							<?php
							foreach ($companies as $company) {
								echo "<label class='checkbox'>";
									$checked = '';
									if (!$access_to)
										$access_to = array();
									if (in_array($company->id, $access_to))
										$checked = ' CHECKED';
									echo "<input type='checkbox' name='companies[]' value='{$company->id}'{$checked}/>";
									echo htmlspecialchars($company->name, ENT_QUOTES, 'UTF-8');
									if (!$company->active)
										echo " (INACTIVE)";
								echo "</label>";
							}
							?>
						</div>
				<?php endif // superuser ?>

				<button type="submit" class="btn btn-primary">Save</button>
				<?php if (is_superuser($this->session->userdata('user_level'))) : ?>
					<?php if ($record->active) { ?>
						<a href="<?= base_url("index.php/maintenance/deactivate_company/{$record->id}") ?>" class="btn btn-primary">Deactivate</a>
					<?php } else { ?>
						<a href="<?= base_url("index.php/maintenance/activate_company/{$record->id}") ?>" class="btn btn-primary">Activate</a>
					<?php } ?>
				<?php endif // superuser ?>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
