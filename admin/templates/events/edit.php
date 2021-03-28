<form action="#" method="post" class="events edit">

<?php if($Controller->call->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Termin-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Termin-ID wird in der URL verwendet und entspricht meistens dem Titel.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Object?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Object?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Object?->longid ?>" size="40" readonly>

<?php } ?>

	<!-- TITLE -->
	<label for="title">
		<span class="name">Titel</span>
		<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
		<span class="infos">Der Titel der Veranstaltung.</span>
	</label>
	<input type="text" size="40"
		id="title" name="title" value="<?= $Object?->title ?>"
		maxlength="50" required>

	<!-- ORGANISATION -->
	<label for="organisation">
		<span class="name">Organisation</span>
		<span class="conditions">erforderlich, 1 bis 40 Zeichen</span>
		<span class="infos">Die Organisation, die zur Veranstaltung eingeladen hat.</span>
	</label>
	<input type="text" size="30"
		id="organisation" name="organisation" value="<?= $Object?->organisation ?>"
		maxlength="40" required>

	<!-- TIMESTAMP -->
	<label for="timeinput">
		<span class="name">Datum und Uhrzeit</span>
		<span class="conditions">erforderlich</span>
		<span class="infos">Datum und Uhrzeit der Veranstaltung.</span>
	</label>
	<input type="text" size="19"
		id="timestamp" name="timestamp" value="<?= $Object?->timestamp ?>" required>

	<!-- LOCATION -->
	<label for="location">
		<span class="name">Ort</span>
		<span class="conditions">optional, bis zu 60 Zeichen</span>
		<span class="infos">Der Ort der Veranstaltung.</span>
	</label>
	<input type="text" size="40"
		id="location" name="location" value="<?= $Object?->location ?>"
		maxlength="60">

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional</span>
		<span class="infos">Beschreibung der Veranstaltung.</span>
	</label>
	<textarea id="description" name="description"
		cols="60" rows="5"><?= $Object?->description ?></textarea>

	<!-- CANCELLED -->
	<label for="cancelled">
		<span class="name">Absage</span>
		<span class="conditions">optional</span>
		<span class="description">Ist der Termin abgesagt?
	</label>
	<label class="checkbodge turn-around">
		<span class="label-field">Ja</span>
		<input type="checkbox" id="cancelled" name="cancelled" value="true"
			<?php if($Object?->cancelled){ echo 'checked'; } ?>>
		<span class="bodgecheckbox">
			<span class="bodgetick">
				<span class="bodgetick-down"></span>
				<span class="bodgetick-up"></span>
			</span>
		</span>
	</label>

	<button type="submit" class="green">Speichern</button>
</form>

<div class="timeinput nojs" data-for="timestamp">
	<label>
		Datum:
		<input type="date">
	</label>
	<label>
		Uhrzeit:
		<input type="time">
	</label>
</div>
