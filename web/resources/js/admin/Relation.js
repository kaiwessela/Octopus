class Relation {
	constructor() {
		this.elem;
		this.object;
		this.onDelete = function(){};
		this.inputs = [];
	}

	bind(elem) {
		if(elem instanceof HTMLElement){
			this.elem = elem;
		} else {
			throw 'Relation.bind(): elem is not an HTMLElement';
		}

		this.elem.querySelector('button[data-action=remove]').addEventListener('click', (e) => {
			e.preventDefault();
			if(this.elem.getAttribute('data-exists') == 'true'){
				this.elem.querySelector('input.action').value = 'delete';
			} else {
				this.onDelete();
			}
		});

		var restoreBtn = this.elem.querySelector('button[data-action=restore]');
		if(restoreBtn){
			restoreBtn.addEventListener('click', (e) => {
				e.preventDefault();
				if(this.elem.getAttribute('data-edited') == 'true'){
					this.elem.querySelector('input.action').value = 'edit';
				} else {
					this.elem.querySelector('input.action').value = 'ignore';
				}
			});
		}

		this.inputs = this.elem.querySelectorAll('input[data-origval]');
		this.inputs.forEach((input) => {
			input.addEventListener('input', () => {
				this.onInput();
			});
		});
	}

	static loadExisting(elems) {
		var relations = [];

		for(var i = 0; i < elems.length; i++){
			var elem = elems[i];

			if(!elem.classList.contains('relation')){
				continue;
			}

			var relation = new Relation();
			relation.object = {
				id: elem.querySelector('.objectId').value
			};
			relation.bind(elem);
			relations.push(relation);
		}

		return relations;
	}

	createElement(template, number) {
		var dummy = document.createElement('div');
		var html = this.object.insertIn(template);
		dummy.innerHTML = html.replace(/{{i}}/g, number);
		return dummy.firstElementChild;
	}

	onInput() {
		if(this.elem.getAttribute('data-exists') != 'true'){
			return;
		}

		var edited = false;

		this.inputs.forEach((input) => {
			if(input.value != input.getAttribute('data-origval')){
				edited = true;
			}
		});

		if(edited){
			this.elem.querySelector('input.action').value = 'edit';
		} else {
			this.elem.querySelector('input.action').value = 'ignore';
		}
	}
}
