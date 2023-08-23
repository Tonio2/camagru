<?php
session_start();

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
	header("Location: login.php");
	exit();
}

$userId = $_SESSION["userId"];

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
echo "hello";
print_r($_FILES);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["picture"])) {
	if (!isset($_POST["csrfToken"]) || $_POST["csrfToken"] != $_SESSION["csrfToken"]) {
		die("CSRF attack");
	}

	$file = $_FILES["picture"];
	print_r($file);
	$filename = $file["tmp_name"];
	$targetPath = "uploads/" . basename($_FILES["picture"]["name"]);

	print($filename);
	print_r($targetPath);

	if (getimagesize($filename)) {
		if (move_uploaded_file($filename, $targetPath)) {
			$sql = "INSERT INTO pictures(user_id, src) VALUES(?, ?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("is", $userId, $targetPath);
			if ($stmt->execute()) {
				$msg = "File successfully uploaded";
			} else {
				$msg = "Failed to save picture information";
			}
		} else {
			$msg = "Failed to upload picture";
		}
	} else {
		$msg = "File is not an image";
	}
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
	<title>Upload picture</title>
</head>

<body>
	<form method="POST" enctype="multipart/form-data">
		<input type="hidden" name="csrfToken" value="<?php echo $_SESSION["csrfToken"]; ?>" />
		<input type="file" name="picture" />
		<input type="submit" value="upload" />
	</form>
	<?php if (!empty($msg)) : ?>
		<div class="error-message">
			<?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php endif; ?>
</body>

</html>