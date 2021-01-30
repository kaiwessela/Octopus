class Person extends DataObject {
	constructor() {
		super();

		this.name;

		super._type = 'Person';
		super._apiname = 'persons';
		super.properties = [
			'id',
			'longid',
			'name'
		];

		this.grouprelations = [];
	}

	extractFromElement(elem) {
		return new Promise((resolve, reject) => {
			if(!elem instanceof HTMLElement){
				throw 'Person.extractFromElement(): elem is not an HTMLElement.';
			}

			for(var i = 0; i < elem.children.length; i++){
				if(this.properties.includes(elem.children[i].name)){
					if(elem.children[i].name == 'id'){
						this.id = elem.children[i].value;
						this.isNew = false;
					} else {
						this[elem.children[i].name] = elem.children[i].value;
					}
				}
			}

			this.isEmpty = false;

			resolve();
		});
	}
}
