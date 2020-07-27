class Dialog {
	constructor(elem) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'elem is not an element';
		}

		this.openButtons = [];
		this.closeButtons = [];
		this.finishButtons = [];
		this.returnValue;
		this.onFinish = function(){};

		this.closeButtons.push(this.elem.querySelectorAll('button.close'));
		this.finishButtons.push(this.elem.querySelectorAll('button.finish'));
	}

	addOpenButton(button) {
		button.addEventListener('click', this.open);
		this.openButtons.push(button);
	}

	addCloseButton(button) {
		button.addEventListener('click', this.close);
		this.closeButtons.push(button);
	}

	addFinishButton(button) {
		button.addEventListener('click', this.finish);
		this.finishButtons.push(button);
	}

	open() {
		this.elem.classList.add('open');
	}

	close() {
		this.elem.classList.remove('open');
	}

	finish() {
		this.elem.classList.remove('open');
		this.onFinish();
	}
}
