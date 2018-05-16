<?php
// Check if HTTPS is being used, otherwise do a redirection
if($_SERVER["HTTPS"] != "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
?>

<!doctype html>
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

	<!-- Bootstrap4.1 Includes -->
	<!-- Unfortunately, there is no official support for BS4 on Linux Browsers... -->
	<!--<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>-->

	<!-- Boostrap3.3.7 Includes -->
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	
	<!-- FontAwesome -->
	<link href="https://use.fontawesome.com/releases/v5.0.4/css/all.css" rel="stylesheet">

	<!-- Materialze CSS -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css"> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script> -->

	<!-- Custom Google Font: Rubik -->
	<link href="https://fonts.googleapis.com/css?family=Rubik" rel="stylesheet">

	<!-- Page CSS -->
	<link rel="stylesheet" href="css/index.css">
</head>
<!-- END header -->

<!-- BEGIN body -->
<body class="container-fluid" ng-app="indexApp">
	<div class="jumbotron jumbotron-custom">
		<h1>PROJECT: InfoSec-166 <img src="img/icon-1968247_1280.png" alt="" height="250px" width="250px"></h1>
		<h2>A blog site for info security topics</h2>
		<p>Created By: R. Javier</p>
	</div>
	<div class="row">
		<div class="col-sm-6 col-md-3 col-lg-2" ng-controller="loginController">
			<div class="thumbnail">
				<a href="#">
					<button class="btn btn-default btn-thumbnail" data-toggle="modal" data-target="#loginModal" style="width:100%">
						<img src="img/symbol-2480166_640.png" alt="Login Image" height="150px" width="150px" data-toggle="tooltip" title="Launch the login prompt!">
						<div class="caption">
							<h2>Login</h2>
						</div>
					</button>
				</a>
			</div>
			<div id="loginModal" class="modal fade" tabindex="-1" role="dialog">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Login</h3>
							<h5 class="msg-error" ng-bind="error"></h5>
						</div>
						<div class="modal-body">
							<div class="input-group">
								<span class="input-group-addon">Username</span>
								<input type="text" ng-model="username" class="form-control">
							</div>
							<div class="input-group">
								<span class="input-group-addon">Password</span>
								<input type="password" ng-model="password" class="form-control">
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="button" class="btn btn-primary" ng-click="submit()">Submit</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-3 col-lg-2" ng-controller="registrationController">
			<div class="thumbnail">
				<a href="#">
					<button class="btn btn-default btn-thumbnail" data-toggle="modal" data-target="#registrationModal" style="width:100%">
						<img src="img/symbol-2485372_640.png" alt="Registration Image" height="150px" width="150px" data-toggle="tooltip" title="Launch the registration prompt!">
						<div class="caption">
							<h2>Register</h2>
						</div>
					</button>
				</a>
			</div>
			<div id="registrationModal" class="modal fade" tabindex="-1" role="dialog">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Registration</h3>
							<h5 class="msg-error" ng-bind="error"></h5>
						</div>
						<div class="modal-body">
							<div class="input-group">
								<span class="input-group-addon">Username</span>
								<input type="text" ng-model="username" class="form-control">
							</div>
							<div class="input-group">
								<span class="input-group-addon">Password</span>
								<input type="password" ng-model="password" class="form-control">
							</div>
							<div class="input-group">
								<span class="input-group-addon">Birth Date</span>
								<input type="date" ng-model="birthdate" class="form-control">
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="button" class="btn btn-primary" ng-click="submit()">Submit</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
<!-- END body -->

<!-- BEGIN footer -->
<footer>
	<script type="text/javascript" src="js/lib/options.js"></script>
	<script type="text/javascript" src="js/lib/utility.js"></script>
	<script type="text/javascript" src="js/index.js"></script>
	<h4>Powered by: <i class="fab fa-angular"></i> <i class="fab fa-js-square"></i></h4>
</footer>
<!-- END footer -->
</html>