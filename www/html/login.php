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
			if ($row["email_validated"] == 1) {
				$session->regenerate();
				$session->set("logged_in", true);
				$session->set("uname", $row["username"]);
				$session->set("userId", $row["id"]);
				$session->redirect("index.php");
			} else {
				$msg = "You need to confirm your email";
			}
		} else {
			$msg = 'invalid credentials';
		}
	} else {
		$msg = 'invalid credentials';
	}
}

$db->closeConnection();
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
	<title>Login</title>
</head>

<body>
	<header class="bg-light">
		<div class="container">
			<nav class="navbar navbar-expand-lg navbar-light">
				<a class="navbar-brand" href="/index.php">Home</a>
			</nav>
		</div>
	</header>
	<main class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Login</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
                            </div>
                            <div class="mb-3">
                                <input type="submit" class="btn btn-primary" value="Login">
                            </div>
                        </form>
                        <a href="/register.php" class="card-link">Create an account</a>
                        <a href="forgot_password.php" class="card-link">Forgot password?</a>
                    </div>
                </div>
                <?php if (!empty($msg)) : ?>
                    <div class="alert alert-danger mt-3">
                        <?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

	<footer class="bg-light mt-5">
		<div class="container py-3">
			<p class="text-center mb-0">Copyright &copy;Antoine 2023, Camagru</p>
		</div>
	</footer>
</body>

</html>