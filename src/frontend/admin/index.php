<?php
# ADMIN PAGE
session_start();
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
require_once ROOT . 'share/endpoint_common.php';

define('ADMIN_URL', SERVER_URL . '/admin');

require_once BACKEND_PATH . 'post.php';
require_once BACKEND_PATH . 'image.php';
require_once BACKEND_PATH . 'imagemanager.php';

$imagemanager = new ImageManager(ROOT . 'resources/images/dynamic'); # TODO make as constant

?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="<?= SERVER_URL ?>/resources/css/admin.css">
	</head>
	<body>
		<header>
			<a href="<?= ADMIN_URL ?>">Startseite</a>
			<a href="<?= ADMIN_URL ?>/all_posts">Posts</a>
			<a href="<?= ADMIN_URL ?>/all_images">Bilder</a>
		</header>
		<main>
			<?php
			if(isset($_GET['page'])){
				$page = 'pages/' . $_GET['page'] . '.adm.php';
				$not_found = false;

				if(!file_exists($page)){
					$page = 'pages/start.adm.php';
					$not_found = true;
				}
			} else {
				$page = 'pages/start.adm.php';
				$not_found = false;
			}

			include $page;
			?>
		</main>
		<?php include ROOT . 'admin/templates/imageinput.php'; ?>
		<script src="<?= ADMIN_URL ?>/js/selectableimage.js"></script>
		<script src="<?= ADMIN_URL ?>/js/imageinputpicker.js"></script>
		<script src="<?= ADMIN_URL ?>/js/imageinputuploader.js"></script>
		<script src="<?= ADMIN_URL ?>/js/imageinput.js"></script>
		<script src="<?= ADMIN_URL ?>/js/script.js"></script>
	</body>
</html>
