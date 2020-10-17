class TimeInput {
	constructor(input) {
		this.input;
		this.elem;
		this.dateElem;
		this.timeElem;

		this.timestamp;


		if(input instanceof HTMLInputElement){
			this.input = input;
		} else {
			throw 'TimeInput::constructor > input is not an HTMLInputElement.';
		}

		this.timestamp = new Date();

		this.elem = document.querySelector('.timeinput.template');
		this.dateElem = document.getElementById('timeinput-date');
		this.timeElem = document.getElementById('timeinput-time');
	}

	invoke() {
		var time = parseInt(this.input.value, 10) * 1000;
		if(!time || time == 0){
			time = Date.now();
		}
		this.timestamp.setTime(time);
		var tzOffset = this.timestamp.getTimezoneOffset();
		this.timestamp.setHours(this.timestamp.getHours() - (tzOffset / 60));

		this.input.parentNode.insertBefore(this.elem, this.input);
		this.input.type = 'hidden';
		this.elem.classList.remove('template');

		this.dateElem.value = this.timestamp.toISOString().slice( 0, 10);
		this.timeElem.value = this.timestamp.toISOString().slice(11, 16);

		this.update();

		this.dateElem.addEventListener('change', () => { this.update() });
		this.timeElem.addEventListener('change', () => { this.update() });
	}

	update() {
		this.timestamp = new Date(this.dateElem.value + 'T' + this.timeElem.value);
		var tzOffset = this.timestamp.getTimezoneOffset();
		this.timestamp.setHours(this.timestamp.getHours() - (tzOffset / 60));

		this.input.value = this.timestamp.getTime() / 1000 + (tzOffset * 60);
	}
}
