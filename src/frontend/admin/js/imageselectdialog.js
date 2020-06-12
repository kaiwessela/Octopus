class ImageSelectDialog extends Dialog {
	constructor(elem = null, openButton = null) {
		if(elem instanceof HTMLElement) {
			var closeButton = elem.querySelector('.close');
			var finishButton = elem.querySelector('.finish');
		}

		super.constructor(elem, openButton, closeButton, finishButton);

		this.moreButton = this.elem.querySelector('.more');
		this.moreButton.addEventListener('click', this.more);

		var templateList = this.elem.querySelectorAll('.masonry > template');

		this.showCount = 0;
		this.images = [];
		for(var i = 0; i < templateList.length; i++){
			this.images[] = new SelectableImage(templateList[i]);
		}

	}

	more() {

	}
}

class SelectableImage {
	constructor(elem) {
		
	}
}
