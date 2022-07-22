class Pagination {
	constructor(parent, totalPages) {
		this.parent = parent;
		this.elem;
		this.template;
		this.items = [];
		this.totalPages = totalPages;
		this.currentPage;
		this.onPaginate = function(page){ return; };
	}

	bind(elem) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'Pagination.bind(): elem is not an HTMLElement';
		}

		this.template = this.elem.querySelector('template').innerHTML;

		for(var i = 1; i <= this.totalPages; i++){
			this.addItem(i);
		}
	}

	paginate(page) {
		if(Number.isInteger(this.currentPage)){
			this.items[this.currentPage].classList.remove('current');
		}

		this.items[page].classList.add('current');
		this.currentPage = page;
		this.onPaginate(page);
	}

	addItem(page) {
		var dummy = document.createElement('div');
		dummy.innerHTML = this.template.replace(/{{page}}/g, page);
		dummy.firstElementChild.addEventListener('click', () => { this.paginate(page); });
		this.items[page] = dummy.firstElementChild;
		this.elem.appendChild(this.items[page]);
	}
}
