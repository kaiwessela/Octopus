class RelationInput {
	constructor() {
		this.elem;
		this.template;
		this.relations = [];
		this.modal;
		this.unique;
		this.objectIds = [];
	}

	bind(elem) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'RelationInput.bind(): elem is not an HTMLElement';
		}

		this.template = this.elem.querySelector('.objects > template').innerHTML;
		this.modal = modals[this.elem.getAttribute('data-selectmodal')];
		this.unique = (this.elem.getAttribute('data-unique') == 'true');

		this.relations = Relation.loadExisting(this.elem.querySelectorAll('.objects .relation'));
		this.relations.forEach((relation) => {
			if(this.unique){
				this.modal.selectedIds.push(relation.object.id);
				this.modal.disabledIds.push(relation.object.id);
			} else {
				this.modal.markedIds.push(relation.object.id);
			}
		});

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
			var rel = new Relation();
			rel.object = this.modal.objects[id];

			var number = this.relations.length;

			var el = rel.createElement(this.template, number);
			this.elem.querySelector('.objects').appendChild(el);

			rel.bind(el);
			rel.onDelete = () => { this.deleteRelation(number, rel.object.id); };
			this.relations[number] = rel;

			if(this.unique){
				this.modal.selectedIds.push(id);
				this.modal.disabledIds.push(id);
			} else {
				this.modal.markedIds.push(id);
			}
		});
	}

	deleteRelation(number, id) {
		this.relations[number].elem.remove();
		delete this.relations[number];

		for(var i in this.modal.selectedIds){
			if(id == this.modal.selectedIds[i]){
				delete this.modal.selectedIds[i];
				break;
			}
		}

		for(var i in this.modal.disabledIds){
			if(id == this.modal.disabledIds[i]){
				delete this.modal.disabledIds[i];
				break;
			}
		}

		for(var i in this.modal.markedIds){
			if(id == this.modal.markedIds[i]){
				delete this.modal.markedIds[i];
				break;
			}
		}
	}
}
