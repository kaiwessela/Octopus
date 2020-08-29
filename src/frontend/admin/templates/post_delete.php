<?php
use \Blog\Config\Config;
$controller->post = $controller->obj; // TEMP
?>

<h1>Post löschen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Post nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Löschen des Posts fehlgeschlagen.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Post erfolgreich gelöscht.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<?php $post = $controller->post; ?>
<p>
	<a href="<?= Config::SERVER_URL ?>/posts/<?= $post->longid ?>">Blogansicht</a>
	<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>/edit" class="edit">Bearbeiten</a>
</p>
<p>Post <span class="code"><?= $post->longid ?></span> löschen?</p>
<form action="#" method="post">
	<input type="hidden" id="id" name="id" value="<?= $post->id ?>">
	<input type="submit" value="Löschen">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/posts">Zurück zu allen Posts</a>
