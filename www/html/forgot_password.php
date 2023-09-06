<?php

require_once "../config/config.php";
require_once "../classes/database.php";
require_once "../classes/session.php";
require_once "../utils/validate.php";
require_once "../utils/mail.php";

$session = new Session();
$session->require_not_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$email = sanitizeInput($_POST["email"]);
	$sql = "SELECT * FROM users WHERE email = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$res = $stmt->get_result();

	if ($res->num_rows > 0) {
		$code = bin2hex(random_bytes(32));
		$sql = "UPDATE users SET password_reset_code = ? WHERE email = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $code, $email);
		if ($stmt->execute()) {
			if (sendResetPasswordMail($email, $code)) {
				$msg = "An email has been sent. Check your inbox.";
			} else {
				$msg = "Something went wrong";
			}
		} else {
			$msg = "Something went wrong";
		}
	} else {
		$msg = "Something went wrong";
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	<title>Reset Password</title>
</head>

<body>
	<h2>Reset Password</h2>
	<form action="forgot_password.php" method="post">
		Email: <input type="mail" name="email" />
		<input type="submit" value="Submit" />
	</form>
	<?php if (!empty($msg)) : ?>
		<div class="error-message">
			<?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php endif; ?>
</body>

</html>