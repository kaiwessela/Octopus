class DataObject {
	constructor() {
		this.id;
		this.longid;

		this.isNew = true;
		this.isEmpty = true;

		this._type = '';
		this._apiname = '';
		this.properties = [];
	}

	load(data) {
		if(!this.isEmpty){
			throw this._type + '.load(): object is not empty.';
		}

		this.properties.forEach((prop) => {
			this[prop] = data[prop];
		});

		this.isNew = false;
		this.isEmpty = false;
	}

	export() {
		if(this.isEmpty){
			throw this._type + '.export(): object is empty.';
		}

		var result = {};
		this.properties.forEach((prop) => {
			result[prop] = this[prop];
		});

		return result;
	}

	insertIn(string) {
		var str = string;

		this.properties.forEach((prop) => {
			str = str.replace(new RegExp('{{'+prop+'}}', 'g'), this[prop]);
		});

		return str;
	}

	pull(identifier) {
		return new Promise((resolve, reject) => {
			if(!this.isEmpty){
				throw this._type + '.pull(): object is not empty.';
			}

			if(!identifier instanceof String || identifier.length < 1){
				throw this._type + '.pull(): invalid identifier.';
			}

			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					if(ajax.response.code == '200 OK'){
						this.load(ajax.response.result);
						resolve();
					} else {
						reject(ajax.response.error.message);
					}
				}
			}
			ajax.open('GET', '/api/v1/'+this._apiname+'/'+identifier, true);
			ajax.send();
		});
	}

	count() {
		return new Promise((resolve, reject) => {
			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					if(ajax.response.code == '200 OK'){
						resolve(ajax.response.result);
					} else {
						reject(ajax.response.error.message);
					}
				}
			}
			ajax.open('GET', '/api/v1/'+this._apiname+'/count', true);
			ajax.send();
		});
	}

	pullList(limit = null, offset = null) {
		return new Promise((resolve, reject) => {
			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					if(ajax.response.code == '200 OK'){
						var results = [];
						ajax.response.result.forEach((data) => {
							var object = µ(this._type);
							object.load(data);
							results.push(object);
						});
						resolve(results);
					} else {
						reject(ajax.response.error.message);
					}
				}
			}

			if(Number.isInteger(limit) && limit > 0){
				if(Number.isInteger(offset) && offset > 0){
					ajax.open('GET', '/api/v1/'+this._apiname+'?limit='+limit+'&offset='+offset, true);
				} else {
					ajax.open('GET', '/api/v1/'+this._apiname+'?limit='+limit, true);
				}
			} else {
				ajax.open('GET', '/api/v1/'+this._apiname, true);
			}

			ajax.send();
		});
	}

	push() {
		return new Promise((resolve, reject) => {
			if(this.isEmpty){
				throw this._type + '.push(): object is empty.';
			}

			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					if(ajax.response.code == '200 OK'){
						resolve(ajax.response.result);
					} else {
						reject(ajax.response.error.message);
					}
				}
			}

			if(this.isNew){
				ajax.open('POST', '/api/v1/'+this._apiname+'/new', true);
			} else {
				ajax.open('POST', '/api/v1/'+this._apiname+'/'+this.id+'/edit', true);
			}

			ajax.setRequestHeader('Content-Type', 'application/json');
			ajax.send(JSON.stringify(this.export()));
		});
	}
}
