class UploadModal extends Modal {
	constructor() {
		super();

		this.type;
		this.object;
	}

	bind(elem) {
		super.bind(elem);

		this.type = this.elem.getAttribute('data-type');
		this.object = µ(this.type);
	}

	async submit() {
		await this.object.extractFromElement(this.elem.querySelector('form'));
		var pushresult = await this.object.push();
		this.value = pushresult.id;

		super.submit();
	}
}
