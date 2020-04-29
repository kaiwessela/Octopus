<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<base href="home.local">
		<link rel="stylesheet" type="text/css" href="resources/css/style.css">
		<title><?php echo $post->headline; ?> – Kai Wessela</title>
	</head>
	<body>
		<header>

		</header>
		<main>
			<article>
				<span class="overline"><?php echo $post->overline; ?></span>
				<h1 class="headline"><?php echo $post->headline; ?></h1>
				<p class="subline"><?php echo $post->subline; ?></p>
				<p class="teaser">
					<?php echo $post->teaser; ?>
				</p>
				<span>Von <?php echo $post->author; ?> &middot; <?php echo to_date($post->timestamp); ?></span>
				<!-- IDEA use picture element -->
				<img src="/resources/images/dynamic/
					<?php echo $post->image->longid . '.' . $post->image->extension;?>?size=large"
					alt="<?php echo $post->image->description; ?>">
				<p>
					<?php echo $post->content; ?>
				</p>
			</article>
		</main>
		<footer>

		</footer>
	</body>
</html>
