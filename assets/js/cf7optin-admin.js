/* Double opt-in for CF7 admin scripts
*/

//Handles DOM loading without jQuery
(function(funcName, baseObj) {
    "use strict";    
    funcName = funcName || "docReady";
    baseObj = baseObj || window;
    var readyList = [];
    var readyFired = false;
    var readyEventHandlersInstalled = false;
   
    function ready() {
        if (!readyFired) {
            readyFired = true;
            for (var i = 0; i < readyList.length; i++) {
                readyList[i].fn.call(window, readyList[i].ctx);
            }
            readyList = [];
        }
    }
    
    function readyStateChange() {
        if ( document.readyState === "complete" ) {
            ready();
        }
    }
    
    baseObj[funcName] = function(callback, context) {
        if (typeof callback !== "function") {
            throw new TypeError("callback for docReady(fn) must be a function");
        }
        if (readyFired) {
            setTimeout(function() {callback(context);}, 1);
            return;
        } else {
            readyList.push({fn: callback, ctx: context});
        }
        if (document.readyState === "complete" || (!document.attachEvent && document.readyState === "interactive")) {
            setTimeout(ready, 1);
        } else if (!readyEventHandlersInstalled) {
            if (document.addEventListener) {
                document.addEventListener("DOMContentLoaded", ready, false);
                window.addEventListener("load", ready, false);
            } else {
                document.attachEvent("onreadystatechange", readyStateChange);
                window.attachEvent("onload", ready);
            }
            readyEventHandlersInstalled = true;
        }
    }
})("docReady", window);

docReady(function() {
	cf7optinMaker();
	cf7optinHandleKeys();
 });

const optinUrl = '[_site_url]/opt-in?aid={{[_serial_number]}}&aem={{[your-email]}}';

// Selects clicked shortcode
function cf7optinSelectNode(elem){
	let range = document.createRange();
	range.selectNodeContents( elem );
	window.getSelection().addRange( range );
}

//handles "Make this double opt-in" button 
function cf7optinMaker() {
	let  email = document.getElementById('wpcf7-mail-body');
	if (email !== null) {
		var cf7optinBtn = document.querySelector('#cf7optin-maker');
		var cf7optinMsg = document.querySelector('.cf7optin-cf7-notice');
		
		if (email.value.indexOf(optinUrl) === -1) {
			cf7optinBtn.addEventListener('click', cf7optinFillTheForm, true);
		} else {
			let msg = cf7optinAdminText.OptInEnabled;
			cf7optinMsg.innerHTML = '<p><Strong>' + msg + '</strong></p>';
		}
	}
}

//fills CF7 form with values essential to double opt in
function cf7optinFillTheForm(){
	const title = document.getElementById('title');
	if (title.value === "") {
		let msg = cf7optinAdminText.EnterTitle;
		window.alert(msg);
		return;
	}
	const form = document.getElementById('wpcf7-form');
	const email = document.getElementById('wpcf7-mail-body');
	const recipient = document.getElementById('wpcf7-mail-recipient');
	const flamingo = document.getElementById('wpcf7-additional-settings');
	
	let confirmMsg = cf7optinAdminText.ConfirmEmail;
	let optinMsg = cf7optinAdminText.FinalizeSubmission;
	form.value += '\r\n' + '<label>' + confirmMsg + '[confirm-email]</label>';
	email.value += '\r\n' + optinMsg + optinUrl;
	recipient.value = '[your-email]';
	flamingo.value = 'flamingo_email: "[your-email]"' + '\r\n' + 'flamingo_subject: "' + title.value +'"';
	let msg = cf7optinAdminText.FormUpdated;
	window.alert(msg);
}

//handles "Copy encryption keys" button
function cf7optinHandleKeys() {
	var copyKeyBtn = document.querySelector('#cf7optin-copy-keys');
	if (copyKeyBtn !== null) {
		copyKeyBtn.addEventListener('click', cf7optinCopyEncryptKeys, true);
	}
}
// Copies auto generated keys to inputs
function cf7optinCopyEncryptKeys () {
	var newkey = document.getElementsByClassName('cf7-optin-shortcode');
	if (newkey.length > 0) {
		let enckey = document.querySelector('#cf7optin-enc-key');
		let ivkey = document.querySelector('#cf7optin-enc-iv');
		if (enckey.value !== '') window.alert(cf7optinAdminText.KeyNotEmpty);
		enckey.value = newkey[0].innerHTML;
		ivkey.value = newkey[1].innerHTML;
		window.alert(cf7optinAdminText.KeysCopied);
	}
}