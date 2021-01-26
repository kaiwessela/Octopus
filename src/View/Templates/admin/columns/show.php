<article>
	<code><?= $Column->longid ?></code>
	<h1><?= $Column->name ?></h1>
	<p><?= $Column->description ?></p>

	<?php foreach($Column->postrelations as $rel){ ?>
		<article>
			<code><?= $rel->post->longid ?></code>
			<h2><?= $rel->post->headline ?></h2>
		</article>
	<?php } ?>
</article>
