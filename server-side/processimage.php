<?php
//  PROJECT:        InfoSec166
//  Name:           R. Javier
//  File:           processimage.php
//  Date Created:   May 10, 2018
//  Last Modified:  May 10, 2018
//  Details:
//                  This file contains logic to process an image upload
//  Dependencies:
//                  PHP
//                  MySQL
//                  Apache Web Server
require "lib/credentials.php";
require "utility.php";

// Enable use of session variables
session_start();

// Globals
$response = "";
$client_nonce = NULL;	// using timestamp as nonce 
$errors = array();

// Only allow https requests here
if ($_SERVER["HTTPS"] != "on") {
	$response = formatResponse("failure","Protocol HTTP is insecure and is not allowed");
	replyToClient($response);
	exit();
}

// Acquire parameters
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	// Check if post is empty (i.e. AngularJS doesn't correctly place post data), and attempt to acquire data
	if (empty($_POST)) {
		$_POST = tryPostRestore();
	}

	// BEGIN Handle File Upload
	$upload_dir = "/var/www/html/info-sec166/upload/";
	$uploadfile = $upload_dir . basename($_FILES["file"]["name"]);
	$uploadfile_size = $_FILES["file"]["size"];
	echo '<pre>';

	// Validate file size
	$file_valid = TRUE;
	if ($uploadfile_size < 100 || $uploadfile_size > 2000000) {
		$file_valid = FALSE;
	}

	// Validate file type
	switch ($_FILES["file"]["type"]) {
		case "image/jpeg":
			// acceptable file type; do nothing
			break;
		case "image/png":
			// acceptable file type; do nothing
			break;
		default:
			$file_valid = FALSE;
			break;
	}

	// Complete Validation and save file if valid
	if ($file_valid) {
		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
		    echo "File is valid, and was successfully uploaded.\n";
		} else {
		    echo "Possible file upload attack!\n";
		}
	} else {
		echo "Your file is unacceptable\n";
	}

	// echo 'Here is some more debugging info:';
	// print_r($_FILES);

	print "</pre>";
	// replyToClient($response);
	exit();
} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
	$action = $_GET["action"];
	$data = $_GET["data"];
	$client_nonce = $data->timestamp + 1;
} else {
	$method_used = $_SERVER["REQUEST_METHOD"];
	$response = formatResponse("failure", array("emsg" => "Method $method_used not allowed"));
	// $statuscode = 500;
}

// Only proceed if the client token is correct
if ($data->token !== $_SESSION["token"]) {
	$response = formatResponse("failure", array("nonce" => $client_nonce, "emsg" => "Invalid token"));
	replyToClient($response, $statuscode);
	exit();
}

// Process the input
switch ($action) {
	default:
		$res_body = array("nonce" => $client_nonce, "emsg" => "The request cannot be performed");
		$response = formatResponse("failure", $res_body);
		// $statuscode = 500;
		break;
}

// Send response to client
replyToClient($response, $statuscode);
?>