
<div class="content">
	<div class="container-fluid">
		<h1>Create User</h1>

		<div id="infoMessage"><?= $message ?></div>

		<?= form_open("auth/create_user") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">First Name</label>
					<input type="text" class="form-control" name="first_name"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Last Name</label>
					<input type="text" class="form-control" name="last_name"/>
				</div>

				<?php if (is_superuser($this->session->userdata('user_level'))) : ?>
					<div class="form-group">
						<label class="form-control-label">Company Name</label>
						<?= form_dropdown('company', $companies) ?>
					</div>
				<?php endif ?>

				<div class="form-group">
					<label class="form-control-label">Email</label>
					<input type="text" class="form-control" name="email"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Phone</label>
					<input type="text" class="form-control" name="phone"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Password</label>
					<input type="password" class="form-control" name="password"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Confirm Password</label>
					<input type="password" class="form-control" name="password_confirm"/>
				</div>

				<div class="form-group">
					<h4>User Level</h4>
					<?= form_dropdown('user_level', $user_levels) ?>
				</div>

				<div class="form-group">
					<h4>Member of Departments</h4>
					<?php
					foreach ($groups as $group) {
						echo "<label class='checkbox'>";
							echo "<input type='checkbox' name='groups[]' value='{$group['id']}'>";
							echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8');
						echo "</label>";
					}
					?>
				</div>

				<button type="submit" class="btn btn-primary">Create User</button>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
