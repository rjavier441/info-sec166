<?php
require "lib/credentials.php";
require "utility.php";

// Enable use of session variables
session_start();

// Globals
$response = "";
$statuscode = NULL;
$action = NULL;
$data = NULL;
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
	$action = $_POST["action"];
	$data = $_POST["data"];
	$client_nonce = $data->timestamp + 1;
} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
	$action = $_GET["action"];
	$data = $_GET["data"];
	$client_nonce = $data->timestamp + 1;
} else {
	$method_used = $_SERVER["REQUEST_METHOD"];
	$response = formatResponse("failure", array("emsg" => "Method $method_used not allowed"));
	$statuscode = 500;
}

// Only proceed if the client token is correct
if ($data->token !== $_SESSION["token"]) {
	$response = formatResponse("failure", array("nonce" => $client_nonce, "emsg" => "Invalid token"));
	replyToClient($response, $statuscode);
	exit();
}

// Process the input
switch ($action) {
	case "getall":
		$res_body = array("nonce" => $client_nonce, "userinfo" => $_SESSION);
		$response = formatResponse("success", $res_body);
		$statuscode = 200;
		break;
	default:
		$res_body = array("nonce" => $client_nonce, "emsg" => "Unrecognized action $action");
		$response = formatResponse("failure", $res_body);
		$statuscode = 500;
		break;
}

// Send response to client
replyToClient($response, $statuscode);
?>