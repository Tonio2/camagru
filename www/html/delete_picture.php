<?php

require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();
$userId = $session->get("userId");

$response = ["success" => false, "msg" => "something went wrong"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"]) && isset($_POST["src"])) {
	$session->check_csrf();
	$db = Database::getInstance();
	$conn = $db->getConnection();
	$pictureId = $_POST["id"];
	$src = $_POST["src"];
	$sql = "DELETE FROM pictures WHERE id = ? AND user_id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ii", $pictureId, $userId);
	if ($stmt->execute()) {
		$path = "../uploads/" . $src;
		if (file_exists($path)) {
			unlink($path);
		}
		$response["success"] = true;
		$response["msg"] = "";
	}
}

header("Content-Type: application/json");
echo json_encode($response);
exit();
