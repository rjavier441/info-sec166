<?php
// Unfortunately, I haven't figured out how to include in front-end php files. I'll have to copy this function for now.
function checkDebugMode ($_IS_OPT) {
    return isset($_IS_OPT) && $_IS_OPT["dbgMode"] === true;
}

// Enable session data usage
session_start();

// Check if HTTPS is being used, otherwise do a redirection
if($_SERVER["HTTPS"] != "on") {
	// With https, the entire request body is encrypted
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

// Compatibility Check: AWS environment differs from my own, so I must refer to them differently
if (checkDebugMode($_IS_OPT)) {
	$subdir = "/info-sec166";
} else {
	$subdir = "";
}

// Acquire token from querystring parameters
$client_token = $_GET["token"];
$recorded_token = $_SESSION["token"];

// Check that the token already has session data
if ($client_token !== $recorded_token || !isset($_SESSION["token"])) {
	// Take client back to the login page
	header("Location: https://" . $_SERVER["HTTP_HOST"] . $subdir);
	exit();
}
session_write_close();
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
<body class="container-fluid" ng-app="homeApp">
	<nav class="navbar navbar-default navbar-fixed-top" ng-controller="navbarController">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapse" data-toggle="collapse" data-target="#navbarCollapsible" aria-expanded="false">
					<span class="sr-only">Toggle Navigation</span>
				</button>
				<a href="#" class="navbar-brand">
					<span><img src="img/icon-1968247_1280.png" alt="" style="max-height: 100%"> InfoSec166</span>
				</a>
			</div>
			<div id="navbarCollapsible" class="collapse navbar-collapse">
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Welcome, {{username}} <span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li ng-click="logout()"><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
							<li><a href="#"></a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<div class="row" ng-controller="postAreaController">
		<div class="col-xs-1 col-sm-1 col-md-2 col-lg-2"></div>
		<div class="post-area col-xs-10 col-sm-10 col-md-8 col-lg-8">
			<div class="well" ng-repeat="post in postList">
				<h2>{{post.title}}</h2>
				<p>{{(post.content).slice(0, 100)}}</p>
			</div>
		</div>
		<div class="col-xs-1 col-sm-1 col-md-2 col-lg-2"></div>
	</div>
</body>
<!-- END body -->

<!-- BEGIN footer -->
<footer>
	<script type="text/javascript" src="js/lib/options.js"></script>
	<script type="text/javascript" src="js/lib/utility.js"></script>
	<script type="text/javascript" src="js/home.js"></script>
	<h4>Powered by: <i class="fab fa-angular"></i> <i class="fab fa-js-square"></i></h4>
</footer>
<!-- END footer -->
</html>