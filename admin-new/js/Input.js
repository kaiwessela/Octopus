class Input {
	constructor(elem, form = null) {
		this.elem;
		this.form;


		if(elem instanceof HTMLInputElement){
			this.elem = elem;
		} else if(elem instanceof HTMLSelectElement){
			this.elem = elem;
		} else if(elem instanceof HTMLTextAreaElement){
			this.elem = elem;
		} else {
			throw 'Input(): elem is of an invalid type.';
		}

		if(form === null || form instanceof Form){
			this.form = form;
		} else {
			throw 'Input(): form is of an invalid type.';
		}
	}
}
