<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.php'; ?>
		<title><?= $Column->name ?> – <?= $site->title ?></title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.php'; ?>
		<main>
			<section class="highlighted">
				<h1><?= $Column->name ?></h1>
			</section>

			<section>
				<?php
				$pagination = $ColumnController->pagination;
				if($pagination->total_pages > 1){
				?>
				<div>
					<b>Seite <?= $pagination->current_page ?> von <?= $pagination->total_pages ?></b>
					– Angezeigt werden Artikel <?= $pagination->first_object ?> bis
					<?= $pagination->last_object ?> von insgesamt <?= $pagination->total_objects ?> Artikeln
				</div>

				<?php
					include COMPONENT_PATH . 'pagination.php';
				}
				?>

				<?php if(!$Column->posts){ ?>
				<p>Keine Artikel gefunden.</p>
				<?php } ?>

				<?php
				foreach($Column->posts as $post){
					include COMPONENT_PATH . 'preview-post.php';
				}
				?>
			</section>
		</main>
		<?php include COMPONENT_PATH . 'footer.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.php'; ?>
	</body>
</html>
