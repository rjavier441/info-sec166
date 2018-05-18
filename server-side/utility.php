<?php
	//  PROJECT:        Infosec166
    //  Name:           R. Javier
    //  File:           utility.php
    //  Date Created:   May 10, 2018
    //  Last Modified:  May 10, 2018
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
        $object = array("status" => $status, "body" => $body);
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
            if (!function_exists('http_response_code')) {
                setHttpResponseCode($statuscode);
            } else {
                http_response_code($statuscode);
            }
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

    // @function    checkDebugMode
    // @parameter   $_IS_OPT - the InfoSec Project Options variable that may or may not be defined within the credentials.php file
    // @returns     On debug mode: true
    //              On production mode: false
    //              On unverified/error: -1
    // @details     This function returns whether or not debug mode is set, and allows the server side to adjust redirect file paths accordingly
    function checkDebugMode ($_IS_OPT) {
        return isset($_IS_OPT) && $_IS_OPT["dbgMode"] === true;
    }

    // @function    setHttpResponseCode
    // @parameter   code - the http/https response code to set
    // @returns     n/a
    // @details     This is a compatibility function that is meant to emulate php 5.4's http_response_code() function. It checks for this function's availability, and uses it if it is available; otherwise, it performs its own implementation.
    function setHttpResponseCode ($code = NULL) {
        if ($code !== NULL) {
            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }
    }

    // END utility.php
?>
