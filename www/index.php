<?php
session_start();

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
	header("Location: login.php");
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

$greeting = "Hello, " . $_SESSION["uname"];

$sql = "SELECT src FROM pictures ORDER BY created_at";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>
	<title>HOME</title>
</head>

<body>
	<p><?php echo htmlentities($greeting, ENT_QUOTES, 'UTF-8'); ?></p>
	<a href="/logout.php">Logout</a>
	<a href="/delete.php">Delete account</a>
	<a href="/upload.php">Upload image</a>
	<h2>List of uploaded images</h2>
	<?php
	while($row = $res->fetch_assoc()) {
		echo "<img src='" . $row["src"] . "' alt='picture' />";
	}
	?>
</body>

</html>