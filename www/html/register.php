<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/validate.php";

$session = new Session();
$session->require_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$msg = "Bad Request";
	$errors = [];
	$uname = sanitizeInput($_POST["username"]); // sanitize to avoid xss attack
	$email = sanitizeInput($_POST["email"]); // sanitize to avoid xss attack
	$pwd = $_POST["password"]; // no need to sanitize it because it will be hashed
	$errors = validatePassword($pwd);
	if (validateUsername($uname) && validateEmail($email) && empty($errors)) {
		$hash = password_hash($pwd, PASSWORD_DEFAULT);
		$sql = "INSERT INTO users(username, email, password) values(?, ?, ?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sss", $uname, $email, $hash);
		if ($stmt->execute()) {
			$msg = "Registration successfull";
		}
	}
}

$db->closeConnection();
?>

<!DOCTYPE html>
<html>

<head>
	<title>Register</title>
</head>

<body>
	<form action="register.php" method="post">
		Username: <input type="text" name="username" required><br>
		Email: <input type="email" name="email" required><br>
		Password: <input type="password" name="password" required><br>
		<input type="submit" value="Register">
	</form>
	<?php if (!empty($msg)) : ?>
		<div class="error-message">
			<?php
			echo htmlentities($msg, ENT_QUOTES, 'UTF-8') . "<br />";
			if (!validateUsername($uname)) {
				echo "Username must be at least 4 charachters long<br />";
			}
			if (!$email) {
				echo "Email is invalid<br />";
			}
			if (!empty($errors)) {
				echo "Password is weak for the following reasons:";
				foreach ($errors as $error) {
					echo "<br />- " . htmlentities($error, ENT_QUOTES, 'UTF-8');
				}
			}
			?>
		</div>
	<?php endif; ?>
	<a href="/login.php">Login</a>
</body>

</html>