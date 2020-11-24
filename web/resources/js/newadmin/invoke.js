var modals = [];

function registerButton(button, modalName = null) {
	if(modalName == null){
		modalName = button.getAttribute('data-modal');
	}

	if(button.getAttribute('data-action') == 'open'){
		modals[modalName].addOpenButton(button);
	} else if(button.getAttribute('data-action') == 'close'){
		modals[modalName].addCloseButton(button);
	} else if(button.getAttribute('data-action') == 'submit'){
		modals[modalName].addSubmitButton(button);
	}
}

document.querySelectorAll('.modal').forEach((elem) => {
	modalName = elem.getAttribute('data-name');
	if(modalName == null){
		return;
	}

	if(elem.classList.contains('selectmodal')){
		modals[modalName] = new SelectModal(elem);
		modals[modalName].setForm(elem.querySelector('form'));
		modals[modalName].setTemplate(elem.querySelector('template'));
		modals[modalName].setForm(elem.querySelector('form'));
		modals[modalName].setObjectBox(elem.querySelector('.objectbox'));
	}

	elem.querySelectorAll('[data-action]').forEach((button) => {registerButton(button, modalName)});
});

document.querySelectorAll('[data-action][data-modal]').forEach((button) => {registerButton(button)});
