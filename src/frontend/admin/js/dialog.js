class Dialog {
	constructor(elem = null, openButton = null, closeButton = null, finishButton = null) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			console.error('Dialog::constructor – elem is not an HTMLElement.');
			return;
		}

		if(openButton == null){
			this.openButton = null;
		} else if(openButton instanceof HTMLElement){
			this.openButton = openButton;
			this.openButton.addEventListener('click', this.open);
		} else {
			console.warn('Dialog::constructor – openButton is not an HTMLElement.');
			return;
		}

		if(closeButton == null){
			this.closeButton = null;
		} else if(closeButton instanceof HTMLElement){
			this.closeButton = closeButton;
			this.closeButton.addEventListener('click', this.close);
		} else {
			console.warn('Dialog::constructor – closeButton is not an HTMLElement.');
			return;
		}

		if(finishButton == null){
			this.finishButton = null;
		} else if(finishButton instanceof HTMLElement){
			this.finishButton = finishButton;
			this.finishButton.addEventListener('click', this.finish);
		} else {
			console.warn('Dialog::constructor – finishButton is not an HTMLElement.');
			return;
		}

		this.returnValue;
		this.onFinish = function(){};
	}

	set onFinish(func) {
		if(typeof func === 'function'){
			this.onFinish = func;
		} else {
			console.error('Dialog::set onFinish – func is not a function.');
			return;
		}
	}

	open() {
		this.elem.classList.add('open');
	}

	close() {
		this.elem.classList.remove('open');
		this.returnValue = null;
	}

	finish() {
		this.elem.classList.remove('open');
		this.onFinish();
	}
}
