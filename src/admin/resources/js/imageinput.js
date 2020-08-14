class ImageInput {
	constructor(container) {
		this.container;
		this.name; // field name for sending the image id to php backend
		this.value;
		this.longid;
		this.input;
		this.clearButton;
		this.pickButton;
		this.uploadButton;
		this.imagebox;
		this.picker;
		this.uploader;

		if(container instanceof HTMLElement){
			this.container = container;
		} else {
			throw 'II:constructor > container is not an HTMLElement.';
		}

		this.container.innerHTML = document.getElementById('iit-basic').innerHTML;
		this.input = document.getElementById('ii-value');
		this.imagebox = this.container.querySelector('.ii-imagebox');

		this.clearButton = document.getElementById('ii-basic-clear') || document.createElement('button');
		this.pickButton = document.getElementById('ii-basic-pick') || document.createElement('button');
		this.uploadButton = document.getElementById('ii-basic-upload') || document.createElement('button');

		this.clearButton.addEventListener('click', () => {this.clear()});
		this.pickButton.addEventListener('click', () => {this.openPicker()});
		this.uploadButton.addEventListener('click', () => {this.openUploader()});

		this.name = this.container.getAttribute('data-name') || 'image_id';
		var value = this.container.getAttribute('data-value') || null;
		var longid = this.container.getAttribute('data-longid') || null;
		this.setImage(value, longid);
		this.setName(this.name);

		this.uploader = new ImageInputUploader(this);
		this.picker = new ImageInputPicker(this);
	}

	clear() {
		this.setImage(null, null);
	}

	setImage(id, longid) {
		if(!id || !longid){
			this.value = null;
			this.longid = null;
			this.clearButton.disabled = true;
			this.imagebox.innerHTML = document.getElementById('iit-imagebox-empty').innerHTML;
		} else {
			this.value = id;
			this.longid = longid;
			this.clearButton.disabled = false;
			this.imagebox.innerHTML = document.getElementById('iit-imagebox-filled').innerHTML
				.replace(/%II\.image\.longid%/g, this.longid);
		}

		this.input.value = id;
	}

	setName(name) {
		this.name = name;
		this.input.name = name;
	}

	openPicker() {
		this.closeUploader();
		this.picker.open();
	}

	openUploader() {
		this.closePicker();
		this.uploader.open();
	}

	closePicker() {
		this.picker.close();
	}

	closeUploader() {
		this.uploader.close();
	}
}
