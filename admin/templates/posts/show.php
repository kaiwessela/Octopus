<section class="posts show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Object->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Object->longid ?></code></td></tr>
		<tr><td><em>Dachzeile:</em></td><td><?= $Object->overline ?></td></tr>
		<tr><td><em>Schlagzeile:</em></td><td><?= $Object->headline ?></td></tr>
		<tr><td><em>Unterzeile:</em></td><td><?= $Object->subline ?></td></tr>
		<tr><td><em>Teaser:</em></td><td><?= $Object->teaser ?></td></tr>
		<tr><td><em>Autor:</em></td><td><?= $Object->author ?></td></tr>
		<tr><td><em>Datum und Uhrzeit:</em></td><td><?= $Object->timestamp?->format('datetime') ?></td></tr>
		<tr>
			<td><em>Rubriken:</em></td>
			<td>
			<?php $Object->columnrelations?->each(function($r) use ($server){ ?>
				<a href="<?= $server->url ?>/admin/columns/<?= $r->column->id ?>" class="button">
					<?= $r->column->name ?>
				</a>
			<?php }); ?>
			</td>
		</tr>
		<tr>
			<td><em>Artikelbild:</em></td>
			<td>
				<?php if(!empty($Object->image)){ ?>
				<a href="<?= $server->url ?>/admin/images/<?= $Object->image->id ?>" class="button">
					<?= $Object->image->title ?? $Object->image->longid ?>
				</a>
				<img src="<?= $Object->image->src() ?>" alt="<?= $Object->image->alternative ?>">
				<?php } ?>
			</td>
		</tr>
	</table>
</section>
