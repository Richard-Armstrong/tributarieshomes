
<div class="content">
	<div class="container-fluid">
		<h1>Edit User</h1>

		<div id="infoMessage"><?= $message ?></div>

		<?= form_open(uri_string()) ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Email</label>
					<input type="text" class="form-control" name="email" value="<?= $user->email ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">First Name</label>
					<input type="text" class="form-control" name="first_name" value="<?= $user->first_name ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Last Name</label>
					<input type="text" class="form-control" name="last_name" value="<?= $user->last_name ?>"/>
				</div>

				<?php if (is_superuser($this->session->userdata('user_level'))) : ?>
					<div class="form-group">
						<label class="form-control-label">Company Name</label>
						<?= form_dropdown('company', $companies, $user->company) ?>
					</div>
				<?php endif ?>

				<div class="form-group">
					<label class="form-control-label">Phone</label>
					<input type="text" class="form-control" name="phone" value="<?= $user->phone ?>"/>
				</div>

				<?php if (is_account_manager($this->session->userdata('user_level'))) : ?>
					<div class="form-group">
						<label class="form-control-label">Password (if changing password)</label>
						<input type="password" class="form-control" name="password" autocomplete="off" />
					</div>

					<div class="form-group">
						<label class="form-control-label">Confirm Password (if changing password)</label>
						<input type="password" class="form-control" name="password_confirm" />
					</div>
				<?php endif ?>

				<?php if (is_superuser($this->session->userdata('user_level')) || $this->ion_auth->user()->id == $user->id) : ?>
					<div class="form-group">
						<label class="checkbox">
							<input type="checkbox" name="notify_sms" value="1"<?= ($user->notify_sms) ? ' CHECKED' : '' ?>/>
							Notify via SMS
						</label>

						<label class="checkbox">
							<input type="checkbox" name="notify_email" value="1"<?= ($user->notify_email) ? ' CHECKED' : '' ?>/>
							Notify via Email
						</label>
					</div>
				<?php endif ?>

				<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
					<?php if (compare_user_level($this->session->userdata('user_level'), $user->level)) : ?>
						<div class="form-group">
							<h4>User Level</h4>
							<?= form_dropdown('user_level', $user_levels, $user->level) ?>
						</div>

						<div class="form-group">
							<h4>Member of Departments</h4>
							<?php
							foreach ($groups as $group) {
								echo "<label class='checkbox'>";
									$checked = NULL;
									foreach ($currentGroups as $grp) {
										if ($group['id'] == $grp->id) {
											$checked = " checked='checked'";
											break;
										}
									}
									echo "<input type='checkbox' name='groups[]' value='{$group['id']}'{$checked}/>";
									echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8');
								echo "</label>";
							}
							?>
						</div>
					<?php endif // user_level ?>
				<?php endif // company_manager ?>

				<?= form_hidden('id', $user->id) ?>
				<?= form_hidden($csrf) ?>

				<button type="submit" class="btn btn-primary">Save User</button>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
