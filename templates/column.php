<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title><?= $Column->name ?> – <?= $site->title ?></title>
	</head>
	<body>
		<?php include 'components/header.php'; ?>
		<main>
			<section class="highlighted">
				<h1><?= $Column->name ?></h1>
			</section>

			<section>
				<?php $pagination = $ColumnController->pagination; ?>
				<div>
					<b>Seite <?= $pagination->current_page ?> von <?= $pagination->last_page() ?></b>
					– Angezeigt werden Artikel <?= $pagination->current_item()->first_object_number() ?> bis
					<?= $pagination->current_item()->last_object_number() ?> von insgesamt <?= $pagination->total_objects ?> Artikeln
				</div>

				<?php include 'components/pagination.php'; ?>

				<?php if(count($Column->objects ?? []) == 0){ ?>
				<p>Keine Artikel gefunden.</p>
				<?php } else { $Column->each(function($post) use ($server){ include 'components/preview-post.php'; }); } ?>
			</section>
		</main>
		<?php include 'components/footer.php'; ?>
		<?php include 'components/scripts.php'; ?>
	</body>
</html>
