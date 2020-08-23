class TimeInput {
	constructor(container) {
		this.container;
		this.name;
		this.value;
		this.input;
		this.dateinput;
		this.timeinput;

		if(container instanceof HTMLElement){
			this.container = container;
		} else {
			throw 'TI:Constructor > container is not an HTMLElement.';
		}

		this.container.innerHTML = document.getElementById('ti-basic').innerHTML;

		this.input = document.getElementById('ti-value');
		this.timeinput = document.getElementById('ti-time');
		this.dateinput = document.getElementById('ti-date');

		this.name = this.container.getAttribute('data-name') || 'timestamp';
		this.value = new Date();
		this.value.setTime(this.container.getAttribute('data-value') * 1000);

		this.input.value = this.value.getTime() / 1000;
		this.input.name = this.name;

		this.dateinput.value = this.value.toISOString().slice(0, 10);
		this.timeinput.value = this.value.toISOString().slice(11, 16);

		this.dateinput.addEventListener('change', () => {this.update()});
		this.timeinput.addEventListener('change', () => {this.update()});
	}

	update() {
		this.value = new Date(this.dateinput.value + 'T' + this.timeinput.value);
		this.input.value = this.value.getTime() / 1000;
	}
}
