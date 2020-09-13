<?php
use \Blog\Config\Config;
use \Blog\Frontend\Web\SiteConfig;
use \Blog\Frontend\Web\Modules\TimeFormat;
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?= $Post->objects[0]->headline ?> – <?= SiteConfig::TITLE ?></title>
		<link rel="canonical" href="<?= Config::SERVER_URL ?>/<?= SiteConfig::CANONICAL_URL_PREFIX ?>/<?= $Post->objects[0]->longid ?>">
		<meta name="author" content="<?= $Post->objects[0]->author ?>">
		<meta name="description" content="<?= $Post->objects[0]->teaser ?>">
		<meta name="date" content="<?= TimeFormat::html_time($Post->objects[0]->timestamp) ?>">

		<?php if($Post->objects[0]->picture){ ?>
			<meta name="twitter:card" content="summary_large_image">
			<meta property="og:image" content="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . $Post->objects[0]->image->longid . '/original.' . $Post->objects[0]->image->extension ?>">
		<?php } else { ?>
			<meta name="twitter:card" content="summary">
		<?php } ?>

		<meta name="twitter:site" content="<?= SiteConfig::TWITTER_SITE ?>">

		<meta property="og:type" content="article">
		<meta property="og:url" content="<?= Config::SERVER_URL ?>/<?= SiteConfig::CANONICAL_URL_PREFIX ?>/<?= $Post->objects[0]->longid ?>">
		<meta property="og:title" content="<?= $Post->objects[0]->headline ?>">
		<meta property="og:description" content="<?= $Post->objects[0]->teaser ?>">
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<article class="post">
				<header>

					<?php if($Post->objects[0]->overline){ ?>
					<p class="overline"><?= $Post->objects[0]->overline ?></p>
					<?php } ?>

					<h1><span><?= $Post->objects[0]->headline ?></span></h1>

					<?php if($Post->objects[0]->subline){ ?>
					<p class="subline"><?= $Post->objects[0]->subline ?></p>
					<?php } ?>

					<p class="author-and-date">
						<!-- IDEA use address element? -->
						Von <?= $Post->objects[0]->author ?>, <wbr>veröffentlicht am
						<time datetime="<?= TimeFormat::html_time($Post->objects[0]->timestamp) ?>">
							<?= TimeFormat::date($Post->objects[0]->timestamp) ?>
						</time>
					</p>
				</header>

				<?php
				if($Post->objects[0]->picture){
					?>
					<figure>
						<?php
						$picture = $Post->objects[0]->picture;
						include COMPONENT_PATH . 'picture.comp.php';
						?>
						<figcaption><small><?= $picture->image->copyright ?></small></figcaption>
					</figure>
					<?php
				}
				?>

				<?= $Post->objects[0]->parsed_content ?>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
