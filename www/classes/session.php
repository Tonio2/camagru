<?php

class Session
{
	// Constructor to start the session
	public function __construct()
	{
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
	}

	// Regenerate session ID for security
	public function regenerate()
	{
		session_regenerate_id(true);
	}

	// Set session variables
	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	// Get session variables
	public function get($key)
	{
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		return null;
	}

	// Check if a session variable is set
	public function has($key)
	{
		return isset($_SESSION[$key]);
	}

	// Remove a session variable
	public function remove($key)
	{
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}

	// Destroy the session
	public function destroy()
	{
		session_destroy();
		$_SESSION = [];
	}

	public function redirect($page)
	{
		header("Location: " . $page);
		exit();
	}

	public function require_not_auth()
	{
		if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]) {
			$this->redirect("index.php");
		}
	}

	public function require_auth()
	{
		if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
			$this->redirect("login.php");
		}
	}
}
