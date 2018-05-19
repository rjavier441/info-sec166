<?php
//  PROJECT:        InfoSec166
//  Name:           R. Javier
//  File:           utility.php
//  Date Created:   May 10, 2018
//  Last Modified:  May 10, 2018
//  Details:
//                  This file contains the logic for all login web transactions
//  Dependencies:
//                  PHP
//                  MySQL
//                  Apache Web Server
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

// Globals
$response = "";
$action = NULL;
$data = NULL;
$client_nonce = NULL;   // using timestamp as nonce 
$errors = array();

// Only allow https requests here
if ($_SERVER["HTTPS"] != "on") {
    $response = formatResponse("failure", array("emsg" => "Protocol HTTP is insecure and is not allowed"));
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
    case "search":
        $page_size = 10;    // default post result set size
        $page_num = 0;  // default post page number

        // Ensure that the appropriate parameters were given
        if (!isset($data->searchterm) || !isset($data->searchtype)) {
            $res_body = array("nonce" => $client_nonce, "emsg" => "Your request was incomplete");
            $response = formatResponse("failure", $res_body);
            break;
        }

        // Use user-defined search settings, if provided
        if (isset($data->pagesize) && gettype($data->pagesize) === "integer" && $data->pagesize > 0) {
            $page_size = $data->pagesize;
        }
        if (isset($data->pagenum) && gettype($data->pagenum) === "integer" && $data->pagenum >= 0) {
            $page_num = $data->pagenum;
        }

        // Ensure only valid search type are given
        $invalid_searchtype = FALSE;
        $validated_searchtype = "";
        switch ($data->searchtype) {
            case "title":
                $validated_searchtype = "title";
                break;
            case "content":
                $validated_searchtype = "content";
                break;
            default:
                $invalid_searchtype = TRUE;
                break;
        }
        if ($invalid_searchtype) {
            $res_body = array("nonce" => $client_nonce, "emsg" => "Invalid search type");
            $response = formatResponse("failure", $res_body);
            break;
        }

        // Execute search with the given parameters
        $posts = searchPosts($data->searchterm, $validated_searchtype, $page_size, $page_num);

        // Pass results to client
        $res_body = array("nonce" => $client_nonce, "posts" => $posts);
        $response = formatResponse("success", $res_body);
        break;
    default:
        $res_body = array("nonce" => $client_nonce, "emsg" => "Unrecognized action $action");
        $response = formatResponse("failure", $res_body);
        break;
}

// Send response to client
replyToClient($response);



// BEGIN Utility Functions
// @function    searchPosts
// @parameter   term - the string/int/bool search term to search for
// @parameter   type - the string search type that determines what attribute to use for the search
// @parameter   size - the int amount of results to have in the result set
// @parameter   page - the int page number of the page of size "size" to return
// @returns     On success: an associative array with member "result" containing the post query result rows, and member "success" = TRUE
//              On failure: an associative array with member "emsg" describing the error, and member "success" = FALSE
// @details     This function allows you to search for posts within the database and returns their data. Note that this function truncates the post's content field to reduce payload size. To acquire a specific post's content, use "getPost" instead
function searchPosts ($term, $type, $size, $page) {
    global $_CREDENTIALS;
    $return_val = NULL;

    // Initialize MySQL database connection
    $db = mysqli_connect($_CREDENTIALS["db"]["host"], $_CREDENTIALS["db"]["user"], $_CREDENTIALS["db"]["pwd"], $_CREDENTIALS["db"]["name"]);

    // Ensure the connection was good
    $any_conn_err = mysqli_connect_errno();
    if ($any_conn_err) {
        $return_val = array("success" => FALSE, "emsg" => "Could not connect to database");
    } else {
        $query = "SELECT * FROM post WHERE $type LIKE ? LIMIT ?, ?";
        $stmt = $db->stmt_init();

        // Prepare statment
        if (!$stmt->prepare($query)) {
            $return_val = array("success" => FALSE, "emsg" => "Could not prepare search");
        } else {
            // Execute statement and compile results
            $offset = $page * $size;
            $formatted_term = "%" . $term . "%";
            $stmt->bind_param("sii", $formatted_term, $offset, $size);
            $stmt->execute();
            $stmt_result_obj = $stmt->get_result();
            $stmt_result = array();
            while ($row = $stmt_result_obj->fetch_assoc()) {
                array_push($stmt_result, $row);
            }
            $stmt->close();
            $return_val = array("success" => TRUE, "result" => $stmt_result);
        }
    }

    // Close MySQL database connection
    mysqli_close($db);
    return $return_val;
}
// END Utility Functions
?>