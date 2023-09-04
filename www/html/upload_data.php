<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/validate.php";

$session = new Session();
$session->require_auth();
$userId = $session->get("userId");
$response = ["success" => false, "msg" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["picture"])) {
	$session->check_csrf();
	$db = Database::getInstance();
	$conn = $db->getConnection();

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
	foreach ($allowedMimes as $allowedMime) {
		if (strpos($mime, $allowedMime) === 0) {
			$isValidMime = true;
			break;
		}
	}

	$maxFileSize = 1024 * 1024 * 5;
	$fileSize = $file["size"];

	if (!in_array($fileExtension, $allowedExtensions)) {
		$response["msg"] = "Extension not allowed";
	} elseif (!$isValidMime) {
		$response["msg"] = "MIME type not allowed : " . $mime;
	} elseif ($fileSize > $maxFileSize) {
		$response["msg"] = "File too large";
	} elseif (getimagesize($filename)) {
		$image = imagecreatefromstring(file_get_contents($filename));
		if (imagejpeg($image, $targetPath, 85)) {
			$sql = "INSERT INTO pictures(user_id, src) VALUES(?, ?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("is", $userId, $unique_filename);
			if ($stmt->execute()) {
				$response["success"] = true;
				$response["msg"] = "File successfully uploaded";
			} else {
				$response["msg"] = "Something went wrong";
			}
		} else {
			$response["msg"] = "File is not an image";
		}
	} else {
		$response["msg"] = "File is not an image";
	}
	$db->closeConnection();
} else {
	$response["msg"] = "Invalid request";
}

header("Content-Type: application/json");
echo json_encode($response);
exit();
