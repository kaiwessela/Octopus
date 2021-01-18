<article>
	<code><?= $obj->longid ?></code>
	<h2><?= $obj->headline ?></h2>
	<strong><?= $obj?->subline ?></strong>
	<small>
		Von <?= $obj->author ?> –
		<time datetime="<?= $obj->timestamp->iso ?>">
			<?= $obj->timestamp->datetime ?>
		</time>
	</small>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
