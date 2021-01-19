class RelationInput {
	constructor() {
		this.elem;
		this.template;
		this.relations = [];
		this.modal;
	}

	bind(elem) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'RelationInput.bind(): elem is not an HTMLElement';
		}

		this.template = this.elem.querySelector('.objects > template').innerHTML;
		this.modal = modals[this.elem.getAttribute('data-selectmodal')];

		var selectBtns = this.elem.querySelectorAll('[data-action=select]');
		if(selectBtns.length != 0){
			selectBtns.forEach((el) => { this.modal.addOpenButton(el); });
		}

		this.modal.onSubmit = () => { this.addRelations(this.modal.value); };
	}

	addRelations(ids) {
		if(!Array.isArray(ids)){
			ids = [ids];
		}

		ids.forEach((id) => {
			var dummy = document.createElement('div');
			var html = this.modal.objects[id].insertIn(this.template);
			dummy.innerHTML = html.replace(/{{i}}/g, this.relations.length);

			var el = dummy.firstElementChild;
			this.relations.push(el);
			this.elem.querySelector('.objects').appendChild(el);
		});
	}
}
