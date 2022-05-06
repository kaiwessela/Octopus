<?php $entityname = $AC->get_entity_name(); ?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title><?= $AC->lang('list.title') ?> – OctopusAdmin</title>
	</head>
	<body>
		<header>
			<?php include 'components/logo.php'; ?>
			<h1><?= $AC->lang('list.title') ?></h1>
			<?php include 'components/login.php'; ?>
		</header>
		<nav id="navigation">
			<?php include 'components/nav.php'; ?>
		</nav>
		<main id="main">
			<div id="messages">

			</div>
			<a href="/admin/<?= $entityname ?>/new" class="add-new"><?= $AC->lang('list.add-new') ?></a>
			<div class="pagination"><?php $pagination = $EntitiesController->pagination; ?>
				<?php if($pagination->page_exists($pagination->current_page)){ ?>
					<b>Seite <?= $pagination->current_page ?> von <?= $pagination->last_page() ?></b>
					<?php if($Entities->is_empty()){ ?>
						– Bislang sind keine Objekte vorhanden.
					<?php } else { ?>
						– Angezeigt werden Objekte <?= $pagination->current_item()?->first_object_number() ?> bis
						<?= $pagination->current_item()?->last_object_number() ?> von insgesamt
						<?= $pagination->total_objects ?> Objekten.
					<?php } ?>
				<?php } else { ?>
					<b>Diese Seite existiert nicht.</b>
					Sie haben Seite <?= $pagination->current_page ?> der Objektliste aufgerufen. Die vorhandenen
					Objekte reichen jedoch nur bis zur Seite <?= $pagination->last_page() ?>. Über die folgende
					Navigation gelangen Sie zu den gültigen Seiten zurück.
				<?php } ?>
			</div>

			<!-- TODO pagination -->

			<section class="list">
				<?php if($Entities->is_empty()){ ?>
					<p><?= $AC->lang('list.is-empty') ?></p>
				<?php } else { $Entities->each(function ($entity) use ($entityname, $AC){ ?>
					<article>
						<?php include "entities/{$entityname}/list-preview.php"; ?>
						<div>
							<a href="/admin/<?= $entityname ?>/<?= $entity->id ?>/edit" class="edit">Bearbeiten</a>
							<a href="/admin/<?= $entityname ?>/<?= $entity->id ?>/delete" class="delete">Löschen</a>
							<?php if($AC->has_live_view()){ ?>
								<a href="<?= $AC->live_view($entity) ?>" class="external">Auf der Website ansehen</a>
							<?php } ?>
						</div>
					</article>
				<?php });} ?>
			</section>
		</main>
	</body>
</html>
