
<script>
	function delete_alert() {
		if (confirm('Are you sure you want to delete this alert?')) {
			window.location.href = '<?= base_url("index.php/alerts/delete/{$form->id}/{$alert->id}") ?>';
		}
	}

	$(document).ready(function() {
		$('.timepicker').timepicker({
		    timeFormat: 'h:mm p',
		    interval: 30,
		    minTime: '0',
		    maxTime: '11:30pm',
		    defaultTime: '8:00AM',
		    startTime: '00:00',
		    dynamic: false,
		    dropdown: true,
		    scrollbar: true
		});
	});
</script>

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("alerts/edit/{$form->id}/{$alert->id}") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Frequency</label>
					<input type="text" class="form-control" name="frequency" placeholder="Number of days until the alert goes off"
						value="<?= $alert->frequency ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Quota</label>
					<input type="text" class="form-control" name="quota" placeholder="Number of entries within the frequency"
						value="<?= $alert->quota ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Primary Recepient</label>
					<?= form_dropdown('primary', $users, $alert->primary) ?>
				</div>

				<div class="form-group">
					<label class="form-control-label">Secondary Recepient</label>
					<?= form_dropdown('secondary', $users, $alert->secondary) ?>
				</div>

				<div class="form-group">
					<label class="form-control-label">Day(s) to Run</label><br>
					<label class="form-control-label">
						Monday
						<input type="checkbox" name="days[1]" value="1"<?php if (in_array(1, $days)) echo " CHECKED"; ?>/>
					</label><br>
					<label class="form-control-label">
						Tuesday
						<input type="checkbox" name="days[2]" value="2"<?php if (in_array(2, $days)) echo " CHECKED"; ?>/>
					</label><br>
					<label class="form-control-label">
						Wednesday
						<input type="checkbox" name="days[3]" value="3"<?php if (in_array(3, $days)) echo " CHECKED"; ?>/>
					</label><br>
					<label class="form-control-label">
						Thursday
						<input type="checkbox" name="days[4]" value="4"<?php if (in_array(4, $days)) echo " CHECKED"; ?>/>
					</label><br>
					<label class="form-control-label">
						Friday
						<input type="checkbox" name="days[5]" value="5"<?php if (in_array(5, $days)) echo " CHECKED"; ?>/>
					</label><br>
					<label class="form-control-label">
						Saturday
						<input type="checkbox" name="days[6]" value="6"<?php if (in_array(6, $days)) echo " CHECKED"; ?>/>
					</label><br>
					<label class="form-control-label">
						Sunday
						<input type="checkbox" name="days[7]" value="7"<?php if (in_array(7, $days)) echo " CHECKED"; ?>/>
					</label>
				</div>

				<div class="form-group">
					<label class="form-control-label">Time to Run</label>
					<input type="text" class="timepicker" name="time" value="<?= $alert->time ?>"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">One Time?</label>
					<input type="checkbox" name="onetime" value="1" <?php if ($alert->onetime) echo "CHECKED"; ?>/>
				</div>

				<div class="form-group">
					<label class="form-control-label">On Entry?</label>
					<input type="checkbox" name="onentry" value="1" <?php if ($alert->onentry) echo "CHECKED"; ?>/>
				</div>

				<button type="submit" class="btn btn-primary">Save</button>
				<button type="button" class="btn btn-primary" onclick="delete_alert()">Delete</button>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
