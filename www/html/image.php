<?php
session_start();

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
	header('HTTP/1.0 403 Forbidden');
	echo 'You are not allowed to access this file.';
	exit;
}

$imgPath = "../uploads/" . $_GET["src"];

if (file_exists($imgPath)) {
	header('Content-type: image/jpeg');
	readfile($imgPath);
} else {
	header('HTTP/1.0 404 Not Found');
	echo "File not found";
}
?>