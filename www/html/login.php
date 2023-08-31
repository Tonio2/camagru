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

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$uname = $_POST["username"];
	$pwd = $_POST["password"];

	$sql = "SELECT * FROM users WHERE username = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $uname);
	$stmt->execute();
	$res = $stmt->get_result();

	if ($res->num_rows > 0) {
		$row = $res->fetch_assoc();
		$hash = $row["password"];
		if (password_verify($pwd, $hash)) {
			session_start();
			$_SESSION["logged_in"] = true;
			$_SESSION["uname"] = $row["username"];
			$_SESSION["userId"] = $row["id"];
			header("Location: index.php");
		}
	}
	
	$msg = 'invalid credentials';
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
	<title>Login</title>
</head>

<body>
	<form method="POST">
		<input type="text" name="username" />
		<input type="password" name="password" />
		<input type="submit" value="login" />
	</form>
	<a href="/register.php">Create an account</a>
	<?php if (!empty($msg)) : ?>
		<div class="error-message">
			<?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php endif; ?>
</body>

</html>