<?php

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
	$uname = $_POST["username"];
	$pwd = $_POST["password"];
	$hash = password_hash($pwd, PASSWORD_DEFAULT);
	$sql = "INSERT INTO users(username, password) values(?, ?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ss", $uname, $hash);
	if ($stmt->execute()) {
		$msg = "Registration successfull";
	} else {
		$msg = "Registration failed";
	}
}

$conn->close();

?>

<!DOCTYPE html>
<html>

<head>
	<title>Register</title>
</head>

<body>
	<form method="POST">
		<input type="text" name="username" />
		<input type="password" name="password" />
		<input type="submit" value="register" />
	</form>
	<p><?php echo $msg; ?></p>
	<a href="/login.php">Login</a>
</body>

</html>