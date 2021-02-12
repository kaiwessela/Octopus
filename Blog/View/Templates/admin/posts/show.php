<section class="posts show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Post->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Post->longid ?></code></td></tr>
		<tr><td><em>Dachzeile:</em></td><td><?= $Post->overline ?></td></tr>
		<tr><td><em>Schlagzeile:</em></td><td><?= $Post->headline ?></td></tr>
		<tr><td><em>Unterzeile:</em></td><td><?= $Post->subline ?></td></tr>
		<tr><td><em>Teaser:</em></td><td><?= $Post->teaser ?></td></tr>
		<tr><td><em>Autor:</em></td><td><?= $Post->author ?></td></tr>
		<tr><td><em>Datum und Uhrzeit:</em></td><td><?= $Post->timestamp?->format('datetime') ?></td></tr>
		<tr>
			<td><em>Artikelbild:</em></td>
			<td>
				<?php if($Post->image){ ?>
				<img src="<?= $Post->image->src() ?>" alt="<?= $Post->image->description ?>">
				<?php } ?>
			</td>
		</tr>
	</table>
</section>
