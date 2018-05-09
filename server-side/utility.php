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
    // @parameter   message - the string message to send along with the status
    // @returns     On success: the formatted associative array response object
    //              On failure: false
    // @details     Creates an associative array object to be sent to the client
    function formatResponse($status, $message) {
        $object = ["status" => $status, "message" => $message];
        if ($status != "success" && $status != "failure") {
            $object = false;
        }

        return $object;
    }

    // @function    replyToClient
    // @parameter   response - the associative array object acquired from a call to formatResponse().
    // @returns     n/a
    // @details     Sends a JSON-encoded response back to the client
    function replyToClient($response) {
        // Send the response data as JSON to the client
        echo json_encode($response);
        return;
    }

    // END utility.php
?>