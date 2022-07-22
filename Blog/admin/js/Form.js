class Form {
	constructor(elem) {
		this.elem;
		// this.method;
		// this.action;
		this.inputs = [];
		this.buttons = [];
		this.isInteractive;


		if(elem instanceof HTMLFormElement){
			this.elem = elem;
		} else {
			throw 'Form(): elem is not a HTMLFormElement.';
		}

		for(let control of this.elem.elements){
			if(control instanceof HTMLButtonElement){
				if(!control.dataset.action){
					continue;
				}

				this.buttons.push(new Button(control, this));
			} else if(control instanceof HTMLInputElement || control instanceof HTMLTextAreaElement || control instanceof HTMLSelectElement){
				let input = new Input(control, this);
				this.inputs[input.id] = input;
			}
		}

		for(let ei in this.elem.getElementsByClassName('enhanced-input')){

		}
	}
}
