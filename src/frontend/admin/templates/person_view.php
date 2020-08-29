<?php
use \Blog\Config\Config;
$controller->person = $controller->obj; // TEMP
?>

<h1>Person ansehen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Person nicht vorhanden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_obj){ ?>
<?php $person = $controller->person ?>
<a href="<?= Config::SERVER_URL ?>/admin/persons" class="button">&laquo; Zurück zu allen Personen</a>

<article class="person">
	<p>
		<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>/edit" class="edit">Bearbeiten</a>
		<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>/delete" class="delete">Löschen</a>
	</p>
	<p class="longid"><?= $person->longid ?></p>
	<h1 class="name"><?= $person->name ?></h1>

	<?php if(!$person->image->is_empty()){ ?>
	<div>
		Profilbild: <span class="code"><?= $person->image->longid ?></span>
		<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $person->image->longid ?>">ansehen</a>
		<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . $person->image->longid . '.'
			. $person->image->extension ?>?size=original" alt="<?= $person->image->description ?>">
	</div>
	<?php } ?>
</article>
<?php } ?>
