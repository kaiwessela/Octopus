<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?php echo $post->headline; ?> – Kai Wessela</title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<article class="full-post">
				<header>
					<p class="overline"><?php echo $post->overline; ?></p>
					<h1 class="headline"><?php echo $post->headline; ?></h1>
					<p class="subline"><?php echo $post->subline; ?></p>
					<!-- IDEA rename to summary? -->
					<p class="teaser">
						<?php echo $post->teaser; ?>
					</p>
					<p class="author"><!-- TEMP rename this class and change structure -->
						<!-- IDEA use address element? -->
						Von <?php echo $post->author; ?> &middot;
						<time datetime="<?php echo to_html_time($post->timestamp); ?>">
							<?php echo to_date($post->timestamp); ?>
						</time>
					</p>
				</header>

<?php
if(isset($post->image)){
	$image_url = '/resources/images/dynamic/' . $post->image->longid . '.' . $post->image->extension . '?size=large';
	$image_alt = $post->image->description;

	?>
				<picture>
					<!-- IDEA add different sources -->
					<img src="<?php echo $image_url; ?>" alt="<?php echo $image_alt; ?>">
				</picture>
	<?php
}
?>

				<p>
					<?php echo $post->content; // TODO parsedown ?>
				</p>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
	</body>
</html>
