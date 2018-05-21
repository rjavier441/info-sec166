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
$isAdmin = NULL;
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

// Acquire the user's admin status
$adminCheck = isAdmin($_SESSION["userid"]);
switch (gettype($adminCheck)) {
    case "boolean":
        $isAdmin = $adminCheck;
        break;
    default:
        $isAdmin = FALSE;   // default to false if the user's admin status cannot be determined
        break;
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
    case "update":
        // Ensure that the appropriate parameters were given
        if (!isset($data->title) || !isset($data->content) || !isset($data->postid)) {
            $res_body = array("nonce" => $client_nonce, "emsg" => "Your request was incomplete");
            $response = formatResponse("failure", $res_body);
        } else {
            $newContent = $data->content;
            $newTitle = $data->title;
            $postid = $data->postid;

            // Update and respond based on the result of the operation
            $update_result = updatePost($postid, $newTitle, $newContent);
            switch (gettype($update_result)) {
                case "boolean":
                    if ($update_result === FALSE) {
                        $res_body = array("nonce" => $client_nonce, "emsg" => "The post was not updated");
                        $response = formatResponse("failure", $res_body);
                    } else {
                        $res_body = array("nonce" => $client_nonce, "success" => TRUE, "result" => "Update successful");
                        $response = formatResponse("success", $res_body);
                    }
                    break;
                default:
                    $res_body = array("nonce" => $client_nonce, "emsg" => $update_result["emsg"]);
                    $response = formatResponse("failure", $res_body);
                    break;
            }
        }
        break;
    case "create":
        // Ensure that the appropriate parameters were given
        if (!isset($data->title) || !isset($data->content) || !isset($data->filename)) {
            $res_body = array("nonce" => $client_nonce, "emsg" => "Your request was incomplete");
            $response = formatResponse("failure", $res_body);
        } else {
            $newContent = $data->content;
            $newTitle = $data->title;
            $newFile = $data->filename;

            // Create a new post
            $creator_result = createPost($newTitle, $newContent, $_SESSION["userid"], $newFile);
            switch (gettype($creator_result)) {
                case "boolean":
                    if ($update_result === FALSE) {
                        $res_body = array("nonce" => $client_nonce, "emsg" => "The post was not created");
                        $response = formatResponse("failure", $res_body);
                    } else {
                        $res_body = array("nonce" => $client_nonce, "success" => TRUE, "result" => "Creation successful");
                        $response = formatResponse("success", $res_body);
                    }
                    break;
                default:
                    $res_body = array("nonce" => $client_nonce, "emsg" => $creator_result["emsg"]);
                    $response = formatResponse("failure", $res_body);
                    break;
            }
        }
        break;
    case "delete":
        // Ensure that the appropriate parameters were given
        if (!isset($data->postid)) {
            $res_body = array("nonce" => $client_nonce, "emsg" => "Your request was incomplete");
            $response = formatResponse("failure", $res_body);
        } else {
            $postidForDeletion = $data->postid;

            // Delete the post
            $deletion_result = deletePost($postidForDeletion, $_SESSION["userid"]);
            switch (gettype($deletion_result)) {
                case "boolean":
                    if ($deletion_result === FALSE) {
                        $res_body = array("nonce" => $client_nonce, "emsg" => "You are not authorized to delete this post");
                        $response = formatResponse("failure", $res_body);
                    } else {
                        $res_body = array("nonce" => $client_nonce, "success" => TRUE, "result" => "Deletion successful");
                        $response = formatResponse("success", $res_body);
                    }
                    break;
                default:
                    $res_body = array("nonce" => $client_nonce, "emsg" => $deletion_result["emsg"]);
                    $response = formatResponse("failure", $res_body);
                    break;
            }
        }
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
    global $_CREDENTIALS, $_SESSION, $isAdmin;
    $return_val = NULL;

    // Initialize MySQL database connection
    $db = mysqli_connect($_CREDENTIALS["db"]["host"], $_CREDENTIALS["db"]["user"], $_CREDENTIALS["db"]["pwd"], $_CREDENTIALS["db"]["name"]);

    // Ensure the connection was good
    $any_conn_err = mysqli_connect_errno();
    if ($any_conn_err) {
        $return_val = array("success" => FALSE, "emsg" => "Could not connect to database");
    } else {
        $query = "SELECT p.postid, u.userid, p.content, p.posttime, p.title, p.filename, u.username AS author FROM post AS p LEFT OUTER JOIN user AS u ON p.userid=u.userid WHERE p.$type LIKE ? ORDER BY p.posttime DESC LIMIT ?, ?";
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
                // Insert new key-value pairs to the $row object; these will allow the client-side UI to determine if the user can edit/delete this particular post
                $permissions = array("can_edit" => FALSE, "can_delete" => FALSE);
                $row = array_merge($row, $permissions);

                // Assign edit/delete permissions for this post
                if ($isAdmin === TRUE || $row["userid"] === $_SESSION["userid"]) {
                    $row["can_edit"] = TRUE;
                    $row["can_delete"] = TRUE;
                }
                array_push($stmt_result, $row);
            }
            $return_val = array("success" => TRUE, "result" => $stmt_result);
        }
        $stmt->close();
    }

    // Close MySQL database connection
    mysqli_close($db);
    return $return_val;
}

// @function    updatePost
// @parameter   pid - the postid of the post to update
// @parameter   title - the post's new title string
// @parameter   content - the post's new content string
// @returns     On successful update: TRUE
//              On failed update: FALSE
//              On other error: array("success" => FALSE, "emsg" => "some error message")
// @details     This function attempts to update the blog post specified by "pid" with the new title and content specified.
function updatePost ($pid, $title, $content) {
    global $_CREDENTIALS, $_SESSION;
    $return_val = NULL;

    // Initialize MySQL database connection
    $db = mysqli_connect($_CREDENTIALS["db"]["host"], $_CREDENTIALS["db"]["user"], $_CREDENTIALS["db"]["pwd"], $_CREDENTIALS["db"]["name"]);

    // Ensure the connection was good
    $any_conn_err = mysqli_connect_errno();
    if ($any_conn_err) {
        $return_val = array("success" => FALSE, "emsg" => "Could not connect to database");
    } else if (!hasEditPermission($pid, $_SESSION["userid"])) {
        $return_val = array("success" => FALSE, "emsg" => "Denied; You can only edit your own posts!");
    } else {
        $query = "UPDATE post SET title=?, content=? WHERE postid=?";
        $stmt = $db->stmt_init();

        // Prepare statement
        if (!$stmt->prepare($query)) {
            $return_val = array("success" => FALSE, "emsg" => "Could not prepare update(s)");
        } else {
            // Execute statement and compile results
            $stmt->bind_param("ssi", $title, $content, $pid);
            if (!$stmt->execute()) {
                $return_val = FALSE;
            } else {
                $return_val = TRUE;
            }
        }
        $stmt->close();
    }

    // Close MySQL database connection
    mysqli_close($db);
    return $return_val;
}

// @function    createPost
// @parameter   title - the title for this new post
// @parameter   content - the content for this new post
// @parameter   uid - the author's userid
// @parameter   filename - the filename of the image associated with the post (passing "" is valid if the post doesn't have an image)
// @returns     On successful creation: TRUE
//              On failed creation: FALSE
//              On other error: array("success" => FALSE, "emsg" => "some error message")
// @details     This function attempts to create a new post with the given title and content.
function createPost ($title, $content, $uid, $filename = "") {
    global $_CREDENTIALS;
    $return_val = NULL;

    // Initialize MySQL database connection
    $db = mysqli_connect($_CREDENTIALS["db"]["host"], $_CREDENTIALS["db"]["user"], $_CREDENTIALS["db"]["pwd"], $_CREDENTIALS["db"]["name"]);

    // Ensure the connection was good
    $any_conn_err = mysqli_connect_errno();
    if ($any_conn_err) {
        $return_val = array("success" => FALSE, "emsg" => "Could not connect to database");
    } else {
        $query = "INSERT INTO post (userid, title, filename, content) VALUES (?,?,?,?)";
        $stmt = $db->stmt_init();

        // Prepare statment
        if (!$stmt->prepare($query)) {
            $return_val = array("success" => FALSE, "emsg" => "Could not prepare statement(s)");
        } else {
            // Execute statement and check the operation's result
            $stmt->bind_param("isss", $uid, $title, $filename, $content);
            if (!$stmt->execute()) {
                $return_val = FALSE;
            } else {
                $return_val = TRUE;
            }
        }
        $stmt->close();
    }

    // Close MySQL database connection
    mysqli_close($db);
    return $return_val;
}

// @function    deletePost
// @parameter   pid - the postid of the post to delete
// @parameter   uid - the userid of the user requesting this deletion
// @returns     On successful processing, and deletion is authorized: TRUE
//              On successful processing, but deletion is unauthorized: FALSE
//              On failure: array("success" => FALSE, "emsg" => "some error message")
// @details     This function attempts to delete a post with the specified "pid", but only if the user is authorized to do so.
function deletePost ($pid, $uid) {
    global $_CREDENTIALS, $isAdmin;
    $return_val = NULL;

    // Check that this user has permission to edit/delete
    $canDelete = hasEditPermission($pid, $uid);

    // Initialize MySQL database connection
    $db = mysqli_connect($_CREDENTIALS["db"]["host"], $_CREDENTIALS["db"]["user"], $_CREDENTIALS["db"]["pwd"], $_CREDENTIALS["db"]["name"]);

    // Ensure the connection was good
    $any_conn_err = mysqli_connect_errno();
    if ($any_conn_err) {
        $return_val = array("success" => FALSE, "emsg" => "Could not connect to database");
    } else if (gettype($canDelete) !== "boolean") {
        $return_val = array("success" => FALSE, "emsg" => "Unable to verify delete permissions");
    } else if ($canDelete === FALSE && $isAdmin === FALSE) {
        $return_val = FALSE;
    } else {
        $postfilename_identified = FALSE;
        $postfilename = "";
        $postinfoquery = "SELECT filename FROM post WHERE postid = ?";
        $piq = $db->stmt_init();
        if (!$piq->prepare($postinfoquery)) {
            $return_val = array("success" => FALSE, "emsg" => "Unable to remove residual files");
        } else {
            $piq->bind_param("i", $pid);
            $piq->execute();
            $piq_res_obj = $piq->get_result();
            $piq_result = array();
            while ($row = $piq_res_obj->fetch_assoc()) {
                array_push($piq_result, $row);
            }

            if (count($piq_result) != 1) {
                $return_val = array("success" => FALSE, "emsg" => "Unable to identify residual files");
            } else {
                $postfilename_identified = TRUE;
                $postfilename = $piq_result[0]["filename"];
            }
        }
        $piq->close();

        if ($postfilename_identified) {
            // Delete the file with the filename, if it is not empty
            $postfilepath = "../upload/" . $postfilename;
            if ($postfilename !== "" && !unlink($postfilepath)) {
                $return_val = array("success" => FALSE, "emsg" => "Residual file '$postfilename' was not removed");
            } else {
                // Proceed to delete the post data, itself
                $query = "DELETE FROM post WHERE postid = ?";
                $stmt = $db->stmt_init();

                // Prepare statment
                if (!$stmt->prepare($query)) {
                    $return_val = array("success" => FALSE, "emsg" => "Could not verify edit permission");
                } else {
                    // Execute statement and compile results
                    $stmt->bind_param("i", $pid);
                    if (!$stmt->execute()) {
                        $return_val = array("success" => FALSE, "emsg" => "Could not delete post");
                    } else {
                        $return_val = TRUE;
                    }
                }
                $stmt->close();
            }
        }
    }

    // Close MySQL database connection
    mysqli_close($db);
    return $return_val;
}

// @function    hasEditPermisssion
// @parameter   pid - the postid of a post
// @parameter   uid - the userid of a user
// @returns     On success, and user can edit: true
//              On success, but user cannot edit: false
//              On failure: array("success" => FALSE, "emsg" => "some error message")
// @details     This function is useful for determining whether a user specified by "uid" is authorized to update/delete a post specified by "pid".
// @note        This function DOES NOT account for a user's admin status.
function hasEditPermission ($pid, $uid) {
    global $_CREDENTIALS;
    $return_val = NULL;

    // Initialize MySQL database connection
    $db = mysqli_connect($_CREDENTIALS["db"]["host"], $_CREDENTIALS["db"]["user"], $_CREDENTIALS["db"]["pwd"], $_CREDENTIALS["db"]["name"]);

    // Ensure the connection was good
    $any_conn_err = mysqli_connect_errno();
    if ($any_conn_err) {
        $return_val = array("success" => FALSE, "emsg" => "Could not connect to database");
    } else {
        $query = "SELECT * FROM post WHERE postid = ?";
        $stmt = $db->stmt_init();

        // Prepare statment
        if (!$stmt->prepare($query)) {
            $return_val = array("success" => FALSE, "emsg" => "Could not verify edit permission");
        } else {
            // Execute statement and compile results
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $stmt_result_obj = $stmt->get_result();
            $stmt_result = array();
            while ($row = $stmt_result_obj->fetch_assoc()) {
                array_push($stmt_result, $row);
            }

            // Check the post's userid, which will identify the post creator
            if ($stmt_result[0]["userid"] !== $uid) {
                $return_val = FALSE;
            } else {
                $return_val = TRUE;
            }
        }
        $stmt->close();
    }

    // Close MySQL database connection
    mysqli_close($db);
    return $return_val;
}

// @function    isAdmin
// @parameter   uid - the userid of user to check for admin status
// @returns     On success, and user has admin status: true
//              On success, but user is not an admin: false
//              On failure: associative array with members "success" => FALSE and "emsg" containing a message regarding the failure
// @details     This funciton checks the database if the user with the specified "uid" is indeed an admin.
function isAdmin ($uid) {
    global $_CREDENTIALS;
    $return_val = NULL;

    // Initialize MySQL database connection
    $db = mysqli_connect($_CREDENTIALS["db"]["host"], $_CREDENTIALS["db"]["user"], $_CREDENTIALS["db"]["pwd"], $_CREDENTIALS["db"]["name"]);

    // Ensure the connection was good
    $any_conn_err = mysqli_connect_errno();
    if ($any_conn_err) {
        $return_val = array("success" => FALSE, "emsg" => "Could not connect to database");
    } else {
        $query = "SELECT * FROM admin WHERE userid = ?";
        $stmt = $db->stmt_init();

        // Prepare statment
        if (!$stmt->prepare($query)) {
            $return_val = array("success" => FALSE, "emsg" => "Could not prepare search");
        } else {
            // Execute statement and compile results
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $stmt_result_obj = $stmt->get_result();
            $stmt_result = array();
            while ($row = $stmt_result_obj->fetch_assoc()) {
                array_push($stmt_result, $row);
            }

            // Check for admin status
            if (count($stmt_result) !== 1) {
                $return_val = FALSE;
            } else {
                $return_val = TRUE;
            }
        }
        $stmt->close();
    }

    // Close MySQL database connection
    mysqli_close($db);
    return $return_val;
}
// END Utility Functions
?>