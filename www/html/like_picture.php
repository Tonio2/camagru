<?php

require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();
$userId = $session->get("userId");

$response = ["success" => false, "msg" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pictureId"])) {
	$session->check_csrf();
	$db = Database::getInstance();
	$conn = $db->getConnection();
	$pictureId = $_POST["pictureId"];
	$sql = "INSERT INTO likes(user_id, picture_id) VALUES(?, ?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ii", $userId, $pictureId);
	if ($stmt->execute()) {
		$response["success"] = true;
	} else {
		$response["msg"] = $stmt->error;
	}
}
header("Content-Type: application/json");
echo json_encode($response);
exit();
?>
