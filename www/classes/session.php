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
		$logged_in = $this->get("logged_in");
		if ($logged_in) {
			$this->redirect("index.php");
		}
	}

	public function require_auth()
	{
		$logged_in = $this->get("logged_in");
		if (!$logged_in) {
			$this->redirect("login.php");
		}
	}

	public function set_csrf() {
		if (!$this->has("csrfToken")) {
			$token = bin2hex(random_bytes(32));
			$this->set("csrfToken", $token);
			return $token;
		} else {
			return $this->get("csrfToken");
		}
	}

	public function check_csrf() {
		$csrfToken = $this->get("csrfToken");
		if (!$csrfToken || $_POST["csrfToken"] != $csrfToken) {
			throw new Error("CSRF attack");
		}
	}
}

?>