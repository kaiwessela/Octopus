<?php
use \Blog\Config\Config;
$controller->person = $controller->obj; // TEMP
?>

<h1>Person bearbeiten</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Person nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, die Person zu bearbeiten.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Person erfolgreich geändert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<?php $person = $controller->person; ?>

<p>Personen-ID: <span class="code"><?= $person->longid ?></span></p>

<p>
	<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>">Ansehen</a>
	<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>/delete" class="delete">Löschen</a>
</p>

<form action="#" method="post">
	<input type="hidden" name="id" value="<?= $person->id ?>">
	<input type="hidden" name="longid" value="<?= $person->longid ?>">

	<label for="name">
		<span class="name">Name</span>
		<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
		<span class="description">
			Der vollständige Name der Person.
		</span>
	</label>
	<input type="text" id="name" class="name" name="name" value="<?= $person->name ?>" required>

	<label>
		<span class="name">Profilbild</span>
		<span class="requirements">optional</span>
		<span class="description">
			Das Profilbild sollte ein Portrait der Person sein.
		</span>
	</label>
	<div id="imageinput" data-value="<?= $person->image->id ?? '' ?>" data-longid="<?= $person->image->longid ?? '' ?>" data-name="image_id"
		data-extension="<?= $person->image->extension ?>"></div>

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/persons">Zurück zu allen Personen</a>

<?php include __DIR__ . '/components/imageinput.comp.php'; ?>
