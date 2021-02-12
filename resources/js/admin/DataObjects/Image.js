class Image extends DataObject {
	constructor() {
		super();

		this.extension;
		this.description;
		this.copyright;
		this.imagedata;

		super._type = 'Image';
		super._apiname = 'images';
		super.properties = [
			'id',
			'longid',
			'extension',
			'description',
			'copyright',
			'imagedata'
		];
	}

	extractFromElement(elem) {
		return new Promise((resolve, reject) => {
			if(!elem instanceof HTMLElement){
				throw 'Image.extractFromElement(): elem is not an HTMLElement.';
			}

			for(var i = 0; i < elem.children.length; i++){
				if(this.properties.includes(elem.children[i].name)){
					if(elem.children[i].name == 'id'){
						this.id = elem.children[i].value;
						this.isNew = false;
					} else if(elem.children[i].name == 'imagedata'){
						continue;
					} else {
						this[elem.children[i].name] = elem.children[i].value;
					}
				}
			}

			this.isEmpty = false;

			var filereader = new FileReader();
			filereader.onload = () => {
				this.imagedata = filereader.result;
				resolve();
			}

			filereader.onabort = () => {
				reject();
			}

			filereader.readAsDataURL(elem.querySelector('[name=imagedata]').files[0]);
		});
	}
}
