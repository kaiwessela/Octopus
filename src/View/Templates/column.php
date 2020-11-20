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
				<?php if(empty($Column->posts)){ ?>
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
