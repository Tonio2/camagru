<?php

function sendMail($to, $obj, $msg) {
	$headers = "From: labalette.antoine@gmail.com" . "\r\n" .
    "Reply-To: labalette.antoine@gmail.com" . "\r\n" .
    "X-Mailer: PHP/" . phpversion();
	return mail($to, $obj, $msg, $headers);
}

function sendConfirmationMail($email, $emailConfirmationCode) {
	$obj = "Welcome on Camagru - Email confirmation";
	$msg = "Welcome on Camagru !" . "\r\n" . "\r\n" .
		"Please click on this link to confirm your email: http://localhost/confirm_mail.php?code=" . $emailConfirmationCode;
	return sendMail($email, $obj, $msg);
}

function sendResetPasswordMail($email, $code) {
	$obj = "Camagru - Reset your password";
	$msg = "Please click on this link to reset your password: http://localhost/reset_password.php?code=" . $code;
	return sendMail($email, $obj, $msg);
}

?>