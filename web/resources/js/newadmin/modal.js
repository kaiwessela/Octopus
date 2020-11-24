class Modal {
	constructor(elem) {
		this.elem;
		this.openButtons = [];
		this.closeButtons = [];
		this.submitButtons = [];
		this.value;
		this.onSubmit = function(){ return; };

		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'Modal.constructor(): elem is not an HTMLElement';
		}
	}

	open() {
		this.elem.classList.add('open');
	}

	close() {
		this.elem.classList.remove('open');
	}

	submit() {
		this.onSubmit();
		this.close();
	}

	addOpenButton(button) {
		if(button instanceof HTMLElement){
			button.addEventListener('click', (e) => { e.preventDefault(); this.open() });
			this.openButtons.push(button);
		} else {
			throw 'SelectModal.addOpenButton(): button is not an HTMLElement.';
		}
	}

	addCloseButton(button) {
		if(button instanceof HTMLElement){
			button.addEventListener('click', (e) => { e.preventDefault(); this.close() });
			this.closeButtons.push(button);
		} else {
			throw 'SelectModal.addCloseButton(): button is not an HTMLElement.';
		}
	}

	addSubmitButton(button) {
		if(button instanceof HTMLElement){
			button.addEventListener('click', (e) => { e.preventDefault(); this.submit() });
			this.submitButtons.push(button);
		} else {
			throw 'SelectModal.addSubmitButton(): button is not an HTMLElement.';
		}
	}
}
