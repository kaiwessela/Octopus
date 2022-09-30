class Application extends DataObject {
	constructor() {
		super();

		this.type;
		this.extension;
		this.title;
		this.description;
		this.copyright;
		this.file;

		super._type = 'Application';
		super._apiname = 'applications';
		super.properties = [
			'id',
			'longid',
			'type',
			'extension',
			'title',
			'description',
			'copyright',
			'alternative',
			'file'
		];
	}

	extractFromElement(elem) {
		return new Promise((resolve, reject) => {
			if(!elem instanceof HTMLElement){
				throw 'Application.extractFromElement(): elem is not an HTMLElement.';
			}

			for(var i = 0; i < elem.children.length; i++){
				if(this.properties.includes(elem.children[i].name)){
					if(elem.children[i].name == 'id'){
						this.id = elem.children[i].value;
						this.isNew = false;
					} else if(elem.children[i].name == 'file'){
						continue;
					} else {
						this[elem.children[i].name] = elem.children[i].value;
					}
				}
			}

			this.isEmpty = false;

			var filereader = new FileReader();
			filereader.onload = () => {
				this.file = filereader.result;
				resolve();
			}

			filereader.onabort = () => {
				reject();
			}

			filereader.readAsDataURL(elem.querySelector('[name=file]').files[0]);
		});
	}
}
