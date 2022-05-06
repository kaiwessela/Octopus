class EntityPicker extends Dialog {
	constructor(elem) {
		super(elem);

		this.entityList;
		this.onSubmit = () => {};
		this.onCancel = () => {};

		this.entityList = new EntityList(this.elem.dataset.entity, this.elem.querySelector('.entities'));
	}

	open() {
		this.entityList.page(1);
		super.open();
	}

	submit() {
		let id = this.getResult()[0];

		this.onSubmit(this.entityList.getEntityByID(id));
		this.close();
	}
}
