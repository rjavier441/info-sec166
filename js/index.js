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
var dbgMode = true;
var protocol = (dbgMode) ? "http" : "https";
var hostname = (dbgMode) ? "localhost/infosec-166" : "www.rjonaws.com";
var urls = {
	"loginSubmit": `${protocol}://${hostname}/server-side/login.php`
};



// Initialize AngularJS App
var app = angular.module("indexApp", []);



// BEGIN Angular Controllers
app.controller("loginController", function ($scope, $http) {
	// BEGIN Model
	var ctl = $scope;
	$scope.error = "";	// error message
	$scope.username = "";
	$scope.password = "";
	// END Model

	// BEGIN Controller Functions
	$scope.$onInit = function () {
		log(`$onInit`, `loginController`, `Login controller initialized`);
	};
	$scope.submit = function () {
		log(`submit`, `loginController`, `submitting credentials...`);	// debug
		$http.post().then().catch();
	};
	// END Controller Functions
});
// END Angular Controllers



// END index.js
