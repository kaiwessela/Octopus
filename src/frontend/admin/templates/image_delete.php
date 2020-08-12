<?php
use \Blog\Config\Config;
?>

<h1>Bild löschen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Bild nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Löschen des Bildes fehlgeschlagen.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Bild erfolgreich gelöscht.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<p>Bild <span class="code"><?= $controller->image->longid ?></span> löschen?</p>
<form action="<?= Config::SERVER_URL ?>/admin/images/<?= $controller->image->id ?>/delete" method="post">
	<input type="hidden" id="id" name="id" value="<?= $controller->image->id ?>">
	<input type="submit" value="Löschen">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/images">Zurück zu allen Bildern</a>
