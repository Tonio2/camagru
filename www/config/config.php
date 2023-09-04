<?php
ini_set('log_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_log', '../file.log');
error_reporting(E_ALL);

function myExceptionHandler($exception)
{
	$errorDetail = "Uncaught Exception: " . $exception;
	error_log($errorDetail);
	echo 'Something went wrong. Please try again later.<br />';
}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	$errorDetail = "Error [$errno]: $errstr in $errfile on line $errline";
	error_log($errorDetail);
}

set_exception_handler('myExceptionHandler');
set_error_handler("myErrorHandler");
?>