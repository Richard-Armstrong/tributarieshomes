
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Sign in &middot; BluWave Forms</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<!-- Le styles -->
		<link rel="stylesheet" media="all" type="text/css" href="<?= base_url("css/bootstrap.css") ?>"/>
		<link rel="stylesheet" media="all" type="text/css" href="<?= base_url("css/bootstrap-responsive.css") ?>"/>
		<link rel="stylesheet" media="all" type="text/css" href="<?= base_url("css/app.css") ?>"/>
		<link rel="stylesheet" media="all" type="text/css" href="<?= base_url("css/icons.css") ?>"/>
		<link rel="stylesheet" media="all" type="text/css" href="<?= base_url("css/style.css") ?>"/>

		<style type="text/css">
			body {
				padding-top: 40px;
				padding-bottom: 40px;
				background-color: #f5f5f5;
			}
			.form-signin {
				max-width: 300px;
				padding: 19px 29px 29px;
				margin: 0 auto 20px;
				background-color: #fff;
				border: 1px solid #e5e5e5;
				-webkit-border-radius: 5px;
				   -moz-border-radius: 5px;
						border-radius: 5px;
				-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
				   -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
						box-shadow: 0 1px 2px rgba(0,0,0,.05);
			}
			.form-signin .form-signin-heading,
			.form-signin .checkbox {
				margin-bottom: 10px;
			}
			.form-signin input[type="text"],
			.form-signin input[type="password"] {
				font-size: 16px;
				height: auto;
				margin-bottom: 15px;
				padding: 7px 9px;
			}
		</style>

		<!-- Fav and touch icons -->
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="../assets/ico/favicon.png">
	</head>

	<body>
		<div class="container">
			<?= form_open("auth/login", array( 'class'	=> 'form-signin' )) ?>

			<div style="text-align:center;"><img src="<?= base_url("assets/img/tributary_logo.png") ?>" /></div>

			<div id="infoMessage"><br><?= $this->session->flashdata('message') ?></div>
			<?= validation_errors() ?>

			<br>

			<div style="text-align:center;"><h3 class="form-signin-heading">Please sign in</h3></div>

			<p><?= form_input($identity) ?></p>

			<p><?= form_password($password) ?></p>

			<label class="checkbox">
				<input type="checkbox" value="remember-me"> Remember me
			</label>

			<a href="<?= base_url("index.php/auth/forgot_password") ?>">Forgot Password</a>
			<div style="text-align:center;"><input class="btn btn-large btn-primary" type="submit" value="Sign in"/></div>

			<br>

			<p>
				Looking for our API documentation? Click below!<br>
				<a href="<?= base_url("index.php/documentation") ?>">Documentation</a>
			</p>
			<?= form_close() ?>
		</div>
	</body>
</html>
