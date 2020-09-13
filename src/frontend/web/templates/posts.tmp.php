<?php
use \Blog\Frontend\Web\SiteConfig;
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title>Alle Artikel – <?= SiteConfig::TITLE ?></title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<section>
				<header class="highlighted">
					<h1>Alle Artikel</h1>
				</header>
				<div>
					<b>Seite <?= $Post->pagination->current_page ?> von <?= $Post->pagination->page_count ?></b>
					– Angezeigt werden Artikel <?= $Post->pagination->get_first_object_number() ?> bis
					<?= $Post->pagination->get_last_object_number() ?> von insgesamt <?= $Post->pagination->object_count ?> Artikeln
				</div>
				<?php $Post->pagination->display(); ?>

				<?php if($Post->error('404')){ ?>
				<p>Keine Posts gefunden.</p>
				<?php } ?>

<?php
foreach($Post->objects as $post){
	include COMPONENT_PATH . 'preview-post.comp.php';
}
?>

			</section>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
