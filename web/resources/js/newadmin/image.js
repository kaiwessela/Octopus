class Image {
	constructor() {
		this.id;
		this.longid;
		this.description;
		this.copyright;
		this.data;

		this.isNew = false;
		this.isEmpty = true;
	}

	pull(identifier) {
		return new Promise((resolve, reject) => {
			if(!this.isEmpty){
				throw 'Image.pull(): Image is not empty.';
			}

			if(!identifier instanceof String || identifier.length < 1){
				throw 'Image.pull(): invalid identifier.';
			}

			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					if(ajax.response.response_code == '200 OK'){
						this.load(ajax.response.result);
						resolve();
					} else {
						reject(ajax.response.error_message);
					}
				}
			}
			ajax.open('GET', '/api/v1/images/' + identifier, true);
			ajax.send();
		});
	}

	static pullList(limit = null, offset = null) {
		return new Promise((resolve, reject) => {
			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					if(ajax.response.response_code == '200 OK'){
						var results = [];
						ajax.response.result.forEach((data) => {
							var image = new Image();
							image.load(data);
							results.push(image);
						});
						resolve(results);
					} else {
						reject(ajax.response.error_message);
					}
				}
			}

			if(Number.isInteger(limit) && limit > 0){
				if(Number.isInteger(offset) && offset > 0){
					ajax.open('GET', '/api/v1/images?limit=' + limit + '&offset=' + offset, true);
				} else {
					ajax.open('GET', '/api/v1/images?limit=' + limit, true);
				}
			} else {
				ajax.open('GET', '/api/v1/images/', true);
			}

			ajax.send();
		});
	}

	load(data) {
		if(!this.isEmpty){
			throw 'Image.load(): Image is not empty.';
		}

		this.id = data.id;
		this.longid = data.longid;
		this.description = data.description;
		this.copyright = data.copyright;

		this.isNew = false;
		this.isEmpty = false;
	}

	dbExport() {
		if(this.isEmpty){
			throw 'Image.dbExport(): Image is empty.';
		}

		return {
			id: this.id,
			longid: this.longid,
			description: this.description,
			copyright: this.copyright,
			imagedata: this.data
		}
	}

	async extractFromElement(elem) {
		if(!elem instanceof HTMLElement){
			throw 'Image.extractFromElement(): elem is not an HTMLElement.';
		}

		var attributes = ['id', 'longid', 'description', 'copyright', 'imagedata'];

		var changed = 0;
		for(var i = 0; i < elem.children.length; i++){
			if(attributes.includes(elem.children[i].name)){
				if(elem.children[i].name == 'id'){
					this.id = elem.children[i].value;
					this.isNew = false;
					changed++;
				} else if(elem.children[i].name == 'imagedata'){
					this.data = await this.readFile(elem.children[i]);
					changed++;
				} else {
					this[elem.children[i].name] = elem.children[i].value;
					changed++;
				}
			}
		}

		if(changed > 0){
			this.isEmpty = false;
		}
	}

	readFile(elem) {
		return new Promise((resolve, reject) => {
			var filereader = new FileReader();

			filereader.onload = () => {
				resolve(filereader.result);
			}

			filereader.readAsDataURL(elem.files[0]);
		});
	}

	push() {
		return new Promise((resolve, reject) => {
			if(this.isEmpty){
				throw 'Image.push(): Image is empty.';
			}

			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					if(ajax.response.response_code == '200 OK'){
						resolve();
					} else {
						reject(ajax.response.error_message);
					}
				}
			}

			if(this.isNew){
				ajax.open('POST', '/api/v1/images/new', true);
			} else {
				ajax.open('POST', '/api/v1/images/'+this.id+'/edit', true);
			}

			ajax.send(JSON.stringify(this.dbExport()));
		});
	}
}
