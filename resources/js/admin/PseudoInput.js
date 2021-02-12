class PseudoInput {
	constructor() {
		this.elem;
		this.input;
		this.value;
		this.state;
		this.type;
		this.setTemplate;
		this.emptyTemplate;
		this.selectModal;
		this.uploadModal;
		this.clearButtons = [];
	}

	bind(elem) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'PseudoInput.bind(): elem is not an HTMLElement';
		}

		this.input = document.getElementById(this.elem.getAttribute('data-for'));
		this.input.parentElement.insertBefore(this.elem, this.input);
		this.input.type = 'hidden';
		this.value = this.input.value;

		this.type = this.elem.getAttribute('data-type');

		this.setTemplate = this.elem.querySelector('template[data-state=set]').innerHTML;
		this.emptyTemplate = this.elem.querySelector('template[data-state=empty]').innerHTML;

		this.selectModal = modals[this.elem.getAttribute('data-selectmodal')];
		this.uploadModal = modals[this.elem.getAttribute('data-uploadmodal')];

		this.selectModal.onSubmit = () => { this.set(this.selectModal.value); };
		this.uploadModal.onSubmit = () => { this.set(this.uploadModal.value); };

		var selectBtns = this.elem.querySelectorAll('[data-action=select]');
		var uploadBtns = this.elem.querySelectorAll('[data-action=upload]');
		var clearBtns = this.elem.querySelectorAll('[data-action=clear]');

		if(selectBtns.length != 0){
			selectBtns.forEach((el) => { this.selectModal.addOpenButton(el) });
		}

		if(uploadBtns.length != 0){
			uploadBtns.forEach((el) => { this.uploadModal.addOpenButton(el) });
		}

		if(clearBtns.length != 0){
			clearBtns.forEach((el) => { this.addClearButton(el) });
		}

		this.set(this.value);
	}

	clear() {
		this.elem.querySelector('.object').innerHTML = this.emptyTemplate;

		this.state = 'clear';
	}

	async set(value) {
		if(value == null || value == ''){
			this.clear();
			return;
		}

		var obj = µ(this.type);
		await obj.pull(value);
		this.elem.querySelector('.object').innerHTML = obj.insertIn(this.setTemplate);
		this.value = value;
		this.input.value = value;

		this.state = 'set';
	}

	addClearButton(button) {
		if(button instanceof HTMLElement){
			button.addEventListener('click', (e) => { e.preventDefault(); this.clear() });
			this.clearButtons.push(button);
		} else {
			throw 'PseudoInput.addClearButton(): button is not an HTMLElement.';
		}
	}
}
