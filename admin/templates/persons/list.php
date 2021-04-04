<article>
	<figure>
		<?php if(!empty($obj->image)){ ?>
		<img src="<?= $obj->image?->src() ?>" alt="<?= $obj->image?->alternative ?>">
		<?php } else { ?>
		<img src="<?= $server->url ?>/admin/resources/images/person.png" alt="Kein Profilbild vorhanden">
		<?php } ?>
		<figcaption>
			<code><?= $obj->longid ?></code>
			<h2><?= $obj->name ?></h2>
		</figcaption>
	</figure>
	<a class="button blue"
		href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>">Ansehen</a>
	<a class="button yellow"
		href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>/edit">Bearbeiten</a>
	<a class="button red"
		href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>/delete">Löschen</a>
</article>
