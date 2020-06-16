<html>
	<body>
		<h1><?= sprintf(lang('email_activate_heading'), $identity) ?></h1>
		<p><?= sprintf(lang('email_activate_subheading'), anchor("http://45.33.98.211/index.php/auth/activate/{$id}/{$activation}", lang('email_activate_link'))) ?></p>
	</body>
</html>
