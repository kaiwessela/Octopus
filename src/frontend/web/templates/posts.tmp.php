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
					<b>Seite <?= $PostListController->pagination->current_page ?> von <?= $PostListController->pagination->page_count ?></b>
					– Angezeigt werden Artikel <?= $PostListController->pagination->get_first_object_number() ?> bis
					<?= $PostListController->pagination->get_last_object_number() ?> von insgesamt <?= $PostListController->pagination->object_count ?> Artikeln
				</div>
				<?php $PostListController->pagination->display(); ?>

				<?php if($PostListController->show_no_posts_found){ ?>
				<p>Keine Posts gefunden.</p>
				<?php } ?>

<?php
foreach($PostListController->posts as $post){
	include COMPONENT_PATH . 'preview-post.comp.php';
}
?>

			</section>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
