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

				<?php $pagination = $Post->pagination; ?>
				<div>
					<b>Seite <?= $pagination->current_page ?> von <?= $pagination->total_pages ?></b>
					– Angezeigt werden Artikel <?= $pagination->first_object ?> bis
					<?= $pagination->last_object ?> von insgesamt <?= $pagination->total_objects ?> Artikeln
				</div>

				<?php include COMPONENT_PATH . 'pagination.comp.php'; ?>

				<?php if(empty($Post->objects)){ ?>
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
