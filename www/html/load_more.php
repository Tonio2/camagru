<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

$items_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

$sql = "SELECT pictures.id AS picture_id,
				pictures.src AS picture_src,
				COUNT(DISTINCT likes.user_id) AS likes_count,
				users.username AS username,
				pictures.created_at AS created_at
				FROM pictures
				LEFT JOIN likes ON pictures.id = likes.picture_id
				JOIN users ON pictures.user_id = users.id
        GROUP BY pictures.id
        ORDER BY pictures.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>