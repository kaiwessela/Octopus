class Dialog {
	constructor(elem) {
		this.elem;
		this.form;
		this.onSubmit = (result) => {};
		this.onCancel = () => {};
		this.cancelButtons;
		this.submitButtons;

		if(elem instanceof HTMLDialogElement){
			this.elem = elem;
		} else {
			throw 'Dialog(): elem is not a HTMLDialogElement.';
		}

		let form = this.elem.querySelector('form');
		if(form instanceof HTMLFormElement){
			this.form = form;
		} else {
			this.form = null;
		}

		this.cancelButtons = Button.multi(this.elem.querySelectorAll('button[data-action=cancel]'), () => { this.cancel(); });
		this.submitButtons = Button.multi(this.elem.querySelectorAll('button[data-action=submit]'), () => { this.submit(); });

		// TODO handle build-in Dialog controls
	}

	open() {
		this.elem.showModal();
	}

	close() {
		this.elem.close();
	}

	cancel() {
		this.close();
		this.onCancel();
	}

	submit() {
		this.onSubmit(this.getResult());
		this.close();
	}

	run() {
		this.open();

		return new Promise((resolve, reject) => {
			this.onSubmit = (result) => { resolve(result); };
			this.onCancel = () => { reject(); };
		});
	}

	getResult() {
		let formdata = new FormData(this.form);
		return formdata.getAll('result');
	}
}
