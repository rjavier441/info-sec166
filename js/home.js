//	PROJECT: 		Infosec 166
// 	Name: 			R. Javier
// 	File: 			home.js
// 	Date Created: 	May 9, 2018
// 	Last Modified: 	May 9, 2018
// 	Details:
// 					This file controls the home page as an angularjs app
// 	Dependencies:
// 					AngularJS v1.6.7



var storageOk = storageAvailable("sessionStorage");
var dbgMode = true;
// var protocol = (dbgMode) ? "http" : "https";
var protocol = "https";
var hostname = (dbgMode) ? "localhost/info-sec166" : "www.rjonaws.com";
var urls = {
	"userInfo": `${protocol}://${hostname}/server-side/userinfo.php`
};



// Initialize AngularJS app
var app = angular.module("homeApp", []);



// BEGIN Angular Controllers
app.controller("navbarController", function ($scope, $http, $window) {
	// BEGIN model
	var ctl = $scope;
	$scope.username = "";
	$scope.error = "";
	// END model

	// BEGIN Controller Functions
	$(document).ready(function () {
		ctl.loadUserInfo();
		console.log("Home initialized");
	});
	$scope.logout = function () {
		log("logout", "navbarController", `Logging you out, ${ctl.username}!`);
	};
	// END Controller Functions

	// BEGIN Utility Functions
	$scope.setError = function (msg) {
		ctl.error = msg;
	};
	$scope.loadUserInfo = function () {
		var requestBody = {
			"action": "getall",
			"data": {
				"token": sessionStorage.getItem("token"),
				"timestamp": Date.now()
			}
		};
		var config = {
			"headers": {
				"Content-Type": "application/json"
			}
		};

		log("loadUserInfo", "navbarController", "Loading user info...");
		$http.post(urls.userInfo, requestBody, config).then((response) => {
			var hasStatus = (typeof response.data.status === "undefined") ? false : true;
			var hasBody = (typeof response.data.body === "undefined") ? false : true;
			var hasUserInfo = (!hasBody) ? false : (typeof response.data.body.userinfo === "undefined") ? false : true;
			var hasNonce = (!hasBody) ? false : (typeof response.data.body.nonce === "undefined") ? false : true;
			var hasEmsg = (!hasBody) ? false : (typeof response.data.body.emsg === "undefined") ? false : true;

			log("post", "navbarController", `Received: ${JSON.stringify(response.data)}`);	// debug
			ctl.setError("");
			if (!hasStatus || !hasBody || !hasNonce || !hasUserInfo) {
				log("post", "navbarController", "Response is incomplete");
				var msg = (hasEmsg) ? response.data.body.emsg : "Response is incomplete; contact the server admin";
			}
			switch (response.status) {
				case 200: {
					ctl.username = response.data.body.userinfo.username;
					break;
				}
				default: {
					log("post", "navbarController", `Unexpected status ${response.status} received...`);
					break;
				}
			}
		}).catch((errResponse) => {
			log("post", "navbarController", `An error occurred: ${JSON.stringify(errResponse)}`);
		});
	};
	// END Utility Functions
});
// END Angular Controllers



// END home.js
