<article>
	<h2><?= $obj->longid ?></h2>
	<a class="button blue" href="<?= $server->url ?>/admin/applications/<?= $obj->id ?>">Ansehen</a>
	<a class="button yellow" href="<?= $server->url ?>/admin/applications/<?= $obj->id ?>/edit">Bearbeiten</a>
	<a class="button red" href="<?= $server->url ?>/admin/applications/<?= $obj->id ?>/delete">Löschen</a>
</article>
