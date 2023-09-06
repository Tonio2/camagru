<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/validate.php";

$session = new Session();
$session->require_not_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["code"])) {
	$code = $_GET["code"];
	$code = $conn->real_escape_string($code);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["password"]) && isset($_POST["code"])) {
	$code = $_POST["code"];
	$code = $conn->real_escape_string($code);
	$sql = "SELECT * FROM users WHERE password_reset_code = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $code);
	$stmt->execute();
	$res = $stmt->get_result();

	if ($res->num_rows > 0) {
		$pwd = $_POST["password"];
		$errors = validatePassword($pwd);
		if (empty($errors)) {
			$hash = password_hash($pwd, PASSWORD_DEFAULT);
			$sql = "UPDATE users SET password = ?, password_reset_code = ? WHERE password_reset_code = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("sss", $hash, $nullValue, $code);
			if ($stmt->execute()) {
				echo "New password set. You can now login";
			} else {
				echo "Something went wrong";
			}
		} else {
			echo "Password validation failed: " . implode(", ", $errors);
		}
	} else {
		echo "Something went wrong";
	}
}

?>

<!doctype html>
<html>

<head>
	<title>Reset Password</title>
</head>

<body>
	<form action="" method="POST">
		<input type="hidden" name="code" value="<?php echo htmlspecialchars($code) ?>">
		<label for="password">New Password:</label>
		<input type="password" name="password" id="password" required>
		<input type="submit" value="Reset Password">
	</form>
</body>

</html>