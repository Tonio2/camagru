<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();
$session->set_csrf();

$db = Database::getInstance();
$conn = $db->getConnection();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$session->check_csrf();

	$uname = $_SESSION["uname"];

	$sql = "DELETE FROM users WHERE username = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $uname);
	if ($stmt->execute()) {
		$session->redirect("logout.php");
	} else {
		$msg = "Failed to delete account";
	}
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Delete account</title>
</head>

<body>
	<p>Are you sure ? This action is not reversible</p>
	<form method="post">
		<input type="hidden" name="csrfToken" value="<?php echo $_SESSION["csrfToken"]; ?>" />
		<input type="submit" value="Delete account" />
	</form>
	<?php if (!empty($msg)) : ?>
		<div class="error-message">
			<?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php endif; ?>

</body>

</html>