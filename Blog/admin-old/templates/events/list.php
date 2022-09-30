<article>
	<code><?= $obj->longid ?></code>
	<h2><?= $obj->title ?></h2>
	<em><?= $obj->organisation ?></em>
	<em><?= $obj->timestamp?->format('datetime_long') ?></em>
	<em><?= $obj->location ?></em>
	<p><?php if($obj->cancelled){ ?><span class="tag red">Abgesagt</span><?php } ?></p>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
