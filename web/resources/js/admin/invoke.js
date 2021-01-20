var modals = [];
var pseudoinputs = [];
var relationinputs = [];
var timeinputs = [];

document.querySelectorAll('.nojs').forEach((el) => { el.classList.remove('nojs'); });

document.querySelectorAll('.modal').forEach((el) => {
	var modalName = el.getAttribute('data-name');
	if(modalName == null){
		return;
	}

	if(el.classList.contains('selectmodal')){
		modals[modalName] = new SelectModal();
	} else if(el.classList.contains('multiselectmodal')){
		modals[modalName] = new MultiSelectModal();
	} else if(el.classList.contains('uploadmodal')){
		modals[modalName] = new UploadModal();
	} else {
		modals[modalName] = new Modal();
	}

	modals[modalName].bind(el);
});

document.querySelectorAll('.pseudoinput').forEach((el) => {
	var name = el.getAttribute('data-for');
	if(name == null){
		return;
	}

	pseudoinputs[name] = new PseudoInput();
	pseudoinputs[name].bind(el);
});

document.querySelectorAll('.relationinput').forEach((el) => {
	var name = el.getAttribute('data-for');
	if(name == null){
		return;
	}

	relationinputs[name] = new RelationInput();
	relationinputs[name].bind(el);
});

document.querySelectorAll('.timeinput').forEach((el) => {
	var name = el.getAttribute('data-for');
	if(name == null){
		return;
	}

	timeinputs[name] = new TimeInput();
	timeinputs[name].bind(el);
});
