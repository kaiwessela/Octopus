class ImageInputUploader {
	constructor(parent) {
		this.parent;
		this.elem;
		this.form;
		this.cancelButton;
		this.closeButton;

		if(parent instanceof ImageInput){
			this.parent = parent;
		} else {
			throw 'IIUploader:constructor > parent is not an ImageInput.';
		}

		var con = document.createElement('div');
		con.innerHTML = document.getElementById('iit-uploader').innerHTML || document.createElement('div');
		this.elem = con.firstElementChild;
		document.body.appendChild(this.elem);

		this.form = this.elem.querySelector('form') || document.createElement('form');
		this.cancelButton = document.getElementById('ii-uploader-cancel') || document.createElement('button');
		this.closeButton = document.getElementById('ii-uploader-close') || document.createElement('button');

		this.form.addEventListener('submit', (event) => {this.submit(event)});
		this.cancelButton.addEventListener('click', () => {this.cancel()});
		this.closeButton.addEventListener('click', () => {this.close()});
	}

	open() {
		this.elem.classList.add('open');
	}

	close() {
		this.elem.classList.remove('open');
	}

	cancel() {
		this.elem.querySelector('form').reset();
		this.close();
	}

	submit(event) {
		event.preventDefault();

		var iiu = this;

		var image = {
			longid: this.elem.querySelector('input[name=longid]').value,
			description: this.elem.querySelector('input[name=description]').value,
			copyright: this.elem.querySelector('input[name=copyright]').value
		}

		var reader = new FileReader();
		reader.addEventListener('load', function(){
			image.imagedata = reader.result;

			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = function(){
				if(this.readyState == 4 && this.status == 200){
					iiu.parent.setImage(this.response.result.id, this.response.result.longid, this.response.result.extension);
					iiu.close();
				}
			}
			ajax.open('POST', '/api/v1/images/new', true);
			ajax.setRequestHeader('Content-Type', 'application/json');
			ajax.send(JSON.stringify(image));

		});
		reader.readAsDataURL(this.elem.querySelector('input[name=imagefile]').files[0]);
	}
}
