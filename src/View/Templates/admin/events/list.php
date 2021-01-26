<article>
	<code><?= $obj->longid ?></code>
	<h2><?= $obj->title ?></h2>
	<small><?= $obj->location ?></small>
	<small><?= $obj->timestamp?->format('datetime_long') ?></small>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
