class Entity {
	constructor(entityClass) {
		this.elem;
		this.class = entityClass;
		this.id;

	}

	load(data) {
		if(!data.id){
			console.debug(data);
			throw 'Entity.load(): invalid entity data.';
		}

		for(let attr in data){
			if(attr === 'class' || attr === 'elem'){
				attr = '_'+attr;
			}

			this[attr] = data[attr];
		}
	}

	pull(identifier, identifyBy = 'id') {
		return new Promise((resolve, reject) => {


			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState === 4){
					if(ajax.status === 200){
						this.load(ajax.response.result);
						resolve();
					} else {
						console.error('Entity.pull() failed.', ajax);
						reject(ajax.status);
					}
				}
			}

			ajax.open('GET', '/api/v1/'+this.class+'s/'+identifier+'?by='+identifyBy, true);
			ajax.send();
		});
	}

	insertInto(container, template) {
		template = Entity.fillTemplate(template, this);

		let dummy = document.createElement('div');
		dummy.innerHTML = template;
		this.elem = dummy.firstElementChild;

		container.appendChild(this.elem);
	}

	static fillTemplate(template, data, prefix = '') {
		if(data instanceof Array){
			for(let attr of data){
				let newPrefix;
				if(prefix === ''){
					newPrefix = attr;
				} else {
					newPrefix = prefix+'.'+attr;
				}

				template = this.fillTemplate(template, data[attr], newPrefix);
			}
		} else if(data instanceof Object){
			for(let attr of Object.keys(data)){
				let newPrefix;
				if(prefix === ''){
					newPrefix = attr;
				} else {
					newPrefix = prefix+'.'+attr;
				}

				template = this.fillTemplate(template, data[attr], newPrefix);
			}
		} else {
			template = template.replace('{'+prefix+'}', data);
		}

		template = template.replace('data-nopreload-', '');

		return template;
	}

	remove() {
		console.log(this.elem);

		if(this.elem === null){
			return;
		}

		this.elem.remove();
	}


}
