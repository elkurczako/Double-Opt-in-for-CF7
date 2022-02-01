/* Scripts for Double opt-in for CF7 plugin
*  version: 1.0
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

// After DOM is fully loaded ***************************************
 
 docReady(function() {
	copyConfirmationEmail();
	addMissingAttributes();
 });
 
 //adds required attribute to elements with "cf7req" class
 //adds pattern to fields identified as telephone numbers
 function addMissingAttributes() {
	 var $requiredInputs = document.querySelectorAll('.cf7req');
	 if ($requiredInputs !== null) {
		 for (var i = 0; i < $requiredInputs.length; i++) {
			 $requiredInputs[i].setAttribute('required', 'true');
		 }
	 }
	 var tel_fields = document.querySelectorAll('[autocomplete="tel"]');
	if (tel_fields !== null) {
		for (var i = 0; i < tel_fields.length; i++) {
			tel_fields[i].setAttribute('pattern', '^[- +()]*[0-9][- +()0-9]*$');
		}
	}
 }
 
 //copies confirmation email value from email field
 function copyConfirmationEmail() {
	const confirmfield = document.querySelector('.wpcf7-form #confirm-email'); 
	 if (confirmfield !== null) {
		const emailfield = document.querySelector('#your-email');
		 emailfield.addEventListener('blur', function(){copyEmailValue(emailfield, confirmfield);}, false);
		 confirmfield.addEventListener('blur', function(){checkIfConfirmChanged(emailfield, confirmfield);}, false);
		 emailfield.addEventListener('keyup',  function(){ clearWarnings(emailfield);}, false);
		 confirmfield.addEventListener('keyup', function(){ clearWarnings(confirmfield);}, false);
	 }
 }

// Clearing error messages while user input 
function clearWarnings(field) {
	let tips = field.parentNode.getElementsByClassName('wpcf7-not-valid-tip');
	while (tips[0]) {
		tips[0].parentNode.removeChild(tips[0]);
	}
	field.setCustomValidity('');
	field.setAttribute('aria-invalid', 'false');
}
	
// Copies email value to confirmation field and validates
function copyEmailValue(previousel, currentel) {
	clearWarnings(currentel);
	if (previousel.value !== null && previousel.value !== '') {
		let isEmail = checkEmailValue(previousel.value);
		//currentel.value = previousel.value;
		if (isEmail === 'notemail') {
			if (previousel.validationMessage !== "invalidemail") {
				displayNotValidTip(previousel, 'notemail');
			}
			if (currentel.validationMessage !== "invalidemail") {
				displayNotValidTip(currentel, 'notemail');
			}
		}
	}
}

// Checking if confirmation email changed
function checkIfConfirmChanged(previousel, currentel) {
	
	if (previousel.value !== currentel.value) {
		// display error and links between inputs 
		if (currentel.validationMessage !== "different") {
			displayNotValidTip(currentel, 'diffb');
		}
		if (previousel.validationMessage !== "different") {
			displayNotValidTip(previousel, 'diffa');
		}
		checkEmailUWWDomain(currentel.value);
	}
}
// Checking if input value is a valid email address
function checkEmailValue(val) {
	let result = '';
	const re = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
	if (re.test(val) === false) {
		result = 'notemail';
		return result;
	}
}
// Creates error message span with classes used in CF7
function displayNotValidTip(elem, msg) {
	const elemContainer = elem.parentNode;
	const tipspan = document.createElement("span");
	let warning = cf7optinWarning.DefaultlWarning;
	switch (msg) {
		case 'diffb':
		warning = cf7optinWarning.SecondEmailWarning;
		elem.setCustomValidity('different');
		elem.setAttribute('aria-invalid', 'true');
		break;
		case 'diffa':
		warning = cf7optinWarning.FirstEmailWarning;
		elem.setCustomValidity('different');
		elem.setAttribute('aria-invalid', 'true');
		break;
		case 'notemail':
		warning = cf7optinWarning.NotEmailWarning;
		elem.setCustomValidity('invalidemail');
		elem.setAttribute('aria-invalid', 'true');
		break;
	}
	tipspan.innerHTML = warning;
	tipspan.className = 'wpcf7-not-valid-tip';
	tipspan.setAttribute("role", "alert");
	elemContainer.appendChild(tipspan);
	elemContainer.classList.add('wpcf7-not-valid');
}
	