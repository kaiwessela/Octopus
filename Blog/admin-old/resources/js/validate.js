var inputs = document.querySelectorAll('input, textarea');
inputs.forEach(input => {
	input.addEventListener('input', (event) => {
		if(input.checkValidity()){
			input.classList.remove('tooshort');
			input.classList.remove('invalid');
			input.classList.add('valid');
		}

		if(input.value == ''){
			input.classList.remove('valid');
		}
	});

	input.addEventListener('invalid', (event) => {
		if(input.validity.valueMissing){
			event.preventDefault();
			input.classList.remove('valid');
			input.classList.remove('invalid');
			input.classList.remove('tooshort');
		} else if(input.validity.tooShort && !input.validity.patternMismatch){
			input.classList.add('tooshort');
			input.classList.remove('valid');
			input.classList.remove('invalid');
		} else {
			input.classList.add('invalid');
			input.classList.remove('valid');
			input.classList.remove('tooshort');
		}
	});
});
