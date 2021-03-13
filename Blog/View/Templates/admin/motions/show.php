<section class="motions show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Motion->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Motion->longid ?></code></td></tr>
		<tr><td><em>Titel:</em></td><td><?= $Motion->title ?></td></tr>
		<tr><td><em>Datum und Uhrzeit:</em></td><td><?= $Motion->timestamp?->format('datetime') ?></td></tr>
		</tr>
	</table>
</section>
