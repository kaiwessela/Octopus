<article>
	<code><?= $obj->longid ?></code>
	<h2><?= $obj->title ?></h2>
	<div>
		<a class="button blue" href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow" href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red" href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
