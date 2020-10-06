<div class="timeinput template">
	<label for="timeinput-date">
		<small>Datum:</small>
		<input type="date" id="timeinput-date">
	</label>
	<label for="timeinput-time">
		<small>Uhrzeit:</small>
		<input type="time" id="timeinput-time">
	</label>
</div>

<script src="<?= $server->url ?>/resources/js/admin/timeinput.js"></script>
<script>
	var timeinput = new TimeInput(document.querySelector('.timeinput'));
	timeinput.invoke();
</script>
