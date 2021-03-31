<article class="post preview">
	<a href="<?= $server->url ?>/posts/<?= $post->longid ?>">

		<?php if($post->image){ ?>
		<picture>
			<source srcset="<?= $post->image->srcset() ?>">
			<img src="<?= $post->image->src() ?>" alt="<?= $post->image->description ?>">
		</picture>
		<?php } ?>

		<p class="overline"><?= $post->overline ?></p>
		<h3><span><?= $post->headline ?></span></h3>
		<p class="subline"><?= $post->subline ?></p>
		<p class="teaser">
			<time datetime="<?= $post->timestamp->iso() ?>">
				<?= $post->timestamp->format('date') ?> –
			</time>
			<?= $post->teaser ?>
		</p>
	</a>
</article>
