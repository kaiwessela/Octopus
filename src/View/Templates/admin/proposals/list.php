<article>
	<code><?= $obj->longid ?></code>
	<small><?= $obj->timestamp->format('date_short') ?></small>
	<h2><?= $obj->title ?></h2>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/proposals/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/proposals/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/proposals/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
