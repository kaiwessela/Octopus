<?php
use \Blog\Config\Config;
?>

<h1>Post ansehen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Post nicht vorhanden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_post){ ?>
<?php $post = $controller->post ?>
<a href="<?= Config::SERVER_URL ?>/admin/posts" class="button">&laquo; Zurück zu allen Posts</a>

<article class="post">
	<p>
		<a href="<?= Config::SERVER_URL ?>/posts/<?= $post->longid ?>">Blogansicht</a>
		<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>/edit" class="edit">Bearbeiten</a>
		<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>/delete" class="delete">Löschen</a>
	</p>
	<p class="longid"><?= $post->longid ?></p>
	<p class="overline"><?= $post->overline ?></p>
	<h1 class="headline"><?= $post->headline ?></h1>
	<p class="subline"><?= $post->subline ?></p>
	<p class="teaser"><?= $post->teaser ?></p>
	<p>
		Von <span class="author"><?= $post->author ?></span> –
		<span class="timestamp"><?= $post->timestamp ?></span>
	</p>

	<?php if(!$post->image->is_empty()){ ?>
	<div>
		Bild: <span class="code"><?= $post->image->longid ?></span>
		<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $post->image->longid ?>">ansehen</a>
		<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . $post->image->longid . '.'
			. $post->image->extension ?>?size=original" alt="<?= $post->image->description ?>">
	</div>
	<?php } ?>

	<p class="content"><?= $post->content ?></p>
</article>
<?php } ?>
