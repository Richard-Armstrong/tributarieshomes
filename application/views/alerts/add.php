
<script>
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

		<?= form_open("alerts/add/{$form->id}") ?>

		<div class="card">
			<div class="card-body">
				<div class="form-group">
					<label class="form-control-label">Frequency</label>
					<input type="text" class="form-control" name="frequency" placeholder="Number of days until the alert goes off"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Quota</label>
					<input type="text" class="form-control" name="quota" placeholder="Number of entries within the frequency"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">Primary Recepient</label>
					<?= form_dropdown('primary', $users) ?>
				</div>

				<div class="form-group">
					<label class="form-control-label">Secondary Recepient</label>
					<?= form_dropdown('secondary', $users) ?>
				</div>

				<div class="form-group">
					<label class="form-control-label">Day(s) to Run</label><br>
					<label class="form-control-label">
						Monday
						<input type="checkbox" name="days[1]" value="1"/>
					</label><br>
					<label class="form-control-label">
						Tuesday
						<input type="checkbox" name="days[2]" value="2"/>
					</label><br>
					<label class="form-control-label">
						Wednesday
						<input type="checkbox" name="days[3]" value="3"/>
					</label><br>
					<label class="form-control-label">
						Thursday
						<input type="checkbox" name="days[4]" value="4"/>
					</label><br>
					<label class="form-control-label">
						Friday
						<input type="checkbox" name="days[5]" value="5"/>
					</label><br>
					<label class="form-control-label">
						Saturday
						<input type="checkbox" name="days[6]" value="6"/>
					</label><br>
					<label class="form-control-label">
						Sunday
						<input type="checkbox" name="days[7]" value="7"/>
					</label>
				</div>

				<div class="form-group">
					<label class="form-control-label">Time to Run</label>
					<input type="text" class="timepicker" name="time"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">One Time?</label>
					<input type="checkbox" name="onetime" value="1"/>
				</div>

				<div class="form-group">
					<label class="form-control-label">On Entry?</label>
					<input type="checkbox" name="onentry" value="1"/>
				</div>

				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>

		<?= form_close() ?>
	</div>
</div>
