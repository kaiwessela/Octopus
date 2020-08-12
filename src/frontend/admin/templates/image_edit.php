<?php
use \Blog\Config\Config;
?>

<h1>Bildinformationen bearbeiten</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Bild nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, die Bildinformationen zu ändern.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Bildinformationen erfolgreich geändert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<p>Bild-URL: <span class="code"><?= $controller->image->longid ?></span></p>
<form action="<?= Config::SERVER_URL ?>/admin/images/<?= $controller->image->id ?>/edit" method="post">
	<input type="hidden" id="id" name="id" value="<?= $controller->image->id ?>">
	<input type="hidden" id="longid" name="longid" value="<?= $controller->image->longid ?>">

	<label for="description">Beschreibung</label>
	<input type="text" id="description" name="description" value="<?= $controller->image->description ?>">

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/images">Zurück zu allen Bildern</a>
