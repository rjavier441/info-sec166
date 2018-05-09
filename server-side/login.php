<?php
require "utility.php";
require "lib/credentials.php";

// Enable use of session variables
session_start();

// Unset all session variables for this session
$_SESSION = array();

// Inputs
$username = "";
$password = "";
$errors = array();

// MySQL database connection
?>