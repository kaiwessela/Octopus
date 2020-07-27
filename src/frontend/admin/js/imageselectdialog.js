class ImageSelectDialog {
	constructor(elem) {
		super.constructor(elem);

		this.firstLoaded;
		this.lastLoaded;
		this.images = [];
		this.imageTemplate = this.elem.querySelector('template.image-template');
		this.loadMoreButton = this.elem.querySelector('button.more');
		this.loadMoreButton.addEventListener('click', this.load);
		this.load();
	}

	load(number) {
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(){
			if(this.readyState == 4 && this.status == 200){

			}
		}
	}

	clickImage() {

	}
}
