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
	"loginSubmit": `${protocol}://${hostname}/server-side/login.php`,
	"registrationSubmit": `${protocol}://${hostname}/server-side/register.php`
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

app.controller("registrationController", function ($scope, $http) {
	// BEGIN Model
	var ctl = $scope; // gives ctl a copy of a reference to $scope
	$scope.error = "";
	$scope.username = "";
	$scope.password = "";
	$scope.birthdate = 0;
	// END Model

	// BEGIN Controller Functions
	$(document).ready(function () {
		log(`$onInit`, `registrationController`, `Registration controller initialized`);
	});
	$scope.setError = function (msg) {
		ctl.error = msg;
	};
	$scope.submit = function () {
		if (Number.isNaN(Date.parse(ctl.birthdate)) || ctl.birthdate <= 0) {
			log(`submit`, `registrationController`, `Please enter a valid date`);
			ctl.setError("");
			ctl.setError("Please enter a valid date");
			return;
		}

		var gRecaptchaResponse = grecaptcha.getResponse();
		var requestBody = {
			"action": "register",
			"data": {
				"g_recaptcha_response": gRecaptchaResponse,
				"username": ctl.username,
				"password": ctl.password,
				"birthyear": ctl.birthdate.getFullYear(),
				"birthmonth": ctl.birthdate.getMonth() + 1,
				"birthday": ctl.birthdate.getDate(),
				"timestamp": Date.now()
			}
		};
		var config = {
			"headers": {
				"Content-Type": "application/json"
			}
		};

		log(`submit`, `registrationController`, `Submitting registration...${ctl.birthdate} ${JSON.stringify(requestBody)}`);	// debug
		$http.post(urls.registrationSubmit, requestBody, config).then((response) => {
			var hasStatus = (typeof response.data.status === "undefined") ? false : true;
			var hasBody = (typeof response.data.body === "undefined") ? false : true;
			var hasNonce = (!hasBody) ? false : (typeof response.data.body.nonce === "undefined") ? false : true;
			var hasEmsg = (!hasBody) ? false : (typeof response.data.body.emsg === "undefined") ? false : true;

			log(`post`, `registrationController`, `Response received: ${JSON.stringify(response.data)}`);	// debug
			ctl.setError("");
			grecaptcha.reset();
			if (!hasStatus || !hasBody || !hasNonce) {
				// Missing some important parameters; let's fail just in case there's a deeper issue
				log(`post`, `registrationController`, `Response is incomplete`);
				var msg = (hasEmsg) ? response.data.body.emsg : "Response incomplete; contact the server admin!";
				ctl.setError(msg);
			} else if (!checkTimestampNonce(requestBody.data.timestamp, response.data.body.nonce)) {
				// Nonce is not correct; this server I'm connected to could be lying about who they claim they are!
				ctl.setError("Incorrect Nonce");
				log(`post`, `registrationController`, `expected nonce "${Date.parse(requestBody.data.timestamp)}", received "${response.data.body.nonce}"`);
			} else {
				// Otherwise, act based on the response
				switch (response.status) {
					case 200: {
						if (response.data.status === "success") {
							log(`post`,`registrationController`, `Registration successful!`);
							$("#registrationModal").modal("hide");
						} else {
							var msg = (hasEmsg) ? response.data.body.emsg : "An unknown error occurred";
							log(`post`,`registrationController`, msg);
							ctl.setError(msg);
						}
						break;
					}
					default: {
						log(`post`, `registrationController`, `Unexpected status code ${response.status}`);
						ctl.setError(`Unexpected status code ${response.status}`);
						break;
					}
				}
			}
		}).catch((errResponse) => {
			log(`post`, `registrationController`, `An error occurred: ${JSON.stringify(errResponse)}`);
		});
	};
	// END Controller Functions
});
// END Angular Controllers



// END index.js
