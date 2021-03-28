<section class="persons show">
	<code><?= $Object->longid ?></code>
	<h1 class="name"><?= $Object->name ?></h1>

	<?php if($Object->image){ ?>
	<div>
		Profilbild: <code><?= $Object->image?->longid ?></code>
		<a href="<?= $server->url ?>/admin/images/<?= $Object->image?->longid ?>">ansehen</a>
		<img src="<?= $Object->image?->src() ?>?size=original" alt="<?= $Object->image?->description ?>">
	</div>
	<?php } ?>
</section>
