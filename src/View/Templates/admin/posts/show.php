<article>
	<code><?= $Post->longid ?></code>
	<b><?= $Post->overline ?></b>
	<h1><?= $Post->headline ?></h1>
	<strong><?= $Post->subline ?></strong>
	<p><?= $Post->teaser ?></p>
	<small>
		Von <?= $Post->author ?> –
		<time datetime="<?= $Post->timestamp->iso() ?>">
			<?= $Post->timestamp->format('datetime') ?>
		</time>
	</small>

	<?php if($Post->image){ ?>
	<div>
		Bild: <code><?= $Post->image?->longid ?></code>
		<a href="<?= $server->url ?>/admin/images/<?= $Post->image?->id ?>">ansehen</a>
		<img src="<?= $Post->image?->src() ?>" alt="<?= $Post->image?->description ?>">
	</div>
	<?php } ?>

	<p><?= $Post?->content ?></p>
</article>
