<?php
use \Blog\Config\Config;
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="<?= Config::SERVER_URL ?>/resources/css/admin.css">
	</head>
	<body>
		<header>
			<a href="<?= Config::SERVER_URL ?>/admin">Startseite</a>
			<a href="<?= Config::SERVER_URL ?>/admin/posts">Posts</a>
			<a href="<?= Config::SERVER_URL ?>/admin/images">Bilder</a>
		</header>
		<main>
			<?php $controller->display(); ?>
		</main>
		<?php // include 'imageinput.php'; ?>
		<script src="<?= Config::SERVER_URL ?>/admin/resources/js/selectableimage.js"></script>
		<script src="<?= Config::SERVER_URL ?>/admin/resources/js/imageinputpicker.js"></script>
		<script src="<?= Config::SERVER_URL ?>/admin/resources/js/imageinputuploader.js"></script>
		<script src="<?= Config::SERVER_URL ?>/admin/resources/js/imageinput.js"></script>
		<script src="<?= Config::SERVER_URL ?>/admin/resources/js/script.js"></script>
	</body>
</html>
