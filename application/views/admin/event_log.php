
<script>
	$(document).ready(function() {
		$('#start').datepicker({
			format: "mm/dd/yyyy"
		});

		$('#end').datepicker({
			format: "mm/dd/yyyy"
		});
	});

	function initials(id) {
		window.open('<?= base_url("index.php/maintenance/view_event_initials/") ?>' + id, '', "width=500 height=250");
	}
</script>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("maintenance/event_log") ?>

		<div class="card">
			<div class="card-body">
				<button type="submit" class="btn btn-primary">Filter</button>

				<table class="table">
					<tr>
						<td>Start Date <input type="text" id="start" name="start" autocomplete="off"/></td>
						<td>User <?= form_dropdown('user', $users) ?></td>
					</tr>

					<tr>
						<td>End Date <input type="text" id="end" name="end" autocomplete="off"/></td>
						<td>Event <?= form_dropdown('event_type', $event_types) ?></td>
					</tr>
				</table>

				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>User</th>
							<th>Event</th>
							<th>Date</th>
							<th>Signature</th>
						</tr>
					</thead>

					<?php
					foreach ($events as $event) {
						echo "<tr>";
							echo "<td>{$event->user}</td>";
							echo "<td>{$event->event}</td>";
							echo "<td>{$event->date}</td>";
							if ($event->signature != NULL)
								echo "<td><button type='button' class='btn btn-primary btn-sm' onclick='initials({$event->id})'>View</button></td>";
							else
								echo "<td></td>";
						echo "</tr>";
					}
					?>
				</table>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
