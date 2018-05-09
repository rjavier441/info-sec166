/*
    PROJECT:        Infosec 166
    Name:           R. Javier
    File:           utility.js
    Date Created:   May 9, 2017
    Last Modified:  May 9, 2017
    Details:
        This file comsists of all the common utility methods used by all pages (front-end).
    Dependencies:
        JQuery v1.12.4
*/

var DEBUG_VERBOSE = true;

/*
    @function   log
    @parameter  name - the name of the function logging the message
    @parameter  context - a description of the action being logged
    @parameter  msg - the message to log
    @details    This function is used to neatly log debug
                messages when Verbose Debugging is enabled
*/
function log (name, context, msg) {
    if (DEBUG_VERBOSE) {
        console.log("[" + name + "] " + context + ": " + msg);
    }
}

/*
	@function 	storageAvailable
	@parameter 	type - the type of browser storage you want to check for (valid values are "sessionStorage" or "localStorage")
	@returns 	On success: true
				On failure: an error
	@details 	This function was provided by MDN (mozilla developer's network) as an example of how to verify if a browser supports storage
*/
function storageAvailable(type) {
    try {
        var storage = window[type],
            x = '__storage_test__';
        storage.setItem(x, x);
        storage.removeItem(x);
        return true;
    }
    catch(e) {
        return e instanceof DOMException && (
            // everything except Firefox
            e.code === 22 ||
            // Firefox
            e.code === 1014 ||
            // test name field too, because code might not be present
            // everything except Firefox
            e.name === 'QuotaExceededError' ||
            // Firefox
            e.name === 'NS_ERROR_DOM_QUOTA_REACHED') &&
            // acknowledge QuotaExceededError only if there's something already stored
            storage.length !== 0;
    }
}