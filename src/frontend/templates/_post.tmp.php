<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?php echo $post->headline; ?> – Kai Florian Wessela</title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<article class="full-post">
				<header>
<?php
if(!empty($post->overline)){
	?>
					<p class="overline"><?php echo $post->overline; ?></p><!-- NOTE: is p semantically correct? -->
	<?php
}
?>
					<h1 class="headline"><?php echo $post->headline; ?></h1>
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
					<!--<img src="<?php echo $image_path . 'original.' . $ext; ?>" alt="<?php echo $image_alt; ?>">-->
				</picture>
	<?php
}
?>

				<?php echo $parsedown->text($post->content); ?>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
	</body>
</html>
