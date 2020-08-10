<?php
use \Blog\Frontend\Web\Modules\TimeFormat;
$tf = new TimeFormat;
?>
<article class="preview">
	<a href="<?= $server->url ?>/posts/<?= $post->longid ?>">

<?php
if($post->show_picture){
	$picture = $post->picture;
	include COMPONENT_PATH . 'picture.comp.php';
}
?>

		<p class="overline"><?= $post->overline ?></p>
		<h3><span><?= $post->headline ?></span></h3>
		<p class="subline"><?= $post->subline ?></p>
		<p class="teaser">
			<time datetime="<?= $timeformat->html_time($post->timestamp) ?>">
				<?= $tf->date($post->timestamp) ?> –
			</time>
			<?= $post->teaser ?>
		</p>
	</a>
</article>
