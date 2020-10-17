class Uploader {
	constructor(parent, element = null) {
		this.parent;
		this.elem;
		this.form;

		this.image;

		this.buttons = [];

		if(element == null){
			this.elem = document.querySelector('.imageinput.template.uploader').firstElementChild;
		} else if(element instanceof HTMLElement){
			this.elem = element;
		} else {
			throw 'Uploader::constructor > element is not an HTMLElement.';
		}

		if(parent instanceof ImageInput){
			this.parent = parent;
		} else {
			throw 'Uploader::constructor > parent is not an ImageInput.';
		}

		this.form = this.elem.querySelector('form');
		this.form.addEventListener('submit', (event) => { this.submit(event) });

		var buttons = this.elem.querySelectorAll('button');
		for(var i = 0; i < buttons.length; i++){
			var button = buttons[i];

			if(button.getAttribute('data-action') == 'close'){
				button.addEventListener('click', () => { this.close() });
			} else if(button.getAttribute('data-action') == 'submit'){
				button.addEventListener('click', (event) => { this.submit(event) });
			}

			this.buttons.push(button);
		}
	}

	invoke() {
		this.parent.elem.appendChild(this.elem);
	}

	open() {
		this.elem.classList.add('open');
	}

	close() {
		this.elem.classList.remove('open');
	}

	cancel() {
		this.form.reset();
		this.close();
	}

	submit(event) {
		event.preventDefault();

		var image = new Image();
		image.longid = this.form.querySelector('input[name=longid]').value;
		image.description = this.form.querySelector('input[name=description]').value;
		image.copyright = this.form.querySelector('input[name=copyright]').value;
		image.imagedata;

		var reader = new FileReader();
		reader.addEventListener('load', () => {
			image.imagedata = reader.result;

			var request = new XMLHttpRequest();
			request.responseType = 'json';
			request.onreadystatechange = () => {
				if(request.readyState == 4 && request.status == 200){
					this.parent.setImage(image);
					this.close();
				}
			}
			request.open('POST', '/api/v1/images/new', true);
			request.setRequestHeader('Content-Type', 'application/json');
			request.send(JSON.stringify(image));

		});
		reader.readAsDataURL(this.form.querySelector('input[name=imagefile]').files[0]);
	}

}
