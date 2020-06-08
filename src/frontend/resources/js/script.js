document.addEventListener('DOMContentLoaded', function() {
	document.querySelector('body > header').classList.remove('open');
	window.setTimeout(function() {
		document.querySelector('body > header').classList.remove('notransition');
	}, 500);
});

document.querySelector('body > header .opener > button').addEventListener('click', function() {
	document.querySelector('body > header').classList.toggle('open');
});
