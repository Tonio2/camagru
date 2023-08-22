<?php
session_start();

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
	header("Location: login.php");
}

echo 'hello';
?>

<!DOCTYPE html>
<html>

<head>
	<title>HOME</title>
</head>

<body>
	<a href="/logout.php">Logout</a>
</body>

</html>