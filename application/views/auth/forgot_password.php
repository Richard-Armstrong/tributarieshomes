<div class="container">
	<h1><?= lang('forgot_password_heading') ?></h1>

	<p><?= sprintf(lang('forgot_password_subheading'), $identity_label) ?></p>

	<div id="infoMessage"><?= $message ?></div>

	<?= form_open("auth/forgot_password") ?>

	<p>
		<label for="identity">
			<?= (($type=='email')
				? sprintf(lang('forgot_password_email_label'), $identity_label)
				: sprintf(lang('forgot_password_identity_label'), $identity_label)) ?>
		</label><br>
		<?= form_input($identity) ?>
	</p>

	<p><?= form_submit('submit', lang('forgot_password_submit_btn')) ?></p>

	<?= form_close() ?>
</div>
