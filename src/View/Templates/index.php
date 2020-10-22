<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.php'; ?>
		<title><?= $site->title ?></title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.php'; ?>
		<main>
			<section class="highlighted">
				<h1>Hallo.</h1>
			</section>
			<section>
				<h2>Neueste Posts</h2>
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
