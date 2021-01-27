<article>
	<em><?= $obj->timestamp?->format('datetime_long') ?></em>
	<h2><?= $obj->title ?></h2>
	<p><code><?= $obj->longid ?></code> – <em><?= $obj->organisation ?></em></p>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
