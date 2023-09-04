<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/validate.php";

$session = new Session();
$session->require_not_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

$msg = "";

//TODO: sanitize user's input
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$uname = sanitizeInput($_POST["username"]);
	$pwd = $_POST["password"]; // no need to sanitize it because it is hashed

	$sql = "SELECT * FROM users WHERE username = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $uname);
	$stmt->execute();
	$res = $stmt->get_result();

	if ($res->num_rows > 0) {
		$row = $res->fetch_assoc();
		$hash = $row["password"];
		if (password_verify($pwd, $hash)) {
			$session->regenerate();
			$session->set("logged_in", true);
			$session->set("uname", $row["username"]);
			$session->set("userId", $row["id"]);
			$session->redirect("index.php");
		}
	}
	
	$msg = 'invalid credentials';
}

$db->closeConnection();
?>

<!DOCTYPE html>
<html>

<head>
	<title>Login</title>
</head>

<body>
	<form method="POST">
		<input type="text" name="username" />
		<input type="password" name="password" />
		<input type="submit" value="login" />
	</form>
	<a href="/register.php">Create an account</a>
	<?php if (!empty($msg)) : ?>
		<div class="error-message">
			<?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php endif; ?>
</body>

</html>