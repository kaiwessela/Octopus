<section class="images show">
	<img src="<?= $Image->src() ?>" alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Image->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Image->longid ?></code></td></tr>
		<tr><td><em>Titel:</em></td><td><?= $Image->title ?></td></tr>
		<tr><td><em>Beschreibung:</em></td><td><?= $Image->description ?></td></tr>
		<tr><td><em>Alternativtext:</em></td><td><?= $Image->alternative ?></td></tr>
		<tr><td><em>Urheberrechtshinweis:</em></td><td><?= $Image->copyright; ?></td></tr>
	</table>
	<h2>Dateien</h2>
	
	<table>
		<tr>
			<th>Variante</th>
			<th>MIME-Typ</th>
			<th>Speicherort</th>
			<th>Link zur Datei</th>
		</tr>
	<?php $scan = $ImageController->scan(); foreach($scan['storage'] as $variant => $status){ ?>
		<tr>
			<td><em><?= $variant ?></em></td>
			<td><code><?= $status['mime'] ?? '' ?></code></td>
			<td>
				<?php if($status['found']){ ?>
				<span class="tag green">Dateisystem</span>
				<?php } else { ?>
				<span class="tag yellow">Nicht im Dateisystem gefunden</span>
				<?php }
				if(isset($scan['db'][$variant])){ ?>
				<span class="tag blue">Datenbank</span>
				<?php } ?>
			</td>
			<td>
				<a href="<?= $Image->src($variant) ?>" class="button gray"><?= $variant ?></a>
			</td>
		</tr>
	<?php } ?>
	</table>
</section>
