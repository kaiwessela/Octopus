<?php
use \Blog\Config\Config;
$controller->posts = $controller->objs; // TEMP
?>

<h1>Alle Posts</h1>

<?php if($controller->show_warn_no_found){ ?>
<span class="message warning">
	Bisher sind keine Posts vorhanden.
</span>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/posts/new" class="button">Neuen Post schreiben</a>

<?php if($controller->show_list){ ?>
	<?php foreach($controller->posts as $post){ ?>
	<article class="post preview">
		<p class="longid"><?= $post->longid ?></p>
		<p class="overline"><?= $post->overline ?></p>
		<h3 class="headline"><?= $post->headline ?></h3>
		<p class="subline"><?= $post->subline ?></p>
		<p>
			<span class="author"><?= $post->author ?></span> –
			<span class="timestamp"><?= date('d.m.Y, H:i \U\h\r', $post->timestamp) ?></span>
		</p>
		<div>
			<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>" class="view">Ansehen</a>
			<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>/edit" class="edit">Bearbeiten</a>
			<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>/delete" class="delete">Löschen</a>
		</div>
		<p class="teaser"><?= $post->teaser ?></p>
	</article>
	<?php } ?>
<?php } ?>
