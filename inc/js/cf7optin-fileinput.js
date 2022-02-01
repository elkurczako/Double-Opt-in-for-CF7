/*	Scripts for Double opt-in for CF7 plugin
*	version: 1.0
*	Override standard display and behaviour of input [type="file"] 
 */
 
 
 docReady(function() {
	cf7optinUploadHandle();
	document.addEventListener('wpcf7mailsent', removeAllFileUploads,true);
 });
 
 
 
 /* fires observer for inputs [type="file"]
*  checks on load if there already is file objects array on input 
*  in case of simple refreshing page with filled form */
function cf7optinUploadHandle() {
	let cf7Form = document.querySelector(".wpcf7-form");
	if (cf7Form !== null) { // we need to change CF7 inputs only 
		var uploadInputs = cf7Form.querySelectorAll("input[type=file]");
		if(uploadInputs.length > 0) {
			for (var i = 0; i < uploadInputs.length; i++) {
				if (uploadInputs[i].hasAttribute("id") === false) uploadInputs[i].setAttribute('id', uploadInputs[i].name);
					
				overwriteDefaultInput(uploadInputs[i]);
				uploadInputs[i].addEventListener('change', displayFileInfo,true);
				var evt = document.createEvent("HTMLEvents");
				evt.initEvent("change", false, true);
				uploadInputs[i].dispatchEvent(evt);
			}
		}
	}
}

/*	Adds "file-upload" class to input [type="file"] labels
*	asumming the labels for input to be higher in the DOM 
*	if there is no such label it will be created
*/
function overwriteDefaultInput(elem) {
	elem.classList.add('overwritten');
	elem.setAttribute('aria-hidden', 'true');
	var elemLabel = elem.parentNode;
	while (elemLabel.tagName !== "FORM") { 
		if (elemLabel.tagName === "LABEL") {
			if (elemLabel.hasAttribute("for") && elemLabel.getAttribute('for') === elem.id) {
				elemLabel.classList.add('file-upload');
				elemLabel.setAttribute('role', 'button');
				addFileRemover(elemLabel, '', false);
				return;
			}
		}
		elemLabel = elemLabel.parentNode;
	}
	if (elemLabel.tagName !== "LABEL") {
		//if no label up the DOM tree then create it
		elemLabel = document.createElement("label");
		elemLabel.innerText = cf7optinInput.LabelDefaultText;
		elemLabel.classList.add('file-upload');
		elemLabel.setAttribute('role', 'button');
		elemLabel.setAttribute('for', elem.id);
		parentSpan = elem.parentNode;
		uberParent = parentSpan.parentNode
		uberParent.replaceChild(elemLabel, parentSpan);
		elemLabel.appendChild(parentSpan);
		addFileRemover(elemLabel, '', false);
	}
	
}	

/*	Changes label text about files and creates reset button
*	Gets file objects selected with input and 
*	adds observer for newly created button
*/
function displayFileInfo() {
	let fileInfo = '';
	let totalSize = 0;
	let cf7Files = this.files;
	if (cf7Files.length === 0) return;
	for (var i = 0; i < cf7Files.length; i++) {
		file = cf7Files[i];
		fileName = file.name;
		fileSize = file.size;
		if (i >= 1) { 
			fileInfo = fileInfo + fileName + " ";
		} else {
			fileInfo = fileInfo + fileName;
		}
		totalSize = totalSize + parseInt(fileSize);
	}
	kilobytes = Math.round(totalSize * 100 / 1024) / 100 + 'KB';
	
	if (Math.round(totalSize / 1024) >= 1024) kilobytes = Math.round(totalSize * 100 / 1024 / 1024) / 100 + 'MB';
	let message = cf7optinInput.SelectedFile + fileInfo + ' ' + cf7optinInput.SelectedSize + kilobytes;
	var node = this.parentNode;
	while (node.tagName !== "LABEL") {
		node = node.parentNode;
	}
	if (node.tagName === "LABEL") {
		node.removeChild(node.firstChild);
		let newText = document.createTextNode(message);
		node.insertBefore(newText, node.firstChild);
		node.classList.add('file-selected');
		node.nextSibling.remove();
		addFileRemover(node, fileInfo, true);
		node.nextSibling.addEventListener('click', removeFileUpload, true); //observer for reset button
		node.focus();
	}
}

/*	Reset button for input [type="file"] 
*	created right next to input down the DOM tree
*/
function addFileRemover(node, fileInfo, arefiles = false) {
	if (node !== null){
		container = node.parentNode;
		const btn = document.createElement("button");
		btn.setAttribute("type", "button");
		if ( arefiles ) {
			btn.innerHTML = '<span aria-hidden="true">' + cf7optinInput.RemoveFile + '</span><span class="sr-only">' + cf7optinInput.RemoveFile + fileInfo + '</span>' ;
			btn.className = 'file-upload';
		} else {
			btn.innerHTML = cf7optinInput.NoFileSelected;
			btn.className = 'nofileselected';
		}
		container.insertBefore(btn, node.nextSibling );
	}
}

/*	Resets FileList object for input  
*	right next to reset button up the DOM tree
*	Removes reset button
*/
function removeFileUpload() {
	let uploadLabel = this.previousSibling;
	let upload = uploadLabel.querySelector("input[type=file]");
	if (upload !== null) {
		upload.value = '';
		uploadLabel.removeChild(uploadLabel.firstChild);
		let newText = document.createTextNode(cf7optinInput.LabelDefaultText);
		uploadLabel.insertBefore(newText, uploadLabel.firstChild);
		uploadLabel.classList.remove('file-selected');
		uploadLabel.focus();
		
		addFileRemover(this, '', false);
		this.remove();
	}
}

/*	Resets all inputs with files
* 	right after succesful form submit and mails sent
*/
function removeAllFileUploads() {
	let labels = document.querySelectorAll('label.file-selected');
	let finput = null ;
	for (var i = 0; i < labels.length; i++) {
		upload = labels[i].querySelector("input[type=file]");
		if (upload !== null) {
			upload.value = '';
			labels[i].removeChild(labels[i].firstChild);
			let newText = document.createTextNode(cf7optinInput.LabelDefaultText);
			labels[i].insertBefore(newText, labels[i].firstChild);
			labels[i].classList.remove('file-selected');
			labels[i].focus();
			
			let remover = labels[i].nextSibling;
			addFileRemover(remover, '', false);
			remover.remove();
		}
	}
}
		