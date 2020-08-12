<?php
use \Blog\Config\Config;
?>

<h1>Neuen Post schreiben</h1>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, den neuen Post zu speichern.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Post erfolgreich gespeichert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<form action="<?= Config::SERVER_URL ?>/admin/posts/new" method="post">
	<label for="longid">URL</label>
	<input type="text" id="longid" name="longid" required>

	<label for="overline">Overline</label>
	<input type="text" id="overline" name="overline">

	<label for="headline">Überschrift</label>
	<input type="text" id="headline" name="headline" required>

	<label for="subline">Subline</label>
	<input type="text" id="subline" name="subline">

	<label for="teaser">Teaser</label>
	<textarea id="teaser" name="teaser"></textarea>

	<label for="author">Autor</label>
	<input type="text" id="author" name="author" required>

	<div id="imageinput" data-value="" data-longid="" data-name="image_id"></div>

	<label for="content">Inhalt</label>
	<textarea id="content" name="content"></textarea>

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/posts">Zurück zu allen Posts</a>
