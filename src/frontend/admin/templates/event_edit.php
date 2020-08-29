<?php
use \Blog\Config\Config;
$controller->event = $controller->obj; // TEMP
?>

<h1>Veranstaltung bearbeiten</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Veranstaltung nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, die Veranstaltung zu bearbeiten.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Veranstaltung erfolgreich geändert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<?php $event = $controller->event; ?>

<p>Veranstaltungs-ID: <span class="code"><?= $event->longid ?></span></p>

<p>
	<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>">Ansehen</a>
	<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>/delete" class="delete">Löschen</a>
</p>

<form action="#" method="post">
	<input type="hidden" name="id" value="<?= $event->id ?>">
	<input type="hidden" name="longid" value="<?= $event->longid ?>">

	<label for="title">
		<span class="name">Titel</span>
		<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
		<span class="description">Der Titel der Veranstaltung.</span>
	</label>
	<input type="text" id="title" class="title" name="title" value="<?= $event->title ?>" required>

	<label for="organisation">
		<span class="name">Organisation</span>
		<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
		<span class="description">Die Organisation, die zur Veranstaltung eingeladen hat.</span>
	</label>
	<input type="text" id="organisation" class="organisation" name="organisation" value="<?= $event->organisation ?>" required>

	<label>
		<span class="name">Datum und Uhrzeit</span>
		<span class="requirements">erforderlich</span>
		<span class="description">Datum und Uhrzeit der Veranstaltung.</span>
	</label>
	<div id="timeinput" data-value="<?= $event->timestamp ?>" data-name="timestamp"></div>

	<label for="location">
		<span class="name">Ort</span>
		<span class="requirements">optional, bis zu 128 Zeichen</span>
		<span class="description">Der Ort der Veranstaltung.</span>
	</label>
	<input type="text" id="location" class="location" name="location" value="<?= $event->location ?>">

	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="requirements">optional</span>
		<span class="description">Beschreibung der Veranstaltung.</span>
	</label>
	<textarea id="description" name="description" class="description"><?= $event->description ?></textarea>

	<label for="cancelled">
		<span class="name">Absage</span>
		<span class="requirements">optional</span>
		<span class="description">Ist die Veranstaltung abgesagt?
	</label>
	<label class="checkbodge turn-around">
		<span class="label-field">Ja</span>
		<input type="checkbox" id="cancelled" name="cancelled" class="cancelled" value="true" <?php if($event->cancelled){ echo 'checked'; } ?>>
		<span class="bodgecheckbox">
			<span class="bodgetick">
				<span class="bodgetick-down"></span>
				<span class="bodgetick-up"></span>
			</span>
		</span>
	</label>

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/events">Zurück zu allen Veranstaltungen</a>

<?php include __DIR__ . '/components/timeinput.comp.php'; ?>
