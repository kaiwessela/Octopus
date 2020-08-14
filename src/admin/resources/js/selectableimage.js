class SelectableImage {
	constructor(data, parent) {
		this.parent;
		this.elem;
		this.id;
		this.longid;
		this.description;

		if(parent instanceof ImageInputPicker){
			this.parent = parent;
		} else {
			throw 'IISI:constructor > parent is not an ImageInputPicker.';
		}

		this.id = data.id;
		this.longid = data.longid;
		this.description = data.description;

		var con = document.createElement('div');
		con.innerHTML = document.getElementById('iit-selectableimage').innerHTML
			.replace('%II.image.longid%', this.longid)
			.replace('%II.image.description%', this.description);
		this.elem = con.firstElementChild;

		this.elem.addEventListener('click', () => {this.select()});
	}

	select() {
		this.parent.submit(this);
	}
}
