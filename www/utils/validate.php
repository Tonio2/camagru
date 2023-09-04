<?php

function sanitizeInput($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

function validateUsername($username)
{
	// Here you can add more username validation logic
	return isset($username) && strlen($username) > 4;
}

function validateEmail($email)
{
	// Here you can add more email validation logic
	return isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password)
{
	$errors = [];

	if (!isset($password)) {
		$errors[] = "A password must be provided.";
	}

	// Check length
	if (strlen($password) < 8) {
		$errors[] = "Password must be at least 8 characters long.";
	}

	// Check for at least one lowercase letter
	if (!preg_match('/[a-z]/', $password)) {
		$errors[] = "Password must contain at least one lowercase letter.";
	}

	// Check for at least one uppercase letter
	if (!preg_match('/[A-Z]/', $password)) {
		$errors[] = "Password must contain at least one uppercase letter.";
	}

	// Check for at least one digit
	if (!preg_match('/[0-9]/', $password)) {
		$errors[] = "Password must contain at least one number.";
	}

	// Check for at least one special character
	if (!preg_match('/[\W_]/', $password)) {
		$errors[] = "Password must contain at least one special character.";
	}

	return $errors;
}

?>