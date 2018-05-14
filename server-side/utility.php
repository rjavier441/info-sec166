<?php
	//  PROJECT:        BeerBuddy
    //  Name:           Rolando Javier
    //  File:           utility.php
    //  Date Created:   April 26, 2018
    //  Last Modified:  April 26, 2018
    //  Details:
    //                  This file contains handy utility functions for all php web transactions
    //  Dependencies:
    //                  PHP
    //                  MySQL
    //                  Apache Web Server

    // @function    formatResponse
    // @parameter   status - the string status to send back to the client. The set of valid inputs includes ONLY these two strings: "success" or "failure".
    // @parameter   body - the string message to send as a response body, along with the status
    // @returns     On success: the formatted associative array response object
    //              On failure: false
    // @details     Creates an associative array object to be sent to the client
    function formatResponse($status, $body) {
        $object = ["status" => $status, "body" => $body];
        if ($status != "success" && $status != "failure") {
            $object = false;
        }

        return $object;
    }

    // @function    replyToClient
    // @parameter   response - the associative array object acquired from a call to formatResponse().
    // @parameter   statuscode - (Optional) The response status code to send to the client (via response header)
    // @returns     n/a
    // @details     Sends a JSON-encoded response back to the client
    function replyToClient($response, $statuscode = NULL) {
        // Customize response status
        if (isset($statuscode)) {
            http_response_code(200);
        }
        // Send the response data as JSON to the client
        echo json_encode($response);
        return;
    }

    // @function    tryPostRestore
    // @parameter   n/a
    // @returns     the $_POST variable after attempting to acquire its data
    // @details     Certain front-end MVC frameworks (i.e. AngularJS) do not pass their post data to the proper field. This function attempts to place that data in the right place and return it.
    // @note        According to Mike Brant's answer on the Stack Overflow post, AngularJS's $http.post() uses "Content-Type:application/json", but I'm not changing my data in the way i'm supposed to in the backend, hence the absence of any $_POST variable content. I will have to get the data here. See this post for more details: "https://stackoverflow.com/questions/15485354/angular-http-post-to-php-and-undefined"
    function tryPostRestore () {
        return array_merge($_POST, (array) json_decode(file_get_contents("php://input", true)));
    }

    // END utility.php
?>