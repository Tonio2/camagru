<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/validate.php";
require_once "../utils/mail.php";

$session = new Session();
$session->require_not_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$msg = "Bad Request";
	$errors = [];
	$uname = sanitizeInput($_POST["username"]); // sanitize to avoid xss attack
	$email = sanitizeInput($_POST["email"]); // sanitize to avoid xss attack
	$pwd = $_POST["password"]; // no need to sanitize it because it will be hashed
	$errors = validatePassword($pwd);
	if (validateUsername($uname) && validateEmail($email) && empty($errors)) {
		$hash = password_hash($pwd, PASSWORD_DEFAULT);
		$emailValidationCode = bin2hex(random_bytes(32));
		$sql = "INSERT INTO users(username, email, password, email_validation_code) values(?, ?, ?, ?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ssss", $uname, $email, $hash, $emailValidationCode);
		if ($stmt->execute()) {
			sendConfirmationMail($email, $emailValidationCode);
			$msg = "Registration successfull. Please check your mails to confirm your email address.";
		}
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
	<title>Register</title>
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
						<form action="register.php" method="post">
							<div class="mb-3">
								<label for="username" class="form-label">Username</label>
								<input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
							</div>
							<div class="mb-3">
								<label for="email" class="form-label">Email</label>
								<input type="email" class="form-control" id="email" name="email" placeholder="Enter email">
							</div>
							<div class="mb-3">
								<label for="password" class="form-label">Password</label>
								<input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
							</div>
							<div class="mb-3">
								<input type="submit" class="btn btn-primary" value="Register">
							</div>
						</form>
						<a href="/login.php" class="card-link">Login</a>
					</div>
				</div>
				<?php if (!empty($msg)) : ?>
					<div class="alert alert-danger mt-3">
						<?php
						echo htmlentities($msg, ENT_QUOTES, 'UTF-8') . "<br />";
						if (!validateUsername($uname)) {
							echo "Username must be at least 4 charachters long<br />";
						}
						if (!$email) {
							echo "Email is invalid<br />";
						}
						if (!empty($errors)) {
							echo "Password is weak for the following reasons:";
							foreach ($errors as $error) {
								echo "<br />- " . htmlentities($error, ENT_QUOTES, 'UTF-8');
							}
						}
						?>
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