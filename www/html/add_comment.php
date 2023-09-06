<?php

require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/mail.php";

$session = new Session();
$session->require_auth();
$userId = $session->get("userId");

$response = ["success" => false, "msg" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pictureId"]) && isset($_POST["comment"])) {
	$session->check_csrf();
	$db = Database::getInstance();
	$conn = $db->getConnection();
	$pictureId = $_POST["pictureId"];
	$comment = $_POST["comment"];
	$sql = "INSERT INTO comments(user_id, picture_id, comment) VALUES(?, ?, ?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("iis", $userId, $pictureId, $comment);
	if ($stmt->execute()) {
		$response["success"] = true;
		$sql = "SELECT u.email AS mail, u.email_preference AS notificationPreference, p.id FROM users as u LEFT JOIN pictures AS p ON p.user_id = u.id WHERE p.id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $pictureId);
		$stmt->execute();
		$res = $stmt->get_result();
		$row = $res->fetch_assoc();
		if ($row["notificationPreference"]) {
			sendMail($row["mail"], "Notification", "Your picture has received a new comment");
		}
	}
}
header("Content-Type: application/json");
echo json_encode($response);
exit();
?>