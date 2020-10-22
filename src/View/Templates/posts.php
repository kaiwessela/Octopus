<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.php'; ?>
		<title>Alle Artikel – <?= $site->title ?></title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.php'; ?>
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

				<?php include COMPONENT_PATH . 'pagination.php'; ?>

				<?php if($Post->empty()){ ?>
				<p>Keine Artikel gefunden.</p>
				<?php } ?>

<?php
foreach($Post->objects as $post){
	include COMPONENT_PATH . 'preview-post.php';
}
?>

			</section>
		</main>
		<?php include COMPONENT_PATH . 'footer.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.php'; ?>
	</body>
</html>
