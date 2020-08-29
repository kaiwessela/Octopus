<?php
use \Blog\Config\Config;
$controller->events = $controller->objs; // TEMP
?>

<h1>Alle Veranstaltungen</h1>

<?php if($controller->show_warn_no_found){ ?>
<span class="message warning">
	Bisher sind keine Veranstaltungen vorhanden.
</span>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/events/new" class="button">Neue Veranstaltung hinzufügen</a>

<?php if($controller->show_list){ ?>
	<?php foreach($controller->events as $event){ ?>
	<article class="event preview">
		<p class="longid"><?= $event->longid ?></p>
		<h3 class="name"><?= $event->title ?></h3>
		<p class="timestamp"><?= date('d.m.Y, H:i \U\h\r', $event->timestamp) ?></p>
		<div>
			<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>" class="view">Ansehen</a>
			<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>/edit" class="edit">Bearbeiten</a>
			<a href="<?= Config::SERVER_URL ?>/admin/events/<?= $event->id ?>/delete" class="delete">Löschen</a>
		</div>
	</article>
	<?php } ?>
<?php } ?>
