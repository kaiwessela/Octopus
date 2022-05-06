class Button {
	constructor(elem, onClick = null) {
		this.elem;
		this.onClick;

		if(elem instanceof HTMLButtonElement){
			this.elem = elem;
		} else {
			throw 'Button(): elem is of an invalid type.';
		}

		this.elem.addEventListener('click', (ev) => { this.click(ev); });

		this.onClick = onClick;
	}

	click(event) {
		event.preventDefault();

		if(this.onClick instanceof Function){
			this.onClick();
		}
	}

	static multi(elems, onClick = null) {
		let buttons = [];

		for(let elem of elems){
			buttons.push(new Button(elem, onClick));
		}
	}
}
