<?php
use \Blog\Config\Config;
$controller->person = $controller->obj; // TEMP
?>

<h1>Person löschen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Person nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Löschen der Person fehlgeschlagen.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Person erfolgreich gelöscht.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<?php $person = $controller->person; ?>
<p>
	<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>">Ansehen</a>
	<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $person->id ?>/edit" class="edit">Bearbeiten</a>
</p>
<p>Person <span class="code"><?= $person->longid ?></span> löschen?</p>
<form action="#" method="post">
	<input type="hidden" id="id" name="id" value="<?= $person->id ?>">
	<input type="submit" value="Löschen">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/persons">Zurück zu allen Personen</a>
