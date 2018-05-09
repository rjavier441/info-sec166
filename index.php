<?php
// Check if HTTPS is being used, otherwise do a redirection
if($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
?>

<!DOCTYPE html>
<html>
<!-- BEGIN header -->
<head>
	<!-- Metadata -->
	<meta charset="UTF-8">
	<meta name="description" content="InfoSec166 Portal">
	<meta name="keywords" content="infosec,portal,se166">
	<meta name="author" content="R. Javier">
	<meta name="last-modified" content="May 9, 2018">

	<!-- Phone Screen Compatibility -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Tab Title -->
	<title>InfoSec166</title>

	<!-- Standard Includes -->
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.7/angular.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link href="https://use.fontawesome.com/releases/v5.0.4/css/all.css" rel="stylesheet">
	<link rel="stylesheet" href="css/index.css">

	<!-- Custom Google Font: Rubik -->
	<link href="https://fonts.googleapis.com/css?family=Rubik" rel="stylesheet">
</head>
<!-- END header -->

<!-- BEGIN body -->
<body class="container-fluid" ng-app="indexApp">
	<div class="row">
		<div class="jumbotron">
			<h1>PROJECT: InfoSec-166</h1>
			<h3>Created By: R. Javier</h3>
			<p>A blog site for info security topics</p>
		</div>
		<div class="row" ng-controller="loginController">
			<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
				<button class="btn btn-primary btn-login" type="button" data-toggle="collapse" data-target="#loginCollapse" aria-expanded="false" aria-controls="loginCollapse">Login</button>
				<div id="loginCollapse" class="collapse">
					<div class="well">
						<div class="input-group">
							<span class="input-group-addon">Username</span>
							<input type="text" ng-model="username" class="form-control">
						</div>
						<div class="input-group">
							<span class="input-group-addon">Password</span>
							<input type="password" ng-model="password" class="form-control">
						</div>
						<button class="btn btn-success btn-submit" ng-click="submit()">Submit</button>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
				<button class="btn btn-info btn-register">Register</button>
			</div>
		</div>
	</div>
</body>
<!-- END body -->

<!-- BEGIN footer -->
<footer>
	<script type="text/javascript" src="js/utility.js"></script>
	<script type="text/javascript" src="js/index.js"></script>
</footer>
<!-- END footer -->
</html>