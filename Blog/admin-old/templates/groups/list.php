<article>
	<code><?= $obj->longid ?></code>
	<h2><?= $obj->name ?></h2>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/groups/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/groups/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/groups/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
