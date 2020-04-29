<?php
if(isset($_GET['id'])){
	try {
		$obj = Post::pull_by_id($_GET['id']);
	} catch(ObjectNotFoundException $e){
		$obj = false;
	}
} else {
	$obj = false;
}

if($obj != false){
	$post = $obj;

	?>
	<article>
		<span class="overline"><?php echo $post->overline; ?></span>
		<h1 class="headline"><?php echo $post->headline; ?></h1>
		<p class="subline"><?php echo $post->subline; ?></p>
		<p class="teaser">
			<?php echo $post->teaser; ?>
		</p>
		<span>Von <?php echo $post->author; ?> &middot; <?php echo $post->timestamp; ?></span>
		<!-- IDEA use picture element -->
		<img src="/resources/images/dynamic/
			<?php echo $post->image->longid . '.' . $post->image->extension;?>?size=large"
			alt="<?php echo $post->image->description; ?>">
		<p>
			<?php echo $post->content; ?>
		</p>
	</article>
	<?php
} else {
	echo 'Objekt nicht gefunden';
}
?>
