<?php
use \Blog\Config\Config;
use \Blog\Frontend\Web\SiteConfig;
use \Blog\Frontend\Web\Modules\TimeFormat;
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?= $PostController->post->headline ?> – <?= SiteConfig::TITLE ?></title>
		<link rel="canonical" href="<?= Config::SERVER_URL ?>/posts/<?= $PostController->post->longid ?>">
		<meta name="author" content="<?= $PostController->post->author ?>">
		<meta name="description" content="<?= $PostController->post->teaser ?>">
		<meta name="date" content="<?= TimeFormat::html_time($PostController->post->timestamp) ?>">
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<article>
				<header>

					<?php if($PostController->post->overline){ ?>
					<p class="overline"><?= $PostController->post->overline ?></p>
					<?php } ?>

					<h1><span><?= $PostController->post->headline ?></span></h1>

					<?php if($PostController->post->subline){ ?>
					<p class="subline"><?= $PostController->post->subline ?></p>
					<?php } ?>

					<p class="author-and-date">
						<!-- IDEA use address element? -->
						Von <?= $PostController->post->author ?>, <wbr>veröffentlicht am
						<time datetime="<?= TimeFormat::html_time($PostController->post->timestamp) ?>">
							<?= TimeFormat::date($PostController->post->timestamp) ?>
						</time>
					</p>
				</header>

				<?php
				if($PostController->show_picture){
					$picture = $PostController->picture;
					include COMPONENT_PATH . 'picture.comp.php';
				}
				?>

				<?= $PostController->parsed ?>
			</article>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
