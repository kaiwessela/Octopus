<article>
	<code><?= $Image->longid ?></code>
	<p><?= $Image->description ?></p>
	<figure>
		<img src="<?= $Image->src() ?>"
			alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden">
		<figcaption><small><?= $Image->copyright; ?></small></figcaption>
	</figure>
	<p>
		Verfügbare Größen:
		<?php foreach($Image->sizes as $size){ ?>
		<a href="<?= $Image->src($size) ?>" class="button gray">
			<?= $size ?>
		</a>
		<?php } ?>
	</p>
</article>
