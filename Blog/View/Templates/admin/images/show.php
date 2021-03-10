<section class="images show">
	<img src="<?= $Image->src() ?>" alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Image->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Image->longid ?></code></td></tr>
		<tr><td><em>Beschreibung:</em></td><td><?= $Image->description ?></td></tr>
		<tr><td><em>Alternativtext:</em></td><td><?= $Image->alternative ?></td></tr>
		<tr><td><em>Urheberrechtshinweis:</em></td><td><?= $Image->copyright; ?></td></tr>
	</table>
	<h2>Versionen</h2>
	<table>
	<?php $scan = $ImageController->scan(); foreach($scan['storage'] as $variant => $status){ ?>
		<tr>
			<td><em><?= $variant ?></em></td>
			<td><?php if(isset($scan['db'][$variant])){ ?>Datenbank<?php } ?></td>
			<td><?= ($status['found']) ? 'Gefunden' : 'Nicht gefunden' ?></td>
			<td><?= $status['mime'] ?></td>
			<td><?php if($variant == 'original'){ ?>
				<a href="<?= $Image->src() ?>" class="button blue">Originaldatei</a>
				<?php } else { ?>
				<a href="<?= $Image->src($variant) ?>" class="button gray"><?= $variant ?></a>
			<?php } ?></td>
		</tr>
	<?php } ?>
	</table>
</section>
