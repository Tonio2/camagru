<?php
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

	$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ss", $uname, $pwd);
	$stmt->execute();
	$res = $stmt->get_result();

	if ($res->num_rows > 0) {
		session_start();
		$_SESSION["logged_in"] = true;
		header("Location: index.php");
	} else {
		$msg = 'invalid credentials';
	}
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
	<p><?php echo $msg; ?></p>
</body>

</html>