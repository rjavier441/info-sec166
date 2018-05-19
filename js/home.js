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
var urls = {
	"userInfo": `${protocol}://${hostname}/server-side/userinfo.php`,
	"logout": `${protocol}://${hostname}/server-side/logout.php`,
	"getposts": `${protocol}://${hostname}/server-side/postviewer.php`
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
	$scope.logout = function (full = false) {
		var requestBody = {
			"action": (full) ? "logoutall" : "logout",
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

		log("logout", "navbarController", `Logging you out, ${ctl.username}!`);
		$http.post(urls.logout, requestBody, config).then((response) => {
			var hasStatus = (typeof response.data.status === "undefined") ? false : true;
			var hasBody = (typeof response.data.body === "undefined") ? false : true;
			var hasRedirect = (!hasBody) ? false : (typeof response.data.body.redirect === "undefined") ? false : true;
			var hasNonce = (!hasBody) ? false : (typeof response.data.body.nonce === "undefined") ? false : true;
			var hasEmsg = (!hasBody) ? false : (typeof response.data.body.emsg === "undefined") ? false : true;

			log("post", "navbarController", `Received: ${JSON.stringify(response.data)}`);
			ctl.setError("");
			if (!hasStatus || !hasBody || !hasRedirect || !hasNonce) {
				log("post", "navbarController", "Response is incomplete");
				var msg = (hasEmsg) ? response.data.body.emsg : "Response is incomplete; contact the server admin";
				ctl.setError(msg);
			} else if (!checkTimestampNonce(requestBody.data.timestamp, response.data.body.nonce)) {
				// Nonce is not correct; this server I'm connected to could be lying about who they claim they are!
				ctl.setError("Incorrect Nonce");
				log(`post`, `navbarController`, `expected nonce "${Date.parse(requestBody.data.timestamp)}", received "${response.data.body.nonce}"`);
			} else {
				switch (response.status) {
					case 200: {
						// On successful logout and annihilation of session, take the user back to the login portal.
						log("post", "navbarController", "Logout successful!");
						sessionStorage.removeItem("token");	// an extra precaution
						$window.location = response.data.body.redirect;
						break;
					}
					default: {
						log("post", "navbarController", `Unexpected status ${response.status} received...`);
						ctl.setError(`Unexpected status ${response.status} received...`);
						break;
					}
				}
			}
		}).catch((errResponse) => {
			log("post", "navbarController", `An error occurred: ${JSON.stringify(errResponse)}`);
			ctl.setError(`An error occurred: ${JSON.stringify(errResponse)}`);
		});
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
				ctl.setError(msg);
			} else if (!checkTimestampNonce(requestBody.data.timestamp, response.data.body.nonce)) {
				// Nonce is not correct; this server I'm connected to could be lying about who they claim they are!
				ctl.setError("Incorrect Nonce");
				log(`post`, `navbarController`, `expected nonce "${Date.parse(requestBody.data.timestamp)}", received "${response.data.body.nonce}"`);
			} else {
				switch (response.status) {
					case 200: {
						ctl.username = response.data.body.userinfo.username;
						break;
					}
					default: {
						log("post", "navbarController", `Unexpected status ${response.status} received...`);
						ctl.setError(`Unexpected status ${response.status} received...`);
						break;
					}
				}
			}
		}).catch((errResponse) => {
			log("post", "navbarController", `An error occurred: ${JSON.stringify(errResponse)}`);
			ctl.setError(`An error occurred: ${JSON.stringify(errResponse)}`);
		});
	};
	// END Utility Functions
});

app.controller("postAreaController", function ($scope, $http, $window) {
	// BEGIN Model
	var ctl = $scope;
	$scope.postList = [];
	$scope.error = "";
	$scope.pagenum = 0;
	$scope.pagesize = 10;
	$scope.searchtype = "title";
	$scope.searchterm = "";
	// END Model

	// BEGIN Controller Functions
	$(document).ready(function () {
		console.log("postAreaController initialized");
		ctl.getPosts();
		// ctl.initEventBindings();
	});
	// $scope.initEventBindings = function () {};
	$scope.setError = function (msg) {
		ctl.error = msg;
	};
	$scope.getPosts = function () {
		var requestBody = {
			"action": "search",
			"data": {
				"token": sessionStorage.getItem("token"),
				"timestamp": Date.now(),
				"pagesize": ctl.pagesize,
				"pagenum": ctl.pagenum,
				"searchtype": ctl.searchtype,
				"searchterm": ctl.searchterm
			}
		};
		var config = {
			"headers": {
				"Content-Type": "application/json"
			}
		};

		log("getPosts", "postAreaController", `Requesting posts...`);
		$http.post(urls.getposts, requestBody, config).then((response) => {
			var hasStatus = (typeof response.data.status === "undefined") ? false : true;
			var hasBody = (typeof response.data.body === "undefined") ? false : true;
			var hasNonce = (!hasBody) ? false : (typeof response.data.body.nonce === "undefined") ? false : true;
			var hasEmsg = (!hasBody) ? false : (typeof response.data.body.emsg === "undefined") ? false : true;
			var hasPostsObject = (!hasBody) ? false : (typeof response.data.body.posts === "undefined") ? false : true;
			var hasPostsSearchStatus = (!hasPostsObject) ? false : (typeof response.data.body.posts.success === "undefined") ? false : true;
			var hasPostsSearchResult = (!hasPostsObject) ? false : (typeof response.data.body.posts.result === "undefined") ? false : true;

			log(`post`, `postAreaController`, `Response received: ${JSON.stringify(response.data)}`);	// debug
			ctl.setError("");
			if (!hasStatus || !hasNonce) {
				log(`post`, `postAreaController`, `Response is incomplete`);
				var msg = (hasEmsg) ? response.data.body.emsg : "Response incomplete; contact the server admin!";
				ctl.setError(msg);
			} else if (!checkTimestampNonce(requestBody.data.timestamp, response.data.body.nonce)) {
				// Nonce is not correct; this server I'm connected to could be lying about who they claim they are!
				log(`post`, `postAreaController`, `expected nonce "${Date.parse(requestBody.data.timestamp)}", received "${response.data.body.nonce}"`);
				ctl.setError("Incorrect Nonce");
			} else if (!hasPostsSearchStatus) {
				log("post", "postAreaController", "Post search status data is missing");
				ctl.setError("Invalid blog post search status data received");
			} else {
				switch (response.status) {
					case 200: {
						if (response.data.body.posts.success === false) {
							log("post", "postAreaController", `Post search unsuccessful`);
							ctl.setError("Post search unsuccessful");
						} else {
							var tempPostList = response.data.body.posts.result;

							// Add the "show" member to each member of the array
							log("post", "postAreaController", `Post search successful`);
							for(var i = 0; i < tempPostList.length; i++) {
								tempPostList[i].show = false;
							}
							ctl.postList = tempPostList;
						}
						break;
					}
					default: {
						log(`post`, `postAreaController`, `Unexpected status code ${response.status}`);
						ctl.setError(`Unexpected status code ${response.status}`);
						break;
					}
				}
			}
		}).catch((errResponse) => {
			log("post", "postAreaController", `An error occurred: ${JSON.stringify(errResponse)}`);
		});
	}
	// END Controller Functions
});
// END Angular Controllers



// END home.js
