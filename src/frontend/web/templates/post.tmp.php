<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?= $Post->object->headline ?> – <?= $site->title ?></title>
		<link rel="canonical" href="<?= $server->url ?>/posts/<?= $Post->object->longid ?>">
		<meta name="author" content="<?= $Post->object->author ?>">
		<meta name="description" content="<?= $Post->object->teaser ?>">
		<meta name="date" content="<?= $Post->object->timestamp->iso ?>">

		<?php if($Post->objects[0]->picture){ ?>
			<meta name="twitter:card" content="summary_large_image">
			<meta property="og:image" content="<?= $server->url . $server->dyn_img_path . $Post->object->image->longid . '/original.' . $Post->object->image->extension ?>">
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
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
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
				if($Post->object->picture){
					?>
					<figure>
						<?php
						$picture = $Post->object->picture;
						include COMPONENT_PATH . 'picture.comp.php';
						?>
						<figcaption><small><?= $picture->image->copyright ?></small></figcaption>
					</figure>
					<?php
				}
				?>

				<?= $Post->object->parsed_content ?>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
