class SelectableImage {
	constructor(data, parent) {
		this.parent;
		this.elem;
		this.id;
		this.longid;
		this.description;
		this.extension;

		if(parent instanceof ImageInputPicker){
			this.parent = parent;
		} else {
			throw 'IISI:constructor > parent is not an ImageInputPicker.';
		}

		this.id = data.id;
		this.longid = data.longid;
		this.description = data.description;
		this.extension = data.extension;

		var con = document.createElement('div');
		con.innerHTML = document.getElementById('iit-selectableimage').innerHTML
			.replace(/%II\.image\.longid%/g, this.longid)
			.replace(/%II\.image\.description%/g, this.description)
			.replace(/%II\.image\.extension%/g, this.extension);
		this.elem = con.firstElementChild;

		this.elem.addEventListener('click', () => {this.select()});
	}

	select() {
		this.parent.submit(this);
	}
}
