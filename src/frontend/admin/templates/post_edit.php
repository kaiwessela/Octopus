<?php
use \Blog\Config\Config;
?>

<h1>Post bearbeiten</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Post nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, den Post zu bearbeiten.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Post erfolgreich geändert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<form action="<?= Config::SERVER_URL ?>/admin/posts/<?= $controller->post->id ?>/edit" method="post">
	<input type="hidden" id="id" name="id" value="<?= $controller->post->id ?>">
	<input type="hidden" id="longid" name="longid" value="<?= $controller->post->longid ?>">

	<label for="overline">Overline (optional)</label>
	<input type="text" id="overline" name="overline" value="<?= $controller->post->overline ?>">

	<label for="headline">Überschrift</label>
	<input type="text" id="headline" name="headline" required value="<?= $controller->post->headline ?>">

	<label for="subline">Subline (optional)</label>
	<input type="text" id="subline" name="subline" value="<?= $controller->post->subline ?>">

	<label for="teaser">Teaser (optional)</label>
	<textarea id="teaser" name="teaser" class="teaser-text"><?= $controller->post->teaser ?></textarea>

	<label for="author">Autor</label>
	<input type="text" id="author" name="author" required value="<?= $controller->post->author ?>">

	<div id="imageinput" data-value="<?= $controller->post->image->id ?? '' ?>" data-longid="<?= $controller->post->image->longid ?? '' ?>" data-name="image_id"></div>

	<label for="content">Inhalt (optional)</label>
	<textarea id="content" name="content" class="long-text"><?= $controller->post->content ?></textarea>

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/posts">Zurück zu allen Posts</a>

<?php include __DIR__ . '/components/imageinput.comp.php';
