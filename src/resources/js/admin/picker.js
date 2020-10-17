class Picker {
	constructor(parent, element = null) {
		this.parent;
		this.elem;
		this.grid;

		this.images = [];

		this.buttons = [];

		if(element == null){
			this.elem = document.querySelector('.imageinput.template.picker').firstElementChild;
		} else if(element instanceof HTMLElement){
			this.elem = element;
		} else {
			throw 'Picker::constructor > element is not an HTMLElement.';
		}

		if(parent instanceof ImageInput){
			this.parent = parent;
		} else {
			throw 'Picker::constructor > parent is not an ImageInput.';
		}

		this.grid = this.elem.querySelector('.grid');

		var buttons = this.elem.querySelectorAll('button');
		for(var i = 0; i < buttons.length; i++){
			var button = buttons[i];

			if(button.getAttribute('data-action') == 'close'){
				button.addEventListener('click', () => { this.close() });
			}

			this.buttons.push(button);
		}
	}

	invoke() {
		this.parent.elem.appendChild(this.elem);
		this.load();
	}

	clear() {
		this.images = [];
		this.grid.innerHTML = '';
	}

	open() {
		this.elem.classList.add('open');
	}

	close() {
		this.elem.classList.remove('open');
	}

	load() {
		this.clear();

		var request = new XMLHttpRequest();
		request.responseType = 'json';
		request.onreadystatechange = () => {
			if(request.readyState == 4 && request.status == 200){
				for(var i = 0; i < request.response.result.length; i++){
					this.images.push(new Image(request.response.result[i]));
				}
				this.loadGrid();
			}
		}
		request.open('GET', '/api/v1/images/', true);
		request.send();
	}

	loadGrid() {
		for(var i = 0; i < this.images.length; i++){
			var image = this.images[i];

			var dummy = document.createElement('div');
			dummy.innerHTML = this.elem.querySelector('template').innerHTML;
			dummy.innerHTML = dummy.innerHTML.replace(/{{id}}/g, image.id);
			dummy.innerHTML = dummy.innerHTML.replace(/{{longid}}/g, image.longid);
			dummy.innerHTML = dummy.innerHTML.replace(/{{extension}}/g, image.extension);
			dummy.firstElementChild.addEventListener('click', (event) => {
				this.close();
				this.parent.set(event.currentTarget.name);
			});

			this.grid.appendChild(dummy.firstElementChild);
		}
	}

}
