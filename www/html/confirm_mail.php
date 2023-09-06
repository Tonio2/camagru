<?php
require_once "../config/config.php";
require_once "../classes/database.php";

$db = Database::getInstance();
$conn = $db->getConnection();

if (isset($_GET["code"])) {
	$code = $_GET["code"];

	$code = $conn->real_escape_string($code);

	$sql = "SELECT * FROM users WHERE email_validation_code = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $code);
	$stmt->execute();
	$res = $stmt->get_result();

	if ($res->num_rows > 0) {
		$sql = "UPDATE users SET email_validated = 1 WHERE email_validation_code = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $code);
		if ($stmt->execute()) {
			echo "Your email is confirmed. You can now login";
		} else {
			echo "Something went wrong";
		}
	} else {
		echo "Something went wrong";
	}
} else {
	echo "Something went wrong";
}

?>


