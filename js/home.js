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
	"postViewer": `${protocol}://${hostname}/server-side/postviewer.php`
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
	$scope.previewSize = 300;
	$scope.error = "";
	$scope.pagenum = 0;
	$scope.pagesize = 10;
	$scope.searchtype = "title";
	$scope.searchterm = "";

	$scope.editor = {
		"error": "",
		"postid": -1,
		"title": "",
		"content": ""
	};
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
	$scope.setPostError = function (index, msg) {
		ctl.postList[index].error = msg;
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
		$http.post(urls.postViewer, requestBody, config).then((response) => {
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
								tempPostList[i].show = false;	// adds a show variable to this post
								tempPostList[i].error = "";	// adds an error variable to this specific post block
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
	$scope.deletePost = function (index) {
		var postToDelete = ctl.postList[index];
		var requestBody = {
			"action": "delete",
			"data": {
				"token": sessionStorage.getItem("token"),
				"timestamp": Date.now(),
				"postid": postToDelete.postid
			}
		};
		var config = {
			"headers": {
				"Content-Type": "application/json"
			}
		};

		log("deletePost", "postAreaController", `Requesting posts...`);
		$http.post(urls.postViewer, requestBody, config).then((response) => {
			var hasStatus = (typeof response.data.status === "undefined") ? false : true;
			var hasBody = (typeof response.data.body === "undefined") ? false : true;
			var hasNonce = (!hasBody) ? false : (typeof response.data.body.nonce === "undefined") ? false : true;
			var hasEmsg = (!hasBody) ? false : (typeof response.data.body.emsg === "undefined") ? false : true;
			var hasSuccess = (!hasBody) ? false : (typeof response.data.body.success === "undefined") ? false : true;

			log(`post`, `postAreaController`, `Response received: ${JSON.stringify(response.data)}`);	// debug
			ctl.setError("");
			ctl.setPostError(index, "");
			if (!hasStatus || !hasNonce) {
				log(`post`, `postAreaController`, `Response is incomplete`);
				var msg = (hasEmsg) ? response.data.body.emsg : "Response incomplete; contact the server admin!";
				ctl.setError(msg);
			} else if (!checkTimestampNonce(requestBody.data.timestamp, response.data.body.nonce)) {
				// Nonce is not correct; this server I'm connected to could be lying about who they claim they are!
				ctl.setError("Incorrect Nonce");
				log(`post`, `postAreaController`, `expected nonce "${Date.parse(requestBody.data.timestamp)}", received "${response.data.body.nonce}"`);
			} else {
				switch (response.status) {
					case 200: {
						if (hasSuccess && response.data.body.success === true) {
							log("post", "postAreaController", "Post Deleted");
							ctl.getPosts();	// reload posts
						} else {
							var msg = (hasEmsg) ? response.data.body.emsg : "Post was not updated";
							log(`post`, `postAreaController`, `Post was not updated`);
							ctl.setError(msg);
							ctl.setPostError(index, msg);
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
			log(`post`, `postAreaController`, `An error occurred: ${JSON.stringify(errResponse)}`);
		});
	};
	$scope.launchEditor = function (index) {
		// console.log(`Editing ${JSON.stringify(ctl.postList[index])}`);	// debug
		var currentPost = ctl.postList[index];
		ctl.editor.postid = currentPost.postid;
		ctl.editor.title = currentPost.title;
		ctl.editor.content = currentPost.content;

		log("launchEditor", "postAreaController", `Launching editor...`);
		$("#editor").modal("show");
	};
	$scope.clearEditor = function () {
		log("clearEditor", "postAreaController", `Clearing editor...`);
		ctl.editor.error = "";
		ctl.editor.postid = -1;
		ctl.editor.title = "";
		ctl.editor.content = "";
	};
	$scope.closeEditorManually = function () {
		log("closeEditorManually", "postAreaController", `Dismissing editor...`);
		$("#editor").modal("hide");
	};
	$scope.submitEditorData = function () {
		var requestBody = {
			"action": "update",
			"data": {
				"token": sessionStorage.getItem("token"),
				"timestamp": Date.now(),
				"title": ctl.editor.title,
				"content": ctl.editor.content,
				"postid": ctl.editor.postid
			}
		};
		var config = {
			"headers": {
				"Content-Type": "application/json"
			}
		};

		log("submitEditorData", "postAreaController", `Saving changes to post...`);
		$http.post(urls.postViewer, requestBody, config).then((response) => {
			var hasStatus = (typeof response.data.status === "undefined") ? false : true;
			var hasBody = (typeof response.data.body === "undefined") ? false : true;
			var hasNonce = (!hasBody) ? false : (typeof response.data.body.nonce === "undefined") ? false : true;
			var hasEmsg = (!hasBody) ? false : (typeof response.data.body.emsg === "undefined") ? false : true;
			var hasSuccess = (!hasBody) ? false : (typeof response.data.body.success === "undefined") ? false : true;

			log(`post`, `postAreaController`, `Response received: ${JSON.stringify(response.data)}`);
			ctl.editor.error = "";
			if (!hasStatus || !hasNonce) {
				log(`post`, `postAreaController`, `Response is incomplete`);
				var msg = (hasEmsg) ? response.data.body.emsg : "Response is incomplete; contact the server admin!";
				ctl.editor.error = msg;
			} else if (!checkTimestampNonce(requestBody.data.timestamp, response.data.body.nonce)) {
				// Nonce is not correct; this server I'm connected to could be lying about who they claim they are!
				ctl.editor.error = "Incorrect Nonce";
				log(`post`, `postAreaController`, `expected nonce "${Date.parse(requestBody.data.timestamp)}", received "${response.data.body.nonce}"`);
			} else {
				switch (response.status) {
					case 200: {
						if (hasSuccess && response.data.body.success === true) {
							// Close editor and reload posts page
							log(`post`, `postAreaController`, "Post updated successfully");
							ctl.clearEditor();
							ctl.closeEditorManually();
							ctl.getPosts();
						} else {
							log(`post`, `postAreaController`, `Post was not updated`);
							ctl.editor.error = (hasEmsg) ? response.data.body.emsg : "Post was not updated";
						}
						break;
					}
					default: {
						var msg = `Unexpected status code ${response.status}`;
						log(`post`, `postAreaController`, msg);
						ctl.editor.error = msg;
						break;
					}
				}
			}
		}).catch((errResponse) => {
			log(`post`, `postAreaController`, `An error occurred: ${JSON.stringify(errResponse)}`);
		});
	};
	// END Controller Functions

	// BEGIN Event Listners
	$scope.$on("createdPost", function (event, args) {
		// Reload all posts on a "createdPost" event, which is signaled by the postCreatorController
		ctl.getPosts();
	});
	// END Event Listners
});

app.controller("postCreatorController", function ($scope, $rootScope, $http, $window) {
	// BEGIN model
	var ctl = $scope;
	$scope.error = "";
	$scope.title = "";
	$scope.content = "";
    $scope.dropzone = null;
	// END model

	// BEGIN Controller Functions
    $(document).ready(function () {
        ctl.configureDropzone();
        console.log("Creator initialized...");
    });
    $scope.configureDropzone = function () {
        var dropzoneElementID = "uploader";
        ctl.dropzone = new Dropzone(`form#${dropzoneElementID}`, {
            "url": "server-side/processimage.php",
            "method": "post",
            "maxFilesize": 7,   // MB
            "paramName": "file",    // name of temp file generated when sending to server
            "createImageThumbnails": true,
            "thumbnailWidth": 100,
            "thumbnailHeight": 100,
            "thumbnailMethod": "contain",
            "maxFiles": 1,
            "acceptedFiles": "image/png,image/jpeg",
            "autoProcessQueue": false,
            "addRemoveLinks": true,
            "previewTemplate": document.querySelector("#tpl").innerHTML,
            "init": function () {
                // Ensure only one file per post
                this.on("addedfile", function (file) {
                    console.log("Added " + JSON.stringify(file.upload.filename));
                    $(".dz-message").addClass("hidden");
                });

                this.on("removedfile", function (file) {
                    console.log("Removed " + JSON.stringify(file.upload.filename));
                    $(".dz-message").removeClass("hidden");
                });

                // Remove the file when upload is complete
                this.on("complete", (file) => {
                    ctl.clearCreator();
                    ctl.closeCreatorManually();
                    this.removeAllFiles();
                    // Broadcast a "createdPost" event to the $rootScope, so that other controllers who need to hear it can react accordingly
                    $rootScope.$broadcast("createdPost");
                    $(".dz-message").removeClass("hidden");
                });
            }
        });
    };
	$scope.launchCreator = function () {
		log("launchCreator", "postCreatorController", "Launching creator...");
		$("#creator").modal("show");
	};
	$scope.clearCreator = function () {
		log("clearCreator", "postCreatorController", "Clearing creator...");
		ctl.error = "";
		ctl.title = "";
		ctl.content = "";
        ctl.dropzone.removeAllFiles();
	};
	$scope.closeCreatorManually = function () {
		log("closeCreatorManually", "postCreatorController", "Closing creator...");
		$("#creator").modal("hide");
	};
	$scope.submitCreatorData = function () {
		var requestBody = {
			"action": "create",
			"data": {
				"token": sessionStorage.getItem("token"),
				"timestamp": Date.now(),
				"title": ctl.title,
				"content": ctl.content,
                "filename": (typeof ctl.dropzone.files[0] === "undefined") ? "" : ctl.dropzone.files[0].upload.filename
			}
		};
		var config = {
			"headers": {
				"Content-Type": "application/json"
			}
		};

		log("submitCreatorData", "postCreatorController", "Submitting new post...");
		$http.post(urls.postViewer, requestBody, config).then((response) => {
			var hasStatus = (typeof response.data.status === "undefined") ? false : true;
			var hasBody = (typeof response.data.body === "undefined") ? false : true;
			var hasNonce = (!hasBody) ? false : (typeof response.data.body.nonce === "undefined") ? false : true;
			var hasEmsg = (!hasBody) ? false : (typeof response.data.body.emsg === "undefined") ? false : true;
			var hasSuccess = (!hasBody) ? false : (typeof response.data.body.success === "undefined") ? false : true;

			log(`post`, `postCreatorController`, `Response received: ${JSON.stringify(response.data)}`);	// debug
			if (!hasStatus || !hasNonce) {
				log(`post`, `loginController`, `Response is incomplete`);
				var msg = (hasEmsg) ? response.data.body.emsg : "Response incomplete; contact the server admin!";
				ctl.error = msg;
			} else if (!checkTimestampNonce(requestBody.data.timestamp, response.data.body.nonce)) {
				// Nonce is not correct; this server I'm connected to could be lying about who they claim they are!
				log(`post`, `postCreatorController`, `expected nonce "${Date.parse(requestBody.data.timestamp)}", received "${response.data.body.nonce}"`);
				ctl.error = "Incorrect Nonce";
			} else {
				switch (response.status) {
					case 200: {
						if (hasSuccess && response.data.body.success === true) {
							log(`post`, `postCreatorController`, `Post created successfully`);
                            ctl.dropzone.processQueue();    // upload image files; the listener in the dropzone modal area will automatically broadcast an event to reload the posts
						} else {
							log(`post`, `postCreatorController`, `Post was not created`);
							ctl.error = (hasEmsg) ? response.data.body.emsg : "Post was not created";
						}
						break;
					}
					default: {
						var msg = `Unexpected status code ${response.status}`;
						log("post", "postCreatorController", msg);
						ctl.error = msg;
						break;
					}
				}
			}
		}).catch((errResponse) => {
			log("post", "postCreatorController", `An error occurred: ${JSON.stringify()}`);
		});
	}
	// END Controller Functions
});
// END Angular Controllers



// END home.js
