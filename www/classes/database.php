<?php

class Database
{
	private static $instance = null;
	private $conn = null;
	
	private function __construct()
	{
		$host = "db";
		$db = $_ENV["MYSQL_DATABASE"];
		$user = $_ENV["MYSQL_USER"];
		$password = $_ENV["MYSQL_PASSWORD"];
		$this->conn = new mysqli($host, $user, $password, $db);
		if ($this->conn->connect_error) {
			throw new Error("Database error: " . $this->conn->connect_error);
		}
	}

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new Database();
		}
		return self::$instance;
	}

	public function getConnection()
	{
		return $this->conn;
	}

	public function closeConnection()
	{
		if ($this->conn !== null) {
			$this->conn->close();
		}
	}
}
