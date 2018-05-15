<?php
require "lib/credentials.php";
require "utility.php";

// Compatibility Check: AWS environment differs from my own, so I must refer to them differently
if (checkDebugMode($_IS_OPT)) {
	$subdir = "/info-sec166";
} else {
	$subdir = "";
}

// Enable use of session variables
session_start();

// Allow only certain uris to access this file's response
// header("Access-Control-Allow-Origin: https://localhost/");

// Only allow https requests here
if ($_SERVER["HTTPS"] != "on") {
	$response = formatResponse("failure","Protocol HTTP is insecure and is not allowed");
	replyToClient($response);
	exit();
}

// Globals
$response = "";
$statuscode = NULL;
$action = "";
$data = "";
$errors = array();

// Unset all session variables for this session
$_SESSION = array();

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

	// On success, repond based on the action
	try {
		switch ($action) {
			case "ping":
				$response = formatResponse("success", "ping");
				$statuscode = 200;
				break;
			case "secure-login":
				// Prepare statement and execute query
				$query = "SELECT * FROM user WHERE username=? AND password=?";
				$stmt = $db->stmt_init();
				if (!$stmt->prepare($query)) {
					$response = formatResponse("failure", "Statement(s) failed");
					$statuscode = 500;
				} else {
					$username = $data->username;
					$password = hash("sha256", $data->password);
					$client_nonce = $data->timestamp + 1;	// using timestamp as nonce 
					$stmt->bind_param("ss", $data->username, hash("sha256",$data->password));

					// Acquire results as an associative array
					// NOTE: Recall that PHP allows you to instantiate variables immediately, thus not needing initialization
					$stmt->execute();
					$result = $stmt->get_result();
					$resultset = array();
					while ($row = $result->fetch_assoc()) {
						array_push($resultset, $row);
					}
					$stmt->close();

					// Respond to client
					$arr_length = count($resultset);
					if ($arr_length != 1) {
						// If the user couldn't be determined, invalidate the response
						$res_body = array("emsg" => "Invalid Credentials");
						$response = formatResponse("failure",$res_body);
						$statuscode = 500;
					} else {
						// Otherwise, have the server remember the client data for 1 hr minimum., and have the client remember it for the same amount
						ini_set("session.gc_maxlifetime", 3600);
						session_set_cookie_params(3600);

						// Then, let's establish the session in three steps:
						// 1.) Generate a session token
						$token = hash("sha256", $username . $_SERVER["REQUEST_METHOD"] . $_SERVER["REQUEST_TIME"]);

						// 2.) Store session data in database
						$query = "INSERT INTO session (userid,token,login) VALUES (?,?,?)";
						$stmt2 = $db->stmt_init();
						if (!$stmt2->prepare($query)) {
							$response = formatResponse("failure", "Unable to establish session");
							$statuscode = 500;
						} else {
							// 3.) Send session token and redirect securely to client
							// $resultset[0]["userid"];
							$res_body = array(
								"token" => $token,
								"nonce" => $client_nonce,
								"redirect" => "https://" . $_SERVER["HTTP_HOST"] . "$subdir/home.php"
							);
							$_SESSION["token"] = $token;
							$_SESSION["username"] = $username;
							$response = formatResponse("success", $res_body);
							$statuscode = 200;
						}
					}
				}
				break;
			case "insecure-login":
				$response = formatResponse("success", "Hah! You thought I'd let you do that?");
				$statuscode = 200;
				break;
			default:
				$response = formatResponse("failure", "Invalid action " . $action);
				$statuscode = 403;
				break;
		}
	} catch (Exception $e) {
		$response = formatResponse("failure", "Internal Server Error");
	}
}

replyToClient($response, $statuscode);
mysqli_close($db);
?>
