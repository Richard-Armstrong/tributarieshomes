
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
		<h1>Deactivated Reports</h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<table class="table">
					<tr>
						<th colspan=2>Reports</th>
					</tr>

					<?php foreach ($reports as $report) : ?>
						<tr>
							<td class="clickable-row pointer" data-href="<?= base_url("index.php/reports/view/{$report->id}") ?>">
								<?= "{$report->name} Form Entries" ?>
							</td>

							<td class="dropdown" style="font-size:1.25rem;">
								<a href="#" class="dropdown-toggle arrow-none" data-toggle="dropdown">
									<i class="mdi mdi-hamburger"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<a href="<?= base_url("index.php/reports/reactivate/{$report->id}") ?>" class="dropdown-item notify-item">
										<?= "Reactivate {$report->name} Report" ?>
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
