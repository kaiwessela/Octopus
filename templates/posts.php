<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title>Alle Artikel – <?= $site->title ?></title>
	</head>
	<body>
		<?php include 'components/header.php'; ?>
		<main>
			<section>
				<header class="highlighted">
					<h1>Alle Artikel</h1>
				</header>

				<?php $pagination = $PostController->pagination; ?>
				<div>
					<b>Seite <?= $pagination->current_page ?> von <?= $pagination->last_page() ?></b>
					– Angezeigt werden Artikel <?= $pagination->current_item()->first_object_number() ?> bis
					<?= $pagination->current_item()->last_object_number() ?> von insgesamt <?= $pagination->total_objects ?> Artikeln
				</div>

				<?php include 'components/pagination.php'; ?>

				<?php if($PostController->status('empty')){ ?>
				<p>Keine Artikel gefunden.</p>
			<?php } else { $Post->each(function($post) use ($server){ include 'components/preview-post.php'; }); } ?>

			</section>
		</main>
		<?php include 'components/footer.php'; ?>
		<?php include 'components/scripts.php'; ?>
	</body>
</html>
