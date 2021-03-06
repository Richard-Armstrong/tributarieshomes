
<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<?= form_open("maintenance/form_reactivate/{$form_id}") ?>

		<h4>Are you sure you want to reactivate this form?</h4>

		<p>
			Yes
			<input type="radio" name="confirm" value="yes" checked="checked"/>
			No
			<input type="radio" name="confirm" value="no"/>
		</p>

		<p><?= form_submit('submit', 'Submit') ?></p>

		<?= form_close() ?>
	</div>
</div>
