<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title><?= $title ?></title>
	</head>
	<body>
		<?php include 'components/header.php'; ?>
		<main>
			<section class="highlighted">
				<h1>Hallo.</h1>
			</section>
			<section>
				<h2>Neueste Posts</h2>
				<?php $Posts->each(function($post) use ($server) { include 'components/preview-post.php'; }); ?>
			</section>
		</main>
		<?php include 'components/footer.php'; ?>
		<?php include 'components/scripts.php'; ?>
	</body>
</html>
