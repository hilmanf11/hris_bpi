<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title><?= $config->name ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="<?= $config->favicon ?>" type="image/png" sizes="16x16">
	<link rel="stylesheet" type="text/css" href="<?= base_url('assets/login/css/util.css') ?>">
	<link rel="stylesheet" type="text/css" href="<?= base_url('assets/login/css/main.css?' . time()) ?>">
	<link rel="stylesheet" type="text/css" href="<?= base_url('assets/login/fonts/font-awesome-4.7.0/css/font-awesome.min.css') ?>">
	<!--===============================================================================================-->
</head>

<body style="background-color: #666666;">
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<form class="login100-form validate-form" style="padding: 30px;" method="post">
					<center>
						<img src="<?= $config->logo ?>" width="150">
					</center>
					<br>
					<?= $message ?>
					<small>WELCOME IN</small>
					<h3>HUMAN RESOURCE INFORMATION SYSTEM</h3>
					<h2><?= $config->name ?></h2>
					<p>Please enter your username and password</p>
					<br>
					<div class="wrap-input100 validate-input">
						<input class="input100" type="text" placeholder="Username" name="username" required autofocus>
					</div>
					<div class="wrap-input100 validate-input" style="position: relative;">
						<input class="input100" type="password" id="passwordInput" placeholder="Password" name="password" style="padding-right: 45px;" required>
						<span id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #777; z-index: 10;">
							<i class="fa fa-eye" id="toggleIcon"></i>
						</span>
					</div>
					<br>
					<div class="container-login100-form-btn">
						<button class="login100-form-btn" type="submit" style="margin-bottom: 10px;">
							Login
						</button>
						<a href="login/forgot" style="margin-bottom: 15px;">Forgot Password ?</a>
						<a href="<?= base_url('assets/APK/hris-astech.apk') ?>" class="login100-form-btn" style="background-color: #28a745; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;" download>
							<i class="fa fa-download"></i> Download HRIS Android
						</a>
					</div>
				</form>
				<div class="login100-more" style="background-image: url('<?= $config->image ?>');"></div>
			</div>
		</div>
	</div>
</body>
<script>
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('passwordInput');
const toggleIcon = document.getElementById('toggleIcon');
if (togglePassword && passwordInput && toggleIcon) {
	togglePassword.addEventListener('click', function () {
		const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
		passwordInput.setAttribute('type', type);
		toggleIcon.classList.toggle('fa-eye');
		toggleIcon.classList.toggle('fa-eye-slash');
	});
}
</script>

</html>