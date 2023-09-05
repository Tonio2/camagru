<?php

require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/validate.php";

function isValidFileExtension($fileExtension, $allowedExtensions)
{
	return in_array($fileExtension, $allowedExtensions);
}

function isValidMime($filename, $allowedMimes)
{
	$info = finfo_open(FILEINFO_MIME);
	$mime = finfo_file($info, $filename);
	finfo_close($info);
	return in_array(explode(";", $mime)[0], $allowedMimes);
}

function isUnderSizeLimit($fileSize, $maxFileSize)
{
	return $fileSize <= $maxFileSize;
}

function superposeImages($filename, $alphaImageSrc)
{
	$baseImage = imagecreatefromstring(file_get_contents($filename));
	$alphaImage = imagecreatefrompng($alphaImageSrc);
	if (!$baseImage || !$alphaImage) {
		return false;
	}

	list($width, $height) = getimagesize($filename);
	list($alpha_width, $alpha_height) = getimagesize($alphaImageSrc);
	
	$square_dimension = max($width, $height);
	$squareImage = imagecreatetruecolor($square_dimension, $square_dimension);

	// Set background to transparent
	imagealphablending($squareImage, false);
	imagesavealpha($squareImage, true);
	$transparent = imagecolorallocatealpha($squareImage, 0, 0, 0, 127);
	imagefill($squareImage, 0, 0, $transparent);

	// Calculate offsets to center the uploaded image on square canvas
	$offsetX = ($square_dimension - $width) / 2;
	$offsetY = ($square_dimension - $height) / 2;

	// Copy uploaded image onto square canvas
	imagecopy($squareImage, $baseImage, $offsetX, $offsetY, 0, 0, $width, $height);

	// Prepare alpha image
	$alpha_image_resized = imagecreatetruecolor($square_dimension, $square_dimension);
	imagealphablending($alpha_image_resized, false);
	imagesavealpha($alpha_image_resized, true);
	imagecopyresampled($alpha_image_resized, $alphaImage, 0, 0, 0, 0, $square_dimension, $square_dimension, $alpha_width, $alpha_height);

	// Merge the images pixel by pixel to respect alpha channel
	for ($x = 0; $x < $square_dimension; $x++) {
		for ($y = 0; $y < $square_dimension; $y++) {
			$alpha_pixel = imagecolorat($alpha_image_resized, $x, $y);
			$alpha = ($alpha_pixel >> 24) & 0xFF;
			if ($alpha < 127) { // check if the pixel is transparent
				imagesetpixel($squareImage, $x, $y, $alpha_pixel);
			}
		}
	}
	return $squareImage;
}

$session = new Session();
$session->require_auth();
$userId = $session->get("userId");

$response = ["success" => false, "msg" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["picture"]) && isset($_POST["secondImage"])) {
	$session->check_csrf();
	$db = Database::getInstance();
	$conn = $db->getConnection();
	$file = $_FILES["picture"];
	$filename = $file["tmp_name"];
	$unique_filename = uniqid() . '.jpg';
	$targetPath = "../uploads/" . $unique_filename;

	$allowedExtensions = ['png', 'jpg', 'jpeg'];
	$allowedMimes = ["image/jpeg", "image/png"];
	$maxFileSize = 1024 * 1024 * 5;
	$fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);
	$fileSize = $file["size"];

	if (!isValidFileExtension($fileExtension, $allowedExtensions)) {
		$response["msg"] = "Extension not allowed";
	} elseif (!isValidMime($filename, $allowedMimes)) {
		$response["msg"] = "MIME type not allowed : " . $mime;
	} elseif (!isUnderSizeLimit($fileSize, $maxFileSize)) {
		$response["msg"] = "File too large";
	} elseif (getimagesize($filename)) {
		$alphaImageIdx = $_POST["secondImage"];
		$alphaImageSrc = "image" . $alphaImageIdx . ".png";
		$resultImage = superposeImages($filename, $alphaImageSrc);

		if ($resultImage && imagejpeg($resultImage, $targetPath, 85)) {
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
?>