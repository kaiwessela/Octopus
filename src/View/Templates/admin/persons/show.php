<article>
	<code><?= $Person->longid ?></code>
	<h1 class="name"><?= $Person->name ?></h1>

	<?php if(!empty($Person->image)){ ?>
	<div>
		Profilbild: <code><?= $Person->image->longid ?></code>
		<a href="<?= $server->url ?>/admin/images/<?= $Person->image->longid ?>">ansehen</a>
		<img src="<?= $server->url . $server->dyn_img_path . $Person->image->longid . '.'
			. $Person->image->extension ?>?size=original" alt="<?= $Person->image->description ?>">
	</div>
	<?php } ?>
</article>
