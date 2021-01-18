<article>
	<code><?= $Image->longid ?></code>
	<p><?= $Image->description ?></p>
	<figure>
		<img src="<?= $Image->source_original ?>"
			alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden">
		<figcaption><small><?= $Image->copyright; ?></small></figcaption>
	</figure>
	<p>
		Verfügbare Größen:
		<?php foreach($Image->sizes as $size){ ?>
		<a href="<?= $server->url . $server->dyn_img_path . $Image->longid ?>/<?= $size ?>.<?= $Image->extension ?>" class="button gray">
			<?= $size ?>
		</a>
		<?php } ?>
	</p>
</article>
