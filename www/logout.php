<?php
session_start();

if (isset($_SESSION["logged_in"])) {
	unset($_SESSION["logged_in"]);
}

if (isset($_SESSION["uname"])) {
	unset($_SESSION["uname"]);
}

session_destroy();

header("Location: login.php");
?>