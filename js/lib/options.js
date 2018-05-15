//	PROJECT: 		Infosec166
// 	Name: 			R. Javier
// 	File: 			options.js
// 	Date Created: 	May 14, 2018
// 	Last Modified: 	May 14, 2018
// 	Details:
// 					This file contains configuration options that allow the sys admin to easily switch between development and production versions of the site.
// 	Dependencies:
// 					JavaScript ECMAScript 6 (string templating)

var dbgMode = false;
var protocol = "https";
var hostname = (dbgMode) ? "localhost/info-sec166" : "www.rjonaws.com";

// END options.js
