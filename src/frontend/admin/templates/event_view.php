<?php
use \Blog\Config\Config;
?>

<h1>Veranstaltung ansehen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Veranstaltung nicht vorhanden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_event){ ?>
<?php $event = $controller->event ?>
<a href="<?= Config::SERVER_URL ?>/admin/events" class="button">&laquo; Zurück zu allen Veranstaltungen</a>

<article class="event">
	<p>
		<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>/edit" class="edit">Bearbeiten</a>
		<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>/delete" class="delete">Löschen</a>
	</p>
	<p class="longid"><?= $event->longid ?></p>
	<h1 class="title"><?= $event->title ?></h1>
	<p class="timestamp"><?= $event->timestamp ?></p>
	<p class="location"><?= $event->location ?></p>
</article>
<?php } ?>
