<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?php echo $post->headline; ?> – Kai Florian Wessela</title>
		<link rel="canonical" href="https://kaiwessela.de/posts/<?php echo $post->longid; ?>">
		<meta name="author" content="<?php echo $post->author; ?>">
		<meta name="description" content="<?php echo $post->teaser; ?>">
		<meta name="date" content="<?php echo to_html_time($post->timestamp); ?>">
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<article>
				<header>
<?php
if(!empty($post->overline)){
	?>
					<p class="overline"><?php echo $post->overline; ?></p><!-- NOTE: is p semantically correct? -->
	<?php
}
?>
					<h1><span><?php echo $post->headline; ?></span></h1>
<?php
if(!empty($post->subline)){
	?>
					<p class="subline"><?php echo $post->subline; ?></p>
	<?php
}
?>
					<p class="author-and-date">
						<!-- IDEA use address element? -->
						Von <?php echo $post->author; ?>, <wbr>veröffentlicht am
						<time datetime="<?php echo to_html_time($post->timestamp); ?>">
							<?php echo to_date($post->timestamp); ?>
						</time>
					</p>
				</header>

<?php
if(isset($post->image)){
	$image_path = '/resources/images/dynamic/' . $post->image->longid . '/';
	$ext = $post->image->extension;
	$image_alt = $post->image->description;

	?>
				<picture>
					<!-- IDEA add different sources -->
					<img src="<?php echo $image_path . 'original.' . $ext; ?>" alt="<?php echo $image_alt; ?>">
				</picture>
	<?php
}
?>

				<?php echo $parsedown->text($post->content); ?>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<script src="/resources/js/script.js"></script>
	</body>
</html>
