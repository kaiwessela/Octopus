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
<form action="#" method="post" enctype="multipart/form-data">
	<label for="longid">
		<span class="name">Bild-ID</span>
		<span class="requirements">
			erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="description">
			Die Bild-ID wird in der URL verwendet und sollte den Bildinhalt kurz
			beschreiben.
		</span>
	</label>
	<input type="text" id="longid" class="longid" name="longid" required>

	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="requirements">optional</span>
		<span class="description">
			Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
			werden kann. Sie sollte den Bildinhalt wiedergeben.
		</span>
	</label>
	<input type="text" id="description" class="description" name="description">

	<label for="copyright">
		<span class="name">Urheberrechtshinweis</span>
		<span class="requirements">optional</span>
		<span class="description">
			Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
			zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
		</span>
	</label>
	<input type="text" id="copyright" class="copyright" name="copyright" value="<?= $image->copyright ?>">

	<label for="imagefile">
		<span class="name">Datei</span>
		<span class="requirements">erforderlich; PNG, JPEG oder GIF</span>
	</label>
	<input type="file" id="imagefile" class="file" name="imagedata" required>

	<input type="submit" value="Hochladen">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/images">Zurück zu allen Bildern</a>
