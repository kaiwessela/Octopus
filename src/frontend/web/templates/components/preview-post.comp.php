<article class="post preview">
	<a href="<?= $server->url ?>/posts/<?= $post->longid ?>">

<?php
if($post->image->id){
	$picture = $post->picture;
	include COMPONENT_PATH . 'picture.comp.php';
}
?>

		<p class="overline"><?= $post->overline ?></p>
		<h3><span><?= $post->headline ?></span></h3>
		<p class="subline"><?= $post->subline ?></p>
		<p class="teaser">
			<time datetime="<?= $post->timestamp->iso ?>">
				<?= $post->timestamp->date ?> –
			</time>
			<?= $post->teaser ?>
		</p>
	</a>
</article>
