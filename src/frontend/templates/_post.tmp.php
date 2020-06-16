<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?= $post->headline ?> – Kai Florian Wessela</title>
		<link rel="canonical" href="https://kaiwessela.de/posts/<?= $post->longid ?>">
		<meta name="author" content="<?= $post->author ?>">
		<meta name="description" content="<?= $post->teaser ?>">
		<meta name="date" content="<?= to_html_time($post->timestamp) ?>">
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<article>
				<header>
<?php
if(!empty($post->overline)){
	?>
					<p class="overline"><?= $post->overline ?></p><!-- NOTE: is p semantically correct? -->
	<?php
}
?>
					<h1><span><?= $post->headline ?></span></h1>
<?php
if(!empty($post->subline)){
	?>
					<p class="subline"><?= $post->subline ?></p>
	<?php
}
?>
					<p class="author-and-date">
						<!-- IDEA use address element? -->
						Von <?= $post->author ?>, <wbr>veröffentlicht am
						<time datetime="<?= to_html_time($post->timestamp) ?>">
							<?= to_date($post->timestamp) ?>
						</time>
					</p>
				</header>

<?php
if(isset($post->image)){
	include COMPONENT_PATH . 'picture.comp.php';
	$picture = new Picture($post->image, 600);
	$picture->display();
}
?>

				<?= $parsedown->text($post->content) ?>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<script src="/resources/js/script.js"></script>
	</body>
</html>
