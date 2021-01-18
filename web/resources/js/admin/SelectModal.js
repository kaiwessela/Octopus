class SelectModal extends Modal {
	constructor() {
		super();

		this.type;
		this.template;
		this.pagination;
		this.objectsPerPage;
		this.objects = [];
	}

	async bind(elem) {
		super.bind(elem);

		this.type = this.elem.getAttribute('data-type');
		this.template = this.elem.querySelector('.objects > template').innerHTML;

		var count = await µ(this.type).count(); // TODO check for errors etc.
		this.objectsPerPage = Number(this.elem.getAttribute('data-objectsperpage') || 10);

		this.pagination = new Pagination(this, Math.ceil(count / this.objectsPerPage));
		this.pagination.bind(this.elem.querySelector('.pagination'));
		this.pagination.onPaginate = (page) => { this.loadObjects(page) };
	}

	open() {
		super.open();
		this.pagination.paginate(1);
	}

	submit() {
		var formdata = new FormData(this.elem.querySelector('form'));
		this.value = formdata.get('result');

		super.submit();
	}

	async loadObjects(page = 1) {
		if(!Number.isInteger(page)){
			throw 'SelectModal.loadObjects(): page is not an integer.';
		}

		this.objects.forEach((el) => { el.remove(); });

		var objs = await µ(this.type).pullList(this.objectsPerPage, (page - 1) * this.objectsPerPage);
		objs.forEach((obj) => {
			var dummy = document.createElement('div');
			var html = obj.insertIn(this.template);

			if(this.value == obj.id){
				dummy.innerHTML = html.replace(/{{current}}/g, 'checked');
			} else {
				dummy.innerHTML = html.replace(/{{current}}/g, '');
			}

			var el = dummy.firstElementChild;
			this.objects.push(el);
			this.elem.querySelector('.objects').appendChild(el);
		});
	}
}
