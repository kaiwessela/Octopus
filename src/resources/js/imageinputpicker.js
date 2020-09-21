class ImageInputPicker {
	constructor(parent) {
		this.parent;
		this.elem;
		this.grid;
		this.images = [];
		this.cancelButton;
		this.closeButton;

		if(parent instanceof ImageInput){
			this.parent = parent;
		} else {
			throw 'IIPicker:constructor > parent is not an ImageInput.';
		}

		var con = document.createElement('div');
		con.innerHTML = document.getElementById('iit-picker').innerHTML;
		this.elem = con.firstElementChild;
		document.body.appendChild(this.elem);

		this.grid = this.elem.querySelector('.grid') || document.createElement('div');
		this.cancelButton = document.getElementById('ii-picker-cancel') || document.createElement('button');
		this.closeButton = document.getElementById('ii-picker-close') || document.createElement('button');

		this.cancelButton.addEventListener('click', () => {this.cancel()});
		this.closeButton.addEventListener('click', () => {this.close()});
	}

	loadImages() {
		var iip = this;
		var ajax = new XMLHttpRequest();
		ajax.responseType = 'json';
		ajax.onreadystatechange = function(){
			if(this.readyState == 4 && this.status == 200){
				for(var i = 0; i < this.response.result.length; i++){
					iip.addImage(new SelectableImage(this.response.result[i], iip));
				}
			}
		}
		ajax.open('GET', '/api/v1/images?limit=10&offset='+iip.images.length, true);
		ajax.send();

	}

	addImage(image) {
		this.images.push(image);
		this.grid.appendChild(image.elem);
	}

	clearImages() {
		this.images = [];
		this.grid.innerHTML = '';
	}

	open() {
		this.elem.classList.add('open');
		if(this.images.length == 0){
			this.loadImages();
		}
	}

	close() {
		this.elem.classList.remove('open');
	}

	cancel() {
		this.close();
		this.clearImages();
	}

	submit(image) {
		this.parent.setImage(image.id, image.longid, image.extension);
		this.close();
	}
}
