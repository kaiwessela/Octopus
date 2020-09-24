<template id="ti-basic">
	<label>Datum: <input type="date" id="ti-date" value=""></label>
	<label>Uhrzeit: <input type="time" id="ti-time" value=""></label>
	<input type="hidden" id="ti-value" name="" value="">
</template>

<script src="<?= $server->url ?>/resources/js/timeinput.js"></script>
<script>
var ti = new TimeInput(document.getElementById('timeinput'));
</script>
