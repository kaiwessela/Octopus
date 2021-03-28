<section class="motions show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Object->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Object->longid ?></code></td></tr>
		<tr><td><em>Titel:</em></td><td><?= $Object->title ?></td></tr>
		<tr><td><em>Datum und Uhrzeit:</em></td><td><?= $Object->timestamp?->format('datetime') ?></td></tr>
		</tr>
	</table>
</section>
