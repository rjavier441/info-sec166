<?php
// Enable session data usage
session_start();
session_write_close();

// Check if HTTPS is being used, otherwise do a redirection
if($_SERVER["HTTPS"] != "on") {
	// With https, the entire request body is encrypted
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

// Acquire token from querystring parameters
$client_token = $_GET["token"];
$recorded_token = $_SESSION["token"];

// Check that the token already has session data
if ($client_token !== $recorded_token) {
	// Take client back to the login page
	header("Location: https://" . $_SERVER["HTTP_HOST"] . "/info-sec166");
	exit();
}
?>
<!DOCTYPE html>
<html>
<!-- BEGIN header -->
<head>
	<!-- Metadata -->
	<meta charset="utf-8">
	<meta name="description" content="InfoSec166 Portal">
	<meta name="keywords" content="infosec,portal,se166">
	<meta name="author" content="R. Javier">
	<meta name="last-modified" content="May 9, 2018">

	<!-- Phone Screen Compatibility -->
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Tab Title -->
	<title>InfoSec166</title>

	<!-- Standard Includes -->
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.7/angular.min.js"></script>

	<!-- Boostrap3.3.7 Includes -->
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	
	<!-- FontAwesome -->
	<link href="https://use.fontawesome.com/releases/v5.0.4/css/all.css" rel="stylesheet">

	<!-- Custom Google Font: Rubik -->
	<link href="https://fonts.googleapis.com/css?family=Rubik" rel="stylesheet">

	<!-- Page CSS -->
	<link rel="stylesheet" href="css/home.css">
</head>
<!-- BEGIN header -->

<!-- BEGIN body -->
<body>
	
</body>
<!-- END body -->

<!-- BEGIN footer -->
<footer>
	<script type="text/javascript" src="js/utility.js"></script>
	<script type="text/javascript" src="js/index.js"></script>
	<h4>Powered by: <i class="fab fa-angular"></i> <i class="fab fa-js-square"></i></h4>
</footer>
<!-- END footer -->
</html>