<section class="columns show">
	<table>
		<tr><td><em>ID:</em></td><td><code><?= $Object->id ?></code></td></tr>
		<tr><td><em>Long-ID:</em></td><td><code><?= $Object->longid ?></code></td></tr>
		<tr><td><em>Name:</em></td><td><?= $Object->name ?></td></tr>
	</table>
	<h2>Artikel:</h2>

	<p>
		<?php $Object->postrelations?->each(function($rel) use ($server){ ?>
		<a class="button gray" href="<?= $server->url ?>/admin/posts/<?= $rel->post->id ?>">
			<?= $rel->post->headline ?>
		</a>
		<?php }); ?>
	</p>
</section>
