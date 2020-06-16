
<div class="content">
	<div class="container-fluid">
		<h1>Users</h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<table class="table table-striped">
					<tr>
						<th><?= lang('index_fname_th') ?></th>
						<th><?= lang('index_lname_th') ?></th>
						<th><?= lang('index_email_th') ?></th>
						<th><?= lang('index_groups_th') ?></th>
						<th><?= lang('index_levels_th') ?></th>
						<th><?= lang('index_status_th') ?></th>
						<th><?= lang('index_action_th') ?></th>
					</tr>

					<?php
					foreach ($users as $user) {
						echo "<tr>";
							// First Name
							echo "<td>" . htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8') . "</td>";
							// Last Name
							echo "<td>" . htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8') . "</td>";
							// Email
							echo "<td>" . htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') . "</td>";
							// User Departments
							echo "<td>";
							foreach ($user->groups as $group) {
								echo htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8') . "<br>";
							}
							echo "</td>";
							// User Level
							echo "<td>" . htmlspecialchars($user->level, ENT_QUOTES, 'UTF-8') . "</td>";
							// Activate/Deactivate
							echo "<td>";
								echo ($user->active)
									? anchor("auth/deactivate/{$user->id}", lang('index_active_link'))
									: anchor("auth/activate/{$user->id}", lang('index_inactive_link'));
							echo "</td>";
							// Edit
							echo "<td>" . anchor("auth/edit_user/{$user->id}", 'Edit') . "</td>";
						echo "</tr>";
					}
					?>
				</table>

				<a href="<?= base_url("index.php/auth/create_user") ?>" class="btn btn-primary">Create</a>
			</div>
		</div>
	</div>
</div>
