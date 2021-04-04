<section class="groups show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Object->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Object->longid ?></code></td></tr>
		<tr><td><em>Name:</em></td><td><?= $Object->name ?></td></tr>
	</table>
	<h2>Mitglieder:</h2>

	<table>
		<tr>
			<th>Nr.</th>
			<th>Name</th>
			<th>Rolle</th>
			<th>Link zum Profil</th>
		</tr>
		<?php $Object->personrelations?->each(function($rel) use ($server){ ?>
		<tr>
			<td><?= $rel->number ?></td>
			<td><?= $rel->person->name ?></td>
			<td><?= $rel->role ?></td>
			<td>
				<a class="button" href="<?= $server->url ?>/admin/persons/<?= $rel->person->id ?>">
					<?= $rel->person->name ?>
				</a>
			</td>
		</tr>
		<?php }) ?>
	</table>
</section>
