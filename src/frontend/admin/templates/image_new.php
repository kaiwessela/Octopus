<?php
use \Blog\Config\Config;
?>

<h1>Neues Bild hochladen</h1>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, ein neues Bild hochzuladen.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Bild erfolgreich hochgeladen.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<form action="<?= Config::SERVER_URL ?>/admin/images/new" method="post" enctype="multipart/form-data">
	<label for="longid">URL</label>
	<input type="text" id="longid" name="longid" required>

	<label for="description">Beschreibung</label>
	<input type="text" id="description" name="description">

	<label for="imagefile">Datei</label>
	<input type="file" id="imagefile" name="imagedata" required>

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/images">Zurück zu allen Bildern</a>
