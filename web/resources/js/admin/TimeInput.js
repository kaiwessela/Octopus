class TimeInput {
	constructor() {
		this.input;
		this.dateInput;
		this.timeInput;
	}

	bind(elem) {
		if(!elem instanceof HTMLElement){
			throw 'TimeInput.bind(): elem is not an HTMLElement';
		}

		this.input = document.getElementById(elem.getAttribute('data-for'));
		this.dateInput = elem.querySelector('input[type=date]');
		this.timeInput = elem.querySelector('input[type=time]');

		if(this.input.value == null || this.input.value == ''){
			var d = new Date();
			this.input.value = d.toISOString().slice(0,19).replace('T', ' ');
		}

		this.dateInput.value = this.input.value.split(' ')[0];
		this.timeInput.value = this.input.value.split(' ')[1].slice(0,5);

		this.input.parentElement.insertBefore(elem, this.input);
		this.input.type = 'hidden';

		this.dateInput.addEventListener('change', () => { this.update(); });
		this.timeInput.addEventListener('change', () => { this.update(); });
	}

	update() {
		this.input.value = this.dateInput.value + ' ' + this.timeInput.value + ':00';
	}
}
