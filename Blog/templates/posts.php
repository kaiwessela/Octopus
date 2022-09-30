<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title>Alle Artikel – <?= $title ?></title>
	</head>
	<body>
		<?php include 'components/header.php'; ?>
		<main>
			<section>
				<header class="highlighted">
					<h1>Alle Artikel</h1>
				</header>

				<?php $pagination = $PostsController->pagination; ?>
				<div>
					<?php if($pagination->page_exists($pagination->current_page)){ ?>
						<b>Seite <?= $pagination->current_page ?> von <?= $pagination->last_page() ?></b>
						<?php if($Posts->is_empty()){ ?>
							– Bislang sind keine Artikel vorhanden.
						<?php } else { ?>
							– Angezeigt werden Artikel <?= $pagination->current_item()?->first_object_number() ?> bis
							<?= $pagination->current_item()?->last_object_number() ?> von insgesamt
							<?= $pagination->total_objects ?> Artikeln.
						<?php } ?>
					<?php } else { ?>
						<b>Diese Seite existiert nicht.</b>
						Sie haben Seite <?= $pagination->current_page ?> der Artikelliste aufgerufen. Die vorhandenen
						Artikel reichen jedoch nur bis zur Seite <?= $pagination->last_page() ?>. Über die folgende
						Navigation gelangen Sie zu den gültigen Seiten zurück.
					<?php } ?>
				</div>

				<?php include 'components/pagination.php'; ?>

				<?php if($Posts->is_empty()){ ?>
				<p>Keine Artikel vorhanden.</p>
			<?php } else { $Posts->each(function($post) use ($server){ include 'components/preview-post.php'; }); } ?>

			</section>
		</main>
		<?php include 'components/footer.php'; ?>
		<?php include 'components/scripts.php'; ?>
	</body>
</html>
