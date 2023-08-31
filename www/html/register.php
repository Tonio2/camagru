<?php

session_start();

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]) {
	header("Location: index.php");
	exit();
}

$host = "db";
$db = $_ENV["MYSQL_DATABASE"];
$user = $_ENV["MYSQL_USER"];
$password = $_ENV["MYSQL_PASSWORD"];

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

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

$conn->close();

function sanitizeInput($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

function validateUsername($username)
{
	// Here you can add more username validation logic
	return isset($username) && strlen($username) > 4;
}

function validateEmail($email)
{
	// Here you can add more email validation logic
	return isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password)
{
	$errors = [];

	if (!isset($password)) {
		$errors[] = "A password must be provided.";
	}

	// Check length
	if (strlen($password) < 8) {
		$errors[] = "Password must be at least 8 characters long.";
	}

	// Check for at least one lowercase letter
	if (!preg_match('/[a-z]/', $password)) {
		$errors[] = "Password must contain at least one lowercase letter.";
	}

	// Check for at least one uppercase letter
	if (!preg_match('/[A-Z]/', $password)) {
		$errors[] = "Password must contain at least one uppercase letter.";
	}

	// Check for at least one digit
	if (!preg_match('/[0-9]/', $password)) {
		$errors[] = "Password must contain at least one number.";
	}

	// Check for at least one special character
	if (!preg_match('/[\W_]/', $password)) {
		$errors[] = "Password must contain at least one special character.";
	}

	return $errors;
}

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