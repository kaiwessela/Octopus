<?php
use \Blog\Config\Config;
$controller->image = $controller->obj; // TEMP
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
<?php $image = $controller->image; ?>

<p>Bild-ID: <span class="code"><?= $image->longid ?></span></p>
<p>
	<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $image->id ?>">Ansehen</a>
	<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $image->id ?>/delete" class="delete">Löschen</a>
</p>

<form action="#" method="post">
	<input type="hidden" id="id" name="id" value="<?= $image->id ?>">
	<input type="hidden" id="longid" name="longid" value="<?= $image->longid ?>">

	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="requirements">optional, bis zu 256 Zeichen</span>
		<span class="description">
			Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
			werden kann. Sie sollte den Bildinhalt wiedergeben.
		</span>
	</label>
	<input type="text" id="description" class="description" name="description" value="<?= $image->description ?>">

	<label for="copyright">
		<span class="name">Urheberrechtshinweis</span>
		<span class="requirements">optional, bis zu 256 Zeichen</span>
		<span class="description">
			Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
			zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
		</span>
	</label>
	<input type="text" id="copyright" class="copyright" name="copyright" value="<?= $image->copyright ?>">

	<input type="submit" value="Speichern">
</form>

<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . "$image->longid/original.$image->extension" ?>" alt="[ANZEIGEFEHLER]">
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/images">Zurück zu allen Bildern</a>
