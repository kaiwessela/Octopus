<section class="events show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Object->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Object->longid ?></code></td></tr>
		<tr><td><em>Titel:</em></td><td><?= $Object->title ?></td></tr>
		<tr><td><em>Veranstalter:</em></td><td><?= $Object->organisation ?></td></tr>
		<tr><td><em>Datum und Uhrzeit:</em></td><td><?= $Object->timestamp?->format('datetime_long') ?></td></tr>
		<tr><td><em>Ort:</em></td><td><?= $Object->location; ?></td></tr>
		<tr>
			<td><em>Absage:</em></td>
			<td>
			<?php if($Object->cancelled){ ?>
				<span class="tag red">Abgesagt</span>
			<?php } else { ?>
				<span class="tag green">Findet statt</span>
			<?php } ?>
			</td>
		</tr>
	</table>
</section>
