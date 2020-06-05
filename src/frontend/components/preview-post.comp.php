<article class="preview-post">
	<a href="/posts/<?php echo $post->longid; ?>">

<?php
if(isset($post->image)){
$image_path = '/resources/images/dynamic/' . $post->image->longid . '/';
$ext = $post->image->extension;
$image_alt = $post->image->description;

?>
		<picture>
			<img src="<?php echo $image_path . 'original.' . $ext; ?>" alt="<?php echo $image_alt; ?>">
		</picture>
<?php
}
?>

		<p class="overline"><?php echo $post->overline; ?></p>
		<h3><?php echo $post->headline; ?></h3>
		<p class="subline"><?php echo $post->subline; ?></p>
		<p class="teaser">
			<time datetime="<?php echo to_html_time($post->timestamp); ?>">
				<?php echo to_date($post->timestamp); ?> –
			</time>
			<?php echo $post->teaser; ?>
		</p>
	</a>
</article>
