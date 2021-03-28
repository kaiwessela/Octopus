<section class="applications show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Object->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Object->longid ?></code></td></tr>
		<tr><td><em>Titel:</em></td><td><?= $Object->title ?></td></tr>
		<tr><td><em>Beschreibung:</em></td><td><?= $Object->description ?></td></tr>
		<tr><td><em>Urheberrechtshinweis:</em></td><td><?= $Object->copyright; ?></td></tr>
	</table>
	<p><a href="<?= $Object->src() ?>">Datei: <?= $Object->longid.'.'.$Object->extension ?></a></p>
</section>
