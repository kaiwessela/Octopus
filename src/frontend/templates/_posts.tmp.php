<?php
$posts = Post::pull_all();
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title>Alle Artikel – Kai Florian Wessela</title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<section>
				<h1>Alle Artikel</h1>
				<p><b>Seite 1 von x</b> – Angezeigt werden Artikel 1 bis 10 von insgesamt 500 Artikeln</p>
			</section>

<?php
foreach($posts as $post){
	?>
			<article class="preview-post">
				<a href="/posts/<?php echo $post->longid; ?>">
					<p class="overline"><?php echo $post->overline; ?></p>
					<h3><?php echo $post->headline; ?></h3>
					<p class="subline"><?php echo $post->subline; ?></p>
				</a>
				<p class="teaser">
					<time datetime="<?php echo to_html_time($post->timestamp); ?>">
						<?php echo to_date($post->timestamp); ?>&nbsp;–&nbsp;
					</time>
					<?php echo $post->teaser; ?>&nbsp;…
				</p>
			</article>
	<?php
}
?>

		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
	</body>
</html>
