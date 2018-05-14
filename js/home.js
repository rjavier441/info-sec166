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
var urls = {};



// Initialize AngularJS app
var app = angular.module("homeApp", []);



// BEGIN Angular Controllers
app.controller("logoutController", function ($scope, $http, $window) {
	// BEGIN model
	var ctl = $scope;
	// END model

	// BEGIN Controller Functions
	$scope.$onInit = function () {};
	// END Controller Functions
});
// END Angular Controllers



// END home.js
