<?php
require_once "../config/config.php";
require_once "../classes/session.php";

$session = new Session();
$session->require_auth();

$imgPath = "../uploads/" . $_GET["src"];

if (file_exists($imgPath)) {
	header('Content-type: image/jpeg');
	readfile($imgPath);
} else {
	header('HTTP/1.0 404 Not Found');
	echo "File not found";
}
?>