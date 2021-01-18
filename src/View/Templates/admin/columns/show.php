<article>
	<code><?= $obj->longid ?></code>
	<h1><?= $obj->name ?></h1>
	<p><?= $obj->description ?></p>

	<?php foreach($obj->posts as $post){ ?>
		<article>
			<code><?= $post->longid ?></code>
			<h2><?= $post->headline ?></h2>
		</article>
	<?php } ?>
</article>
