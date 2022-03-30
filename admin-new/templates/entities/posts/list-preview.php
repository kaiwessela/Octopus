<article>
	<h2><?= $entity->headline ?></h2>
	<p><?= $entity->timestamp->format('dd.MM.yyyy') ?> – Von <?= $entity->author ?></p>
	<div>
		<a href="/admin/posts/<?= $entity->id ?>/edit">Bearbeiten</a>
		<a href="/admin/posts/<?= $entity->id ?>/delete">Löschen</a>
		<a href="/posts/<?= $entity->longid ?>">Auf der Website ansehen</a>
	</div>
</article>
