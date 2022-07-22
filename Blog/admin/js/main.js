for(let jsel of document.querySelectorAll('.jsonly, [data-jsonly]')){
	jsel.classList.remove('jsonly');
}

for(let nojs of document.querySelectorAll('.nojs, [data-nojs]')){
	nojs.classList.add('nojs-off');
	nojs.classList.remove('nojs');
}

var entityPickers = [];

for(let dialog of document.querySelectorAll('dialog.entity-picker')){
	let picker = new EntityPicker(dialog);
	entityPickers[picker.entityList.class] = picker;
}

var forms = [];

for(let form of document.querySelectorAll('main > form')){
	forms.push(new Form(form));
}

var entityInputs = [];

for(let ei of document.getElementsByClassName('entity-input')){
	entityInputs.push(new EntityInput(ei, entityPickers));
}
