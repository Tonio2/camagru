<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();
$csrfToken = $session->set_csrf();

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
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
	<title>Delete account</title>
</head>

<body>
	<header class="bg-light">
		<div class="container">
			<nav class="navbar navbar-expand-lg navbar-light">
				<a class="navbar-brand" href="/index.php">Home</a>
				<div class="navbar-nav">
					<a class="nav-item nav-link active" href="/upload.php">Upload</a>
					<a class="nav-item nav-link" href="/account.php">Account</a>
					<a class="nav-item nav-link" href="/logout.php">Logout</a>
				</div>
			</nav>
		</div>
	</header>
	<main class="container mt-5">
		<p>Are you sure ? This action is not reversible</p>
		<form method="post">
			<input type="hidden" name="csrfToken" value="<?php echo $csrfToken; ?>" />
			<input type="submit" value="Delete account" />
		</form>
		<?php if (!empty($msg)) : ?>
			<div class="error-message">
				<?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
			</div>
		<?php endif; ?>
	</main>

	<footer class="bg-light mt-5">
		<div class="container py-3">
			<p class="text-center mb-0">Copyright &copy;Antoine 2023, Camagru</p>
		</div>
	</footer>

</body>

</html>