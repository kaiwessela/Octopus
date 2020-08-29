<?php
use \Blog\Config\Config;
$controller->event = $controller->obj; // TEMP
?>

<h1>Veranstaltung löschen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Veranstaltung nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Löschen der Veranstaltung fehlgeschlagen.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Veranstaltung erfolgreich gelöscht.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<?php $event = $controller->event; ?>
<p>
	<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>">Ansehen</a>
	<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>/edit" class="edit">Bearbeiten</a>
</p>
<p>Person <span class="code"><?= $event->longid ?></span> löschen?</p>
<form action="#" method="post">
	<input type="hidden" id="id" name="id" value="<?= $event->id ?>">
	<input type="submit" value="Löschen">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/events">Zurück zu allen Veranstaltungen</a>
