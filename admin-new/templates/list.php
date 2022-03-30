<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title>Alle Artikel – OctopusAdmin</title>
	</head>
	<body>
		<header>
			<?php include 'components/logo.php'; ?>
			<h1>Alle Artikel</h1>
			<?php include 'components/login.php'; ?>
		</header>
		<nav id="navigation">
			<?php include 'components/nav.php'; ?>
		</nav>
		<main id="main">
			<div id="messages">

			</div>
			<a href="/admin/posts/new" class="add-new">
				Neuen Artikel schreiben
			</a>
			<div class="pagination"><?php $pagination = $EntitiesController->pagination; ?>
				<?php if($pagination->page_exists($pagination->current_page)){ ?>
					<b>Seite <?= $pagination->current_page ?> von <?= $pagination->last_page() ?></b>
					<?php if($Entities->is_empty()){ ?>
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

			<!-- TODO pagination -->

			<section class="list">
				<?php if($Entities->is_empty()){ ?>
				<p>Keine Artikel vorhanden.</p>
				<?php } else {
					$Entities->each(function($entity){
						include 'entities/posts/list-preview.php';
					});
				} ?>
			</section>
		</main>
	</body>
</html>
