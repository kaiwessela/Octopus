document.addEventListener('DOMContentLoaded', function() {
	document.querySelector('body > header .opener > button').addEventListener('click', function() {
		document.querySelector('body > header').classList.toggle('open');
	});

	document.querySelector('body > header').classList.remove('open');
	window.setTimeout(function() {
		document.querySelector('body > header').classList.remove('notransition');
	}, 500);

});

window.onload = function() {
	var pagination = document.querySelector('.pagination');
	pagination.scrollTo((pagination.scrollWidth - pagination.offsetWidth) / 2, 0);
}
