
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
		<h1>Deactivated Forms</h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<table class="table">
					<tr>
						<th colspan=2>Forms</th>
					</tr>

					<?php foreach ($forms as $form) : ?>
						<tr>
							<td class="clickable-row pointer" data-href="<?= base_url("index.php/main/form_entries/{$form->id}") ?>">
								<?= "{$form->name} Form Entries" ?>
							</td>

							<td class="dropdown" style="font-size:1.25rem;">
								<a href="#" class="dropdown-toggle arrow-none" data-toggle="dropdown">
									<i class="mdi mdi-hamburger"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<a href="<?= base_url("index.php/maintenance/form_reactivate/{$form->id}") ?>" class="dropdown-item notify-item">
										<?= "Reactivate {$form->name} Form" ?>
									</a>
								</div>
							</td>
						</tr>
					<?php endforeach ?>
				</table>
			</div>
		</div>
	</div>
</div>
