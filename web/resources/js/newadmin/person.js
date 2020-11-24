class Person {
	constructor() {
		this.id;
		this.longid;
		this.name;

		this.is_new;
		this.is_empty;


		this.is_new = false;
		this.is_empty = true;
	}

	pull(identifier) {
		return new Promise((resolve, reject) => {
			if(!this.is_empty){
				throw 'Person.pull(): Person is not empty.';
			}

			if(!identifier instanceof String || identifier.length < 1){
				throw 'Person.pull(): invalid identifier.';
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
			ajax.open('GET', '/api/v1/persons/' + identifier, true);
			ajax.send();
		});
	}

	static pullList(limit = null, offset = null) {
		return new Promise((resolve, reject) => {
			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					console.log(ajax);
					if(ajax.response.response_code == '200 OK'){
						var results = [];
						ajax.response.result.forEach((data) => {
							var person = new Person();
							person.load(data);
							results.push(person);
						});
						resolve(results);
					} else {
						reject(ajax.response.error_message);
					}
				}
			}

			if(Number.isInteger(limit) && limit > 0){
				if(Number.isInteger(offset) && offset > 0){
					ajax.open('GET', '/api/v1/persons?limit=' + limit + '&offset=' + offset, true);
				} else {
					ajax.open('GET', '/api/v1/persons?limit=' + limit, true);
				}
			} else {
				ajax.open('GET', '/api/v1/persons/', true);
			}

			ajax.send();
		});
	}

	load(data) {
		if(!this.is_empty){
			throw 'Person.load(): Person is not empty.';
		}

		this.id = data.id;
		this.longid = data.longid;
		this.name = data.name;

		this.is_new = false;
		this.is_empty = false;
	}

	replace(string) {
		string = string.replace(/{{id}}/g, this.id);
		string = string.replace(/{{longid}}/g, this.longid);
		string = string.replace(/{{name}}/g, this.name);
		return string;
	}
}
