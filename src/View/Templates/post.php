<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.php'; ?>
		<title><?= $Post->headline ?> – <?= $site->title ?></title>
		<link rel="canonical" href="<?= $server->url ?>/posts/<?= $Post->longid ?>">
		<meta name="author" content="<?= $Post->author ?>">
		<meta name="description" content="<?= $Post->teaser ?>">
		<meta name="date" content="<?= $Post->timestamp->iso() ?>">

		<?php if($Post->image){ ?>
			<meta name="twitter:card" content="summary_large_image">
			<meta property="og:image" content="<?= $Post->image->src() ?>">
		<?php } else { ?>
			<meta name="twitter:card" content="summary">
			<!-- TODO add og:image -->
		<?php } ?>

		<meta name="twitter:site" content="<?= $site->twitter ?>">

		<meta property="og:type" content="article">
		<meta property="og:url" content="<?= $server->url ?>/posts/<?= $Post->longid ?>">
		<meta property="og:title" content="<?= $Post->headline ?>">
		<meta property="og:description" content="<?= $Post->teaser ?>">
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.php'; ?>
		<main>
			<article class="post">
				<header>
					<p class="overline"><?= $Post->overline ?></p>
					<h1><span><?= $Post->headline ?></span></h1>
					<p class="subline"><?= $Post->subline ?></p>
					<p class="author-and-date">
						<!-- IDEA use address element? -->
						Von <?= $Post->author ?>, <wbr>veröffentlicht am
						<time datetime="<?= $Post->timestamp->iso() ?>">
							<?= $Post->timestamp->format('date') ?>
						</time>
					</p>
				</header>

				<?php if($Post->image){ ?>
				<figure>
					<picture>
						<source srcset="<?= $Post->image->srcset() ?>">
						<img src="<?= $Post->image->src() ?>" alt="<?= $Post->image->description ?>">
					</picture>
					<figcaption><small><?= $Post->image->copyright ?></small></figcaption>
				</figure>
				<?php } ?>

				<?= $Post->content?->parse() ?>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.php'; ?>
	</body>
</html>
