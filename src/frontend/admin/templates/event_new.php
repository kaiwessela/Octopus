<?php
use \Blog\Config\Config;
?>

<h1>Neue Veranstaltung hinzufügen</h1>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, die neue Veranstaltung zu speichern.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Veranstaltung erfolgreich gespeichert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<form action="#" method="post">
	<label for="longid">
		<span class="name">Veranstaltungs-ID</span>
		<span class="requirements">
			erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="description">
			Die Veranstaltungs-ID wird in der URL verwendet und entspricht meistens dem Titel.
		</span>
	</label>
	<input type="text" id="longid" class="longid" name="longid" required>

	<label for="title">
		<span class="name">Titel</span>
		<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
		<span class="description">Der Titel der Veranstaltung.</span>
	</label>
	<input type="text" id="title" class="title" name="title" required>

	<label for="organisation">
		<span class="name">Organisation</span>
		<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
		<span class="description">Die Organisation, die zur Veranstaltung eingeladen hat.</span>
	</label>
	<input type="text" id="organisation" class="organisation" name="organisation" required>

	<label>
		<span class="name">Datum und Uhrzeit</span>
		<span class="requirements">erforderlich</span>
		<span class="description">Datum und Uhrzeit der Veranstaltung.</span>
	</label>
	<div id="timeinput" data-value="" data-name="timestamp"></div>

	<label for="location">
		<span class="name">Ort</span>
		<span class="requirements">optional, bis zu 128 Zeichen</span>
		<span class="description">Der Ort der Veranstaltung.</span>
	</label>
	<input type="text" id="location" class="location" name="location">

	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="requirements">optional</span>
		<span class="description">Beschreibung der Veranstaltung.</span>
	</label>
	<textarea id="description" name="description" class="description"></textarea>

	<label for="cancelled">
		<span class="name">Absage</span>
		<span class="requirements">optional</span>
		<span class="description">Ist die Veranstaltung abgesagt?
	</label>
	<label>Ja <input type="checkbox" id="cancelled" class="cancelled" name="cancelled" value="true"></label>


	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/events">Zurück zu allen Veranstaltungen</a>

<?php include __DIR__ . '/components/timeinput.comp.php'; ?>
