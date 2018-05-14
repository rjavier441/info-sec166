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

/*
    @function   checkTimestampNonce
    @parameter  ref - the client's reference timestamp to compare to the nonce; any number of milliseconds since Unix Epoch
    @parameter  nonce - a timestamp from the server; any number of milliseconds since Unix Epoch
    @returns    On valid nonce: true
                On invalid nonce: false
    @details    This function validates a timestamp-based nonce from the server, verifying that the responding party is the intended recipient of your message. The premise is that you sent a NONCE of the current timestamp that YOU (the client) know. This acts as a challenge to the server. If you are using HTTPS, the PKI was used to establish a shared secret symmetric key, and that key is used by the client and server to encrypt/decrypt. Thus, communication between client and server is secure and cannot be eavesdropped. If the server responds back with the appropriate timestamp consecutively after yours (i.e. timestamp + 1), you can know that you are indeed talking to the intended the server, since only they can decrypt your message and change respond back with the consecutive timestamp. This prevents replays from happening
*/
function checkTimestampNonce (ref, nonce) {
    return nonce === ref + 1;
}
