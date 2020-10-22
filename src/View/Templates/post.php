<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.php'; ?>
		<title><?= $Post->object->headline ?> – <?= $site->title ?></title>
		<link rel="canonical" href="<?= $server->url ?>/posts/<?= $Post->object->longid ?>">
		<meta name="author" content="<?= $Post->object->author ?>">
		<meta name="description" content="<?= $Post->object->teaser ?>">
		<meta name="date" content="<?= $Post->object->timestamp->iso ?>">

		<?php if(!empty($Post->object->image)){ ?>
			<meta name="twitter:card" content="summary_large_image">
			<meta property="og:image" content="<?= $Post->object->image->source_original ?>">
		<?php } else { ?>
			<meta name="twitter:card" content="summary">
		<?php } ?>

		<meta name="twitter:site" content="<?= $site->twitter ?>">

		<meta property="og:type" content="article">
		<meta property="og:url" content="<?= $server->url ?>/posts/<?= $Post->object->longid ?>">
		<meta property="og:title" content="<?= $Post->object->headline ?>">
		<meta property="og:description" content="<?= $Post->object->teaser ?>">
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.php'; ?>
		<main>
			<article class="post">
				<header>

					<?php if($Post->object->overline){ ?>
					<p class="overline"><?= $Post->object->overline ?></p>
					<?php } ?>

					<h1><span><?= $Post->object->headline ?></span></h1>

					<?php if($Post->object->subline){ ?>
					<p class="subline"><?= $Post->object->subline ?></p>
					<?php } ?>

					<p class="author-and-date">
						<!-- IDEA use address element? -->
						Von <?= $Post->object->author ?>, <wbr>veröffentlicht am
						<time datetime="<?= $Post->object->timestamp->iso ?>">
							<?= $Post->object->timestamp->date ?>
						</time>
					</p>
				</header>

				<?php
				if(!empty($Post->object->image)){
					?>
					<figure>
						<?php
						$picture = $Post->object->image;
						include COMPONENT_PATH . 'picture.php';
						?>
						<figcaption><small><?= $picture->copyright ?></small></figcaption>
					</figure>
					<?php
				}
				?>

				<?= $Post->object->content->parsed ?>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.php'; ?>
	</body>
</html>
