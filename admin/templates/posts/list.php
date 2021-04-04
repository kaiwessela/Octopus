<article class="post">
	<code><?= $obj->longid ?></code>
	<p class="overline"><?= $obj->overline ?></p>
	<h2 class="headline"><?= $obj->headline ?></h2>
	<p class="subline"><?= $obj->subline ?></p>
	<p class="author"><?= $obj->timestamp->format('date_short') ?> – <?= $obj->author ?></p>
	<p class="teaser"><?= $obj->teaser ?></p>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
