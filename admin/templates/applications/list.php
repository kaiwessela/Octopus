<article>
	<article class="application">
		<img src="<?= $server->url ?>/resources/images/icons/<?= $obj->type ?>.svg">
		<div class="label">
			<h2><?= $obj->title ?></h2>
			<code><?= $obj->longid ?></code>
		</div>
	</article>
	<a class="button blue" href="<?= $server->url ?>/admin/applications/<?= $obj->id ?>">Ansehen</a>
	<a class="button yellow" href="<?= $server->url ?>/admin/applications/<?= $obj->id ?>/edit">Bearbeiten</a>
	<a class="button red" href="<?= $server->url ?>/admin/applications/<?= $obj->id ?>/delete">Löschen</a>
</article>
