<section class="columns show">
	<code><?= $Object->longid ?></code>
	<h1><?= $Object->name ?></h1>
	<p><?= $Object->description ?></p>

	<?php foreach($Object->postrelations as $rel){ ?>
		<article>
			<code><?= $rel->post->longid ?></code>
			<h2><?= $rel->post->headline ?></h2>
		</article>
	<?php } ?>
</section>
