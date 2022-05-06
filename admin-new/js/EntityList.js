class EntityList extends Array {
	constructor(entityClass, containerElem = null) {
		super();

		this.class = entityClass;
		this.pulledPage;
		this.pulledAmount;
		this.totalEntitiesAvailable;
		this.totalPagesAvailable;
		this.containerElem;
		this.entityTemplate;

		if(containerElem instanceof HTMLElement){
			this.containerElem = containerElem;

			if(this.containerElem.querySelector('template')){
				this.entityTemplate = this.containerElem.querySelector('template').innerHTML;
			}
		}
	}

	pull(page = 1, amount = 20) {
		return new Promise((resolve, reject) => {
			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState === 4){
					if(ajax.status === 200){
						for(let data of ajax.response.result){
							let entity = new Entity(this.class);
							entity.load(data);
							this.push(entity);
						}

						this.pulledPage = page;
						this.pulledAmount = amount;
						this.totalEntitiesAvailable = ajax.response.total_available;

						resolve();
					} else {
						console.error('EntityList.pull() failed.', ajax);
						reject();
					}
				}
			}

			ajax.open('GET', '/api/v1/'+this.class+'s?amount='+amount+'&page='+page, true);
			ajax.send();
		});
	}

	clear() {
		for(let entity of this){
			this[i].remove();
		}

		this.length = 0;
	}

	async page(page) {
		if(this.length !== 0){
			this.clear();
		}

		await this.pull(page, this.pulledAmount || 20);

		if(this.containerElem){
			for(let entity of this){
				entity.insertInto(this.containerElem, this.entityTemplate || '');
			}
		}
	}

	nextPage() {
		this.page(this.pulledPage + 1);
	}

	prevPage() {
		this.page(this.pulledPage - 1);
	}

	getEntityByID(id) {
		for(let entity of this){
			if(entity.id === id){
				return entity;
			}
		}

		throw 'EntityList.getEntityByID(): entity «'+id+'» not found.';
	}
}
