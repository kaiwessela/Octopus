<section class="images show">
	<img src="<?= $Image->src() ?>" alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Image->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Image->longid ?></code></td></tr>
		<tr><td><em>Beschreibung/Alternativtext:</em></td><td><?= $Image->description ?></td></tr>
		<tr><td><em>Urheberrechtshinweis:</em></td><td><?= $Image->copyright; ?></td></tr>
		<tr>
			<td><em>Verfügbare Größen:</em></td>
			<td>
				<?php foreach($Image->sizes as $size){ ?>
				<a href="<?= $Image->src($size) ?>" class="button gray">
					<?= $size ?>
				</a>
				<?php } ?>
			</td>
		</tr>
	</table>
</section>
