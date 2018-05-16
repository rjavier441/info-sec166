<?php
//  PROJECT:        InfoSec166
//  Name:           R. Javier
//  File:           utility.php
//  Date Created:   May 15, 2018
//  Last Modified:  May 15, 2018
//  Details:
//                  This file contains all new user registration logic
//  Dependencies:
//                  PHP
//                  MySQL
//                  Apache Web Server
require "lib/credentials.php";
require "utility.php";

// Enable use of session variables
session_start();

// Only allow https requests here
if ($_SERVER["HTTPS"] != "on") {
	$response = formatResponse("failure","Protocol HTTP is insecure and is not allowed");
	replyToClient($response);
	exit();
}

// Globals
$response = "";
$errors = array();

// Initialize MySQL database connection
$db = mysqli_connect($_CREDENTIALS["db"]["host"], $_CREDENTIALS["db"]["user"], $_CREDENTIALS["db"]["pwd"], $_CREDENTIALS["db"]["name"]);

$any_conn_err = mysqli_connect_errno();
if ($any_conn_err) {
	// Check if the database connection succeeded
	$response = formatResponse("failure", $any_conn_err);
} else if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	// Ensure that only post requests operated on this file
	$method_used = $_SERVER["REQUEST_METHOD"];
	$response = formatResponse("failure", "Method $method_used not allowed!");
} else {
	if (empty($_POST)) {
		$_POST = tryPostRestore();
	}
	$action = $_POST['action'];
	$data = $_POST['data'];
	$client_nonce = $data->timestamp + 1;

	// On success, perform the requested action
	try {
		switch ($action) {
			case "register":
				if (!containsAllRegistrationFields($data)) {
					$res_body = array("nonce" => $client_nonce, "emsg" => "Incomplete request");
					$response = formatResponse("failure", $res_body);
				} else {
					// Prepare a SQL statement
					$query = "INSERT INTO user (username,birthdate,password,bio) VALUES (?,?,?,'No bio, yet...')";
					$stmt = $db->stmt_init();
					if (!$stmt->prepare($query)) {
						$res_body = array("nonce" => $client_nonce, "emsg" => "Statement(s) failed");
						$response = formatResponse("failure", $res_body);
					} else {
						// Acquire new user credentials
						$username = $data->username;
						$password = hash("sha256", $data->password);

						// Format a SQL-parsable datetime string
						$bd_year = $data->birthyear;
						$bd_mon = zeroPad($data->birthmonth);
						$bd_mday = zeroPad($data->birthday);
						$fake_bd_time = "00:00:00";
						$bd_full = $bd_year . "-" . $bd_mon . "-" . $bd_mday . " " . $fake_bd_time;

						// Bind parameters and execute statement
						$stmt->bind_param("sss", $username, $bd_full, $password);
						if (!$stmt->execute()) {
							$res_body = array("nonce" => $client_nonce, "emsg" => "Failed to register new user");
							$response = formatResponse("failure", $res_body);
						} else {
							// $res_body = array("nonce" => $client_nonce, "bdStr" => "$bd_full", "uname" => $data->username, "pwd" => $data->password, "echo" => $data);	// debug
							$res_body = array("nonce" => $client_nonce, "msg" => "You have been successfully registered!");
							$response = formatResponse("success", $res_body);
						}
					}
				}
				break;
			default:
				$res_body = array("nonce" => $client_nonce, "emsg" => "Unrecognized action $action");
				$response = formatResponse("failure", $res_body);
				break;
		}
	} catch (Exception $e) {
		$res_body = array("nonce" => $client_nonce, "emsg" => "Internal Server Error");
		$response = formatResponse("failure", $res_body);
	}
}

replyToClient($response);
mysqli_close($db);



// BEGIN Utility Functions
function containsAllRegistrationFields ($obj) {
	return isset($obj->username) && isset($obj->password) && isset($obj->birthyear) && isset($obj->birthday) && isset($obj->birthmonth) && isset($obj->timestamp);
}

function zeroPad ($num) {
	// If num is less than 10, it pads it with an extra zero
	if ($num < 10) {
		$num = "0" . $num;
	}
	return $num;
}
// END Utility Functions
?>