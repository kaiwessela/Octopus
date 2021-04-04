<section class="motions show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Object->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Object->longid ?></code></td></tr>
		<tr><td><em>Titel:</em></td><td><?= $Object->title ?></td></tr>
		<tr><td><em>Sitzungsdatum:</em></td><td><?= $Object->timestamp?->format('date') ?></td></tr>
		<tr>
			<td><em>Status:</em></td>
			<td><span class="tag <?= match($Object->status){'draft' => 'blue', 'accepted' => 'green', 'rejected' => 'red'} ?>">
				<?= match($Object->status){
					'draft' => 'Entwurf',
					'accepted' => 'Angenommen',
					'rejected' => 'Abgelehnt'
				} ?>
			</span></td>
		</tr>
		<tr>
			<td><em>Dokument:</em></td>
			<td>
				<?php if(!empty($Object->document)){ ?>
				<a href="<?= $server->url ?>/admin/applications/<?= $Object->document->id ?>">
					<article class="application">
						<img src="<?= $server->url ?>/admin/resources/icons/<?= $Object->document->type ?>.svg">
						<div class="label">
							<h2 class="title"><?= $Object->document->title ?></h2>
							<code><?= $Object->document->longid ?></code>
						</div>
					</article>
				</a>
				<?php } ?>
			</td>
	</table>
</section>
