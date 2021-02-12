class ListInput {
	constructor() {
		this.elem;
		this.itemContainer;
		this.template;
	}

	bind(elem) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'ListInput.bind(): elem is not an HTMLElement';
		}

		this.itemContainer = this.elem.querySelector('.items');
		this.template = this.elem.querySelector('template').innerHTML;

		this.elem.querySelector('button[data-action=add]').addEventListener('click', (e) => {
			e.preventDefault();
			this.addItem();
		});

		var removeBtns = this.elem.querySelectorAll('.items button[data-action=remove]')
		if(removeBtns.length != 0){
			removeBtns.forEach((el) => {
				el.addEventListener('click', (e) => {
					e.preventDefault();
					this.removeItem(el.getAttribute('data-number'));
				});
			});
		}
	}

	addItem() {
		var number = this.itemContainer.childNodes.length+1;
		var dummy = document.createElement('tbody');
		dummy.innerHTML = this.template.replace(/{{i}}/g, number);

		var el = dummy.firstElementChild;
		el.querySelector('button[data-action=remove]').addEventListener('click', (e) => {
			e.preventDefault();
			this.removeItem(number);
		})

		this.itemContainer.appendChild(el);
	}

	removeItem(number) {
		this.itemContainer.querySelectorAll('.item').forEach((item) => {
			if(item.getAttribute('data-number') == number){
				item.remove();
			}
		});
	}

}
