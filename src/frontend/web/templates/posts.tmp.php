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
					<b>Seite <?= $pagination->current_page ?> von <?= $pagination->page_count ?></b>
					– Angezeigt werden Artikel <?= $pagination->get_first_object_number() ?> bis
					<?= $pagination->get_last_object_number() ?> von insgesamt <?= $pagination->object_count ?> Artikeln
				</div>
				<?php $pagination->display(); ?>

<?php
foreach($posts as $post){
	include COMPONENT_PATH . 'preview-post.comp.php';
}
?>

			</section>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
