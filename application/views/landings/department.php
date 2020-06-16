
<script>
	$(document).ready(function() {
		$(".clickable-row").click(function() {
			return window.location = $(this).data("href")
		});
	});
</script>

<style>
	.table tbody > tr > td {
		height: 3.5rem;
		font-size: 1rem;
		vertical-align: middle;
	}
</style>

<div class="content">
	<div class="container-fluid">
		<h1>Welcome to the <?= ucwords($group->name) ?> Department!</h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
					<div>
						<a href="<?= base_url("index.php/main/create_form") ?>" class="btn btn-primary">Create Form</a>
					</div>
				<?php endif ?>

				<table class="table">
					<thead>
						<tr>
							<th colspan=2>Forms</th>
						</tr>
					</thead>

					<tbody>
					<?php foreach ($forms as $form) : ?>
						<tr>
							<td class="clickable-row pointer" data-href="<?= base_url("index.php/main/form/{$form->id}") ?>">
								<?= "{$form->name} Form" ?>
							</td>

							<td class="dropdown" style="font-size:1.25rem;">
								<a href="#" class="dropdown-toggle arrow-none" data-toggle="dropdown">
									<i class="mdi mdi-hamburger"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<a href="<?= base_url("index.php/main/form_entries/{$form->id}") ?>" class="dropdown-item notify-item">
										<?= "{$form->name} Form Entries" ?>
									</a>

									<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
										<a href="<?= base_url("index.php/main/form_logic/{$form->id}") ?>" class="dropdown-item notify-item">
											<?= "{$form->name} Logic" ?>
										</a>

										<a href="<?= base_url("index.php/main/edit_form/{$form->id}") ?>" class="dropdown-item notify-item">
											<?= "{$form->name} Edit" ?>
										</a>

										<a href="<?= base_url("index.php/main/form_import/{$form->id}") ?>" class="dropdown-item notify-item">
											<?= "{$form->name} Import" ?>
										</a>

										<a href="<?= base_url("index.php/alerts/list/{$form->id}") ?>" class="dropdown-item notify-item">
											<?= "{$form->name} Alerts" ?>
										</a>

										<a href="<?= base_url("index.php/main/form_deactivate/{$form->id}") ?>" class="dropdown-item notify-item">
											<?= "Deactivate {$form->name} Form" ?>
										</a>

										<a href="<?= base_url("index.php/main/create_form/{$form->id}") ?>" class="dropdown-item notify-item">
											<?= "Duplicate {$form->name} Form" ?>
										</a>
									<?php endif ?>
								</div>
							</td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
