class ImageInput {
	constructor(input) {
		this.input;
		this.elem;

		this.image;

		this.picker;
		this.uploader;

		this.buttons = [];


		if(input instanceof HTMLInputElement){
			this.input = input;
		} else {
			throw 'ImageInput::constructor > input is not an HTMLInputElement.';
		}

		this.elem = document.querySelector('.imageinput.template.main').firstElementChild;

		this.picker = new Picker(this);
		this.uploader = new Uploader(this);

		var buttons = this.elem.querySelectorAll('button');
		for(var i = 0; i < buttons.length; i++){
			var button = buttons[i];

			if(button.getAttribute('data-action') == 'clear'){
				button.addEventListener('click', () => { this.clear() });
			} else if(button.getAttribute('data-action') == 'picker'){
				button.addEventListener('click', () => { this.picker.open() });
			} else if(button.getAttribute('data-action') == 'uploader'){
				button.addEventListener('click', () => { this.uploader.open() });
			} else {
				continue;
			}

			this.buttons.push(button);
		}
	}

	invoke() {
		this.load(this.input.value);

		this.input.parentNode.insertBefore(this.elem, this.input);
		this.picker.invoke();
		this.uploader.invoke();

		this.input.type = 'hidden';
	}

	load(id) {
		if(id == null || id == ''){
			id = 'null';
		}

		var request = new XMLHttpRequest();
		request.responseType = 'json';
		request.onreadystatechange = () => {
			if(request.readyState == 4 && request.status == 200){
				this.set(new Image(request.response.result));
			} else {
				this.set(null);
			}
		}
		request.open('GET', '/api/v1/images/' + id, true);
		request.send();
	}

	set(image = null) {
		var emptyElem = this.elem.querySelector('template.empty');
		var filledElem = this.elem.querySelector('template.filled');

		var previewElem = this.elem.querySelector('.preview');

		if(image == null){
			this.image = null;
			this.input.value = '';

			previewElem.innerHTML = emptyElem.innerHTML;
		} else if(typeof image === 'string'){
			this.load(image);
		} else {
			this.image = image;
			this.input.value = this.image.id;

			var html = filledElem.innerHTML.replace(/{{id}}/g, this.image.id);
			html = html.replace(/{{longid}}/g, this.image.longid);
			html = html.replace(/{{extension}}/g, this.image.extension);
			previewElem.innerHTML = html;
		}
	}

	clear() {
		this.set(null);
	}
}
