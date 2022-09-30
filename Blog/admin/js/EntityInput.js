class EntityInput {
	constructor(elem, availableEntityPickers) {
		this.elem;
		this.containerElem;
		this.input;
		this.entityPicker;
		this.value;
		this.pickButtons;
		this.clearButtons;
		this.setTemplate;
		this.clearTemplate;

		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'EntityInput(): elem is not a HTMLElement.';
		}

		let input = document.getElementById(this.elem.dataset.for);
		if(input instanceof HTMLInputElement){
			this.input = input;
		} else {
			throw 'EntityInput(): no valid input element found.';
		}

		let entityPicker = availableEntityPickers[this.elem.dataset.entity];
		if(entityPicker instanceof EntityPicker){
			this.entityPicker = entityPicker;
		} else {
			throw 'EntityInput(): no valid EntityPicker found.';
		}

		this.pickButtons = Button.multi(this.elem.querySelectorAll('button[data-action=pick]'), () => { this.openPicker() });
		this.clearButtons = Button.multi(this.elem.querySelectorAll('button[data-action=clear]'), () => { this.clear() });

		this.setTemplate = this.elem.querySelector('template[data-case=set]').innerHTML;
		this.clearTemplate = this.elem.querySelector('template[data-case=clear]').innerHTML;

		this.containerElem = this.elem.querySelector('.container');

		let value = this.input.getAttribute('value');
		if(value == null || value == '' || value == 'undefined'){
			this.clear();
		} else {
			this.firstLoad(value);
		}
	}

	async firstLoad(value) {
		let entity = new Entity(this.entityPicker.entityList.class);
		await entity.pull(value);
		this.set(entity);
	}

	async openPicker() {
		try {
			let result = await this.entityPicker.run();

			this.set(result);
		} catch(e){
			this.clear();
		}
	}

	set(entity) {
		if(!entity instanceof Entity){
			throw 'EntityInput.set(): value must be an Entity.';
		}

		this.containerElem.innerHTML = '';
		entity.insertInto(this.containerElem, this.setTemplate);
		this.value = entity;
		this.input.value = entity.id;
	}

	clear() {
		this.containerElem.innerHTML = this.clearTemplate;
		this.input.value = '';
		this.value = null;
	}
}
