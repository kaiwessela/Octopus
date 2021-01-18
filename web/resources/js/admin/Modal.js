class Modal {
	constructor() {
		this.name;
		this.elem;
		this.value;
		this.openButtons = [];
		this.closeButtons = [];
		this.submitButtons = [];
		this.onSubmit = function(){ return; };
	}

	bind(elem) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'Modal.bind(): elem is not an HTMLElement';
		}

		this.name = this.elem.getAttribute('data-name');

		var openBtns = document.querySelectorAll('[data-name='+this.name+'] [data-action=open], [data-modal='+this.name+'][data-action=open]');
		var closeBtns = document.querySelectorAll('[data-name='+this.name+'] [data-action=close], [data-modal='+this.name+'][data-action=close]');
		var submitBtns = document.querySelectorAll('[data-name='+this.name+'] [data-action=submit], [data-modal='+this.name+'][data-action=submit]');

		if(openBtns.length != 0){
			openBtns.forEach((el) => { this.addOpenButton(el) });
		}

		if(closeBtns.length != 0){
			closeBtns.forEach((el) => { this.addCloseButton(el) });
		}

		if(submitBtns.length != 0){
			submitBtns.forEach((el) => { this.addSubmitButton(el) });
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
			throw 'Modal.addOpenButton(): button is not an HTMLElement.';
		}
	}

	addCloseButton(button) {
		if(button instanceof HTMLElement){
			button.addEventListener('click', (e) => { e.preventDefault(); this.close() });
			this.closeButtons.push(button);
		} else {
			throw 'Modal.addCloseButton(): button is not an HTMLElement.';
		}
	}

	addSubmitButton(button) {
		if(button instanceof HTMLElement){
			button.addEventListener('click', (e) => { e.preventDefault(); this.submit() });
			this.submitButtons.push(button);
		} else {
			throw 'Modal.addSubmitButton(): button is not an HTMLElement.';
		}
	}
}
