<?php
session_start();

if (isset($_SESSION["logged_in"])) {
	unset($_SESSION["logged_in"]);
}

if (isset($_SESSION["uname"])) {
	unset($_SESSION["uname"]);
}

if (isset($_SESSION["userId"])) {
	unset($_SESSION["userId"]);
}

session_destroy();

header("Location: login.php");
?>