//	PROJECT: 		Infosec 166
// 	Name: 			R. Javier
// 	File: 			index.js
// 	Date Created: 	May 9, 2018
// 	Last Modified: 	May 9, 2018
// 	Details:
// 					This file controls the index page as an angularjs app
// 	Dependencies:
// 					AngularJS v1.6.7



// Globals
var storageOk = storageAvailable("sessionStorage");
var urls = {
	"loginSubmit": `${protocol}://${hostname}/server-side/login.php`
};



// Initialize AngularJS App
var app = angular.module("indexApp", []);



// BEGIN Angular Controllers
app.controller("loginController", function ($scope, $http, $window) {
	// BEGIN Model
	var ctl = $scope;
	$scope.error = "";	// error message
	$scope.username = "";
	$scope.password = "";
	// END Model

	// BEGIN Controller Functions
	$(document).ready(function () {
		log(`$onInit`, `loginController`, `Login controller initialized`);
	});
	$scope.setError = function (msg) {
		// $("#loginModalErr").html(msg);
		ctl.error = msg;
	};
	$scope.submit = function () {
		var requestBody = {
			"action": "secure-login",
			"data": {
				"username": ctl.username,
				"password": ctl.password,
				"timestamp": Date.now()
			}
		};
		var config = {
			"headers": {
				"Content-Type": "application/json"
			}
		};

		console.log(JSON.stringify(requestBody));

		log(`submit`, `loginController`, `Submitting credentials...`);	// debug
		$http.post(urls.loginSubmit, requestBody, config).then((response) => {
			var hasStatus = (typeof response.data.status === "undefined") ? false : true;
			var hasBody = (typeof response.data.body === "undefined") ? false : true;
			var hasToken = (!hasBody) ? false : (typeof response.data.body.token === "undefined") ? false : true;
			var hasRedirect = (!hasBody) ? false : (typeof response.data.body.redirect === "undefined") ? false : true;
			var hasNonce = (!hasBody) ? false : (typeof response.data.body.nonce === "undefined") ? false : true;
			var hasEmsg = (!hasBody) ? false : (typeof response.data.body.emsg === "undefined") ? false : true;

			log(`post`, `loginController`, `Response received: ${JSON.stringify(response.data)}`);	// debug
			ctl.setError("");
			if (!hasStatus || !hasBody || !hasToken || !hasRedirect || !hasNonce) {
				log(`post`, `loginController`, `Response is incomplete`);
				var msg = (hasEmsg) ? response.data.body.emsg : "Response incomplete; contact the server admin!";
				ctl.setError(msg);
			} else if (!checkTimestampNonce(requestBody.data.timestamp, response.data.body.nonce)) {
				// Nonce is not correct; this server I'm connected to could be lying about who they claim they are!
				ctl.setError("Incorrect Nonce");
				log(`post`, `loginController`, `expected nonce "${Date.parse(requestBody.data.timestamp)}", received "${response.data.body.nonce}"`);
			} else {
				switch (response.status) {
					case 200: {
						log(`post`,`loginController`, `Login successful`);
						if (storageOk) {
							sessionStorage.setItem("token", response.data.body.token);
						}
						ctl.enterDashboard(response.data.body.redirect);
						break;
					}
					default: {
						log(`post`, `loginController`, `Unexpected status code ${response.status}`);
						ctl.setError(`Unexpected status code ${response.status}`);
						break;
					}
				}
			}
		}).catch((errResponse) => {
			log(`post`, `loginController`, `An error occurred: ${JSON.stringify(errResponse)}`);
		});
	};
	$scope.enterDashboard = function (redir) {
		// var config = {
		// 	"params": {
		// 		"token": sessionStorage.getItem("token")
		// 	},
		// 	"headers": {
		// 		"Content-Type": "text/html"
		// 	}
		// };

		log(`enterDashboard`, "loginController", `Going to dashboard`);
		// $http.get(redir, config);
		$window.location = `${redir}?token=${sessionStorage.getItem("token")}`;
	};
	// END Controller Functions
});

app.controller("registrationController", function ($scope, $http) {});
// END Angular Controllers



// END index.js
