<div class="main">
	<label for="title">
		<div class="name">Titel</div>
	</label>
	<input type="text" id="title" name="title" value="<?= $Entity?->title ?>"
		size="100" maxlength="100" required>

	<fieldset id="timestamp" class="row">
		<legend>Termin</legend>

		<label>
			<div class="name">Datum</div>
			<input type="date" id="timestamp-date" name="timestamp[date]" value="<?= $Entity?->timestamp?->to_html_date() ?>"
				size="10" required>
		</label>

		<label>
			<div class="name">Uhrzeit</div>
			<input type="time" id="timestamp-time" name="timestamp[time]" value="<?= $Entity?->timestamp?->to_html_time() ?>"
				size="5" required>
		</label>
	</fieldset>

	<label for="location">
		<div class="name">Ort</div>
	</label>
	<input type="text" id="location" name="location" value="<?= $Entity?->location ?>"
		size="100" maxlength="100">

	<label for="organisation">
		<div class="name">Organisation</div>
	</label>
	<input type="text" id="organisation" name="organisation" value="<?= $Entity?->organisation ?>"
		size="60" maxlength="60">

	<label for="description">
		<div class="name">Beschreibung</div>
	</label>
	<textarea id="description" name="description" cols="80" rows="10"><?= $Entity?->description ?></textarea>

	<fieldset id="cancelled">
		<legend>Absage</legend>
		<label>
			<div class="name">Findet statt</div>
			<input type="radio" name="cancelled" value="false" <?php if(!$Entity?->cancelled){ ?>checked<?php } ?> >
		</label>
		<label>
			<div class="name">Veranstaltung abgesagt</div>
			<input type="radio" name="cancelled" value="true" <?php if($Entity?->cancelled){ ?>checked<?php } ?> >
		</label>
	</fieldset>

</div>
<div class="side">
	<?php include __DIR__.'/../../components/edit/id_and_longid.php'; ?>

</div>
