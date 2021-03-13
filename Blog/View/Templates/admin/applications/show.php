<section class="applications show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Application->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Application->longid ?></code></td></tr>
		<tr><td><em>Titel:</em></td><td><?= $Application->title ?></td></tr>
		<tr><td><em>Beschreibung:</em></td><td><?= $Application->description ?></td></tr>
		<tr><td><em>Urheberrechtshinweis:</em></td><td><?= $Application->copyright; ?></td></tr>
	</table>
	<p><a href="<?= $Application->src() ?>">Datei: <?= $Application->longid.'.'.$Application->extension ?></a></p>
</section>
