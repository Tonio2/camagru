<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/validate.php";

$session = new Session();
$session->require_auth();
$session->set_csrf();

$db = Database::getInstance();
$conn = $db->getConnection();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["picture"])) {
	$session->check_csrf();

	$file = $_FILES["picture"];
	$filename = $file["tmp_name"];
	$unique_filename = uniqid() . '.jpg';
	$targetPath = "../uploads/" . $unique_filename;

	$allowedExtensions = ['png', 'jpg', 'jpeg'];
	$fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);

	$allowedMimes = ["image/jpeg", "image/png"];
	$info = finfo_open(FILEINFO_MIME);
	$mime = finfo_file($info, $filename);
	finfo_close($info);
	$îsValidMime = false;
	foreach($allowedMimes as $allowedMime) {
		if (strpos($mime, $allowedMime) === 0) {
			$isValidMime = true;
			break;
		}
	}

	$maxFileSize = 1024 * 1024 * 5;
	$fileSize = $file["size"];

	if (!in_array($fileExtension, $allowedExtensions)) {
		$msg = "Extension not allowed";
	} elseif (!$isValidMime) {
		$msg = "MIME type not allowed : " . $mime;
	} elseif ($fileSize > $maxFileSize) {
		$msg = "File too large";
	} elseif (getimagesize($filename)) {
		$image = imagecreatefromstring(file_get_contents($filename));
		if (imagejpeg($image, $targetPath, 85)) {
			$sql = "INSERT INTO pictures(user_id, src) VALUES(?, ?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("is", $userId, $unique_filename);
			if ($stmt->execute()) {
				$msg = "File successfully uploaded";
			} else {
				$msg = "Something went wrong";
			}
		} else {
			$msg = "File is not an image";
		}
	} else {
		$msg = "File is not an image";
	}
}

$db->closeConnection();
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