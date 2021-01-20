class MultiSelectModal extends Modal {
	constructor() {
		super();

		this.type;
		this.template;
		this.objectCount;
		this.objectsPerPage;
		this.objects = [];
		this.objectElems = [];
	}

	async bind(elem) {
		super.bind(elem);

		this.type = this.elem.getAttribute('data-type');
		this.template = this.elem.querySelector('.objects > template').innerHTML;
		this.objectsPerPage = Number(this.elem.getAttribute('data-objectsperpage') || 10);

		this.objectCount = await µ(this.type).count();

		this.elem.querySelectorAll('[data-action=loadmore]').forEach((el) => {
			el.addEventListener('click', () => { this.loadMore(); });
		});
	}

	open() {
		super.open();

		if(Object.keys(this.objects).length == 0){
			this.loadMore();
		}
	}

	submit() {
		var formdata = new FormData(this.elem.querySelector('form'));
		this.value = formdata.getAll('result');

		super.submit();
	}

	async loadMore() {
		var objs = await µ(this.type).pullList(this.objectsPerPage, Object.keys(this.objects).length);
		objs.forEach((obj) => {
			var dummy = document.createElement('div');
			dummy.innerHTML = obj.insertIn(this.template);

			var el = dummy.firstElementChild;
			this.objects[obj.id] = obj;
			this.objectElems[obj.id] = el;
			this.elem.querySelector('.objects').appendChild(el);
		});

		if(Object.keys(this.objects).length >= this.objectCount){
			this.elem.querySelector('[data-action=loadmore]').remove();
		}
	}
}
