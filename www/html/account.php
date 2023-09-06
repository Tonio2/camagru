<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";
require_once "../utils/validate.php";

$session = new Session();
$session->require_auth();

$db = Database::getInstance();
$conn = $db->getConnection();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $session->get("userId");
    
    $updateFields = [];
    $updateValues = [];
    $types = "";
    
    if (!empty($_POST["newUsername"])) {
        $updateFields[] = "username = ?";
        $updateValues[] = sanitizeInput($_POST["newUsername"]);
        $types .= "s";
    }
    
    if (!empty($_POST["newEmail"])) {
        $updateFields[] = "email = ?";
        $updateValues[] = sanitizeInput($_POST["newEmail"]);
        $types .= "s";
    }
    
    if (!empty($_POST["newPassword"])) {
        $updateFields[] = "password = ?";
        $hashedPassword = password_hash($_POST["newPassword"], PASSWORD_DEFAULT);
        $updateValues[] = $hashedPassword;
        $types .= "s";
    }
    
    $updateFields[] = "email_preference = ?";
    $emailPreference = $_POST["emailPreference"];
    $updateValues[] = $emailPreference;
    $types .= "i";
    
    $updateValues[] = $userId; // for the WHERE condition
    $types .= "i";
    
    if (!empty($updateFields)) {
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$updateValues);
        
        if ($stmt->execute()) {
            $msg = "Account updated successfully";
        } else {
            $msg = "Failed to update account";
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
    <title>Account Settings</title>
</head>
<body>
<header class="bg-light">
		<div class="container">
			<nav class="navbar navbar-expand-lg navbar-light">
				<a class="navbar-brand" href="/index.php">Home</a>
				<div class="navbar-nav">
					<a class="nav-item nav-link" href="/upload.php">Upload</a>
					<a class="nav-item nav-link active" href="/account.php">Account</a>
					<a class="nav-item nav-link" href="/logout.php">Logout</a>
				</div>
			</nav>
		</div>
	</header>
    <main class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Account Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="newUsername" class="form-label">New Username</label>
                                <input type="text" class="form-control" id="newUsername" name="newUsername">
                            </div>
                            <div class="mb-3">
                                <label for="newEmail" class="form-label">New Email</label>
                                <input type="email" class="form-control" id="newEmail" name="newEmail">
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword">
                            </div>
                            <div class="mb-3">
                                <label for="emailPreference">Email Notification</label>
                                <select class="form-select" id="emailPreference" name="emailPreference">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <input type="submit" class="btn btn-primary" value="Update">
                            </div>
                        </form>
                    </div>
                </div>
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-info mt-3">
                        <?php echo htmlentities($msg, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer class="bg-light mt-5">
        <div class="container py-3">
            <p class="text-center mb-0">Copyright &copy; Antoine 2023, Camagru</p>
        </div>
    </footer>
</body>
</html>