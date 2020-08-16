<?php
use \Blog\Config\Config;
?>

<h1>Neue Person hinzufügen</h1>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, die neue Person zu speichern.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Person erfolgreich gespeichert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<form action="#" method="post">
	<label for="longid">
		<span class="name">Personen-ID</span>
		<span class="requirements">
			erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="description">
			Die Personen-ID wird in der URL verwendet und entspricht meistens dem Namen.
		</span>
	</label>
	<input type="text" id="longid" class="longid" name="longid" required>

	<label for="name">
		<span class="name">Name</span>
		<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
		<span class="description">
			Der vollständige Name der Person.
		</span>
	</label>
	<input type="text" id="name" class="name" name="name" required>

	<label>
		<span class="name">Profilbild</span>
		<span class="requirements">optional</span>
		<span class="description">
			Das Profilbild sollte ein Portrait der Person sein.
		</span>
	</label>
	<div id="imageinput" data-value="" data-longid="" data-name="image_id"></div>

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/persons">Zurück zu allen Personen</a>

<?php include __DIR__ . '/components/imageinput.comp.php'; ?>
