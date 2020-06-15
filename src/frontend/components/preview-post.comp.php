<article class="preview">
	<a href="/posts/<?= $post->longid ?>">

<?php
if(isset($post->image)){
	include COMPONENT_PATH . 'picture.comp.php';
	$picture = new Picture($post->image, 200);
	$picture->display();
}
?>

		<p class="overline"><?= $post->overline ?></p>
		<h3><span><?= $post->headline ?></span></h3>
		<p class="subline"><?= $post->subline ?></p>
		<p class="teaser">
			<time datetime="<?= to_html_time($post->timestamp) ?>">
				<?= to_date($post->timestamp) ?> –
			</time>
			<?= $post->teaser ?>
		</p>
	</a>
</article>
