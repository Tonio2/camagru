<?php
session_start();

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
	header("Location: login.php");
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
	$uname = $_SESSION["uname"];

	$sql = "DELETE FROM users WHERE username = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $uname);
	if ($stmt->execute()) {
		header("Location: /logout.php");
	} else {
		$msg = "Failed to delete account";
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Delete account</title>
	</head>

	<body>
		<p>Are you sure ? This action is not reversible</p>
		<form method="post">
			<input type="submit" value="Delete account" />
		</form>
	</body>
</html>