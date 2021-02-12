<article>
	<code><?= $obj->longid ?></code>
	<small><?= $obj->author ?>, <?= $obj->timestamp->format('date_short') ?></small>
	<h2><?= $obj->headline ?></h2>
	<strong><?= $obj?->subline ?></strong>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
