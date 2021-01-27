<article>
	<figure>
		<img src="<?= $obj->src() ?>" alt="<?= $obj->description ?>">
		<figcaption><code><?= $obj->longid ?></code></figcaption>
	</figure>
	<a class="button blue" href="<?= $server->url ?>/admin/images/<?= $obj->id ?>">Ansehen</a>
	<a class="button yellow" href="<?= $server->url ?>/admin/images/<?= $obj->id ?>/edit">Bearbeiten</a>
	<a class="button red" href="<?= $server->url ?>/admin/images/<?= $obj->id ?>/delete">Löschen</a>
</article>
