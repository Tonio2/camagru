<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

$greeting = "Hello, " . $_SESSION["uname"];

$sql = "SELECT src FROM pictures ORDER BY created_at";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>
	<title>HOME</title>
</head>

<body>
	<p><?php echo htmlentities($greeting, ENT_QUOTES, 'UTF-8'); ?></p>
	<a href="/logout.php">Logout</a>
	<a href="/delete.php">Delete account</a>
	<a href="/upload.php">Upload image</a>
	<h2>List of uploaded images</h2>
	<?php
	while($row = $res->fetch_assoc()) {
		echo "<img src='image.php?src=" . $row["src"] . "' alt='picture' />";
	}
	?>
</body>

</html>