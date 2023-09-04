<?php
require_once "../config/config.php";
require_once "../classes/session.php";

$session = new Session();
$session->destroy();
$session->redirect("login.php");
?>