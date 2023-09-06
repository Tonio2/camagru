<?php

function mail($to, $obj, $msg) {
	$headers = "From: labalette.antoine@gmail.com" . "\r\n" .
    "Reply-To: labalette.antoine@gmail.com" . "\r\n" .
    "X-Mailer: PHP/" . phpversion();
	return mail($to, $obj, $msg, $headers);
}

?>