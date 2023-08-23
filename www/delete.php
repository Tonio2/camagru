<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

if (!isset($_SESSION["csrfToken"])) {
	$_SESSION["csrfToken"] = bin2hex(random_bytes(32));
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (!isset($_POST["csrfToken"]) || $_POST["csrfToken"] != $_SESSION["csrfToken"]) {
		die("CSRF attack");
	}

	$uname = $_SESSION["uname"];

	$sql = "DELETE FROM users WHERE username = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $uname);
	if ($stmt->execute()) {
		header("Location: /logout.php");
	} else {
		$msg = "Failed to delete account" . $stmt->error;;
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
		<input type="hidden" name="csrfToken" value="<?php echo $_SESSION["csrfToken"]; ?>" />
		<input type="submit" value="Delete account" />
	</form>
	<?php if (!empty($msg)) : ?>
		<div class="error-message">
			<?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php endif; ?>

</body>

</html>