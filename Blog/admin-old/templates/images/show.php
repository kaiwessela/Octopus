<section class="images show">
	<img src="<?= $Object->src() ?>" alt="<?= $Object->alternative ?>">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Object->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Object->longid ?></code></td></tr>
		<tr><td><em>Titel:</em></td><td><?= $Object->title ?></td></tr>
		<tr><td><em>Beschreibung:</em></td><td><?= $Object->description ?></td></tr>
		<tr><td><em>Alternativtext:</em></td><td><?= $Object->alternative ?></td></tr>
		<tr><td><em>Urheberrechtshinweis:</em></td><td><?= $Object->copyright; ?></td></tr>
	</table>
	<h2>Dateien</h2>

	<table>
		<tr>
			<th>Variante</th>
			<th>MIME-Typ</th>
			<th>Speicherort</th>
			<th>Link zur Datei</th>
		</tr>
	<?php $scan = $Controller->scan(); foreach($scan['storage'] as $variant => $status){ ?>
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
				<a href="<?= $Object->src($variant) ?>" class="button gray"><?= $variant ?></a>
			</td>
		</tr>
	<?php } ?>
	</table>
</section>
