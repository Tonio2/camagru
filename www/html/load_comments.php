<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

$pictureId = isset($_GET['pictureId']) && is_numeric($_GET['pictureId']) ? intval($_GET['pictureId']) : 0;

$sql = "SELECT comments.comment AS comment,
				comments.created_at AS comment_date,
				users.username AS author
				FROM comments
				JOIN users ON comments.user_id = users.id
				WHERE comments.picture_id = ?
				ORDER BY comments.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pictureId);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);

?>