<?php
use \Blog\Config\Config;
$controller->persons = $controller->objs; // TEMP
?>

<h1>Alle Personen</h1>

<?php if($controller->show_warn_no_found){ ?>
<span class="message warning">
	Bisher sind keine Personen vorhanden.
</span>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/persons/new" class="button">Neue Person hinzufügen</a>

<?php if($controller->show_list){ ?>
	<?php foreach($controller->persons as $person){ ?>
	<article class="person preview">
		<p class="longid"><?= $person->longid ?></p>
		<h3 class="name"><?= $person->name ?></h3>
		<div>
			<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>" class="view">Ansehen</a>
			<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>/edit" class="edit">Bearbeiten</a>
			<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>/delete" class="delete">Löschen</a>
		</div>
	</article>
	<?php } ?>
<?php } ?>
