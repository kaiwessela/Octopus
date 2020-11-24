class SelectModal extends Modal {
	constructor(elem) {
		super(elem);

		this.type;
		this.objects = [];
		this.objectBox;
		this.form;
		this.template;
		this.valueObject;
	}

	open() {
		super.open();

		if(this.objects.length == 0){
			this.loadObjects();
		}
	}

	submit() {
		var formdata = new FormData(this.form);
		this.value = formdata.get('result');

		this.objects.forEach((object) => {
			if(this.value == object.id){
				this.valueObject = object;
			}
		});

		super.submit();
	}

	async loadObjects(page = 1) {
		if(!Number.isInteger(page)){
			throw 'SelectModal.loadObjects(): page is not an integer.';
		}

		if(this.type == 'post'){
			this.objects = await Post.pullList(10, (page - 1) * 10);
		} else if(this.type == 'column'){
			this.objects = await Column.pullList(10, (page - 1) * 10);
		} else if(this.type == 'person'){
			this.objects = await Person.pullList(10, (page - 1) * 10);
		} else {
			throw 'SelectModal.loadObjects(): invalid type.';
		}

		this.objectBox.innerHTML = '';
		this.objects.forEach((object) => {
			if(this.value == object.id){
				this.objectBox.innerHTML += object.replace(this.template.innerHTML).replace(/{{selected}}/g, 'checked');
			} else {
				this.objectBox.innerHTML += object.replace(this.template.innerHTML).replace(/{{selected}}/g, '');
			}
		});
	}

	setObjectBox(elem) {
		if(elem instanceof HTMLElement){
			this.objectBox = elem;
		} else {
			throw 'SelectModal.setObjectBox(): elem is not an HTMLElement.';
		}
	}

	setTemplate(elem) {
		if(elem instanceof HTMLElement){
			this.template = elem;
		} else {
			throw 'SelectModal.setTemplate(): elem is not an HTMLElement.';
		}
	}

	setForm(elem) {
		if(elem instanceof HTMLElement){
			this.form = elem;
		} else {
			throw 'SelectModal.setForm(): elem is not an HTMLElement.';
		}
	}
}
