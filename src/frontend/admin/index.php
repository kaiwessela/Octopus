<?php
# ADMIN PAGE

session_start();

# define constants
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
define('BACKEND_PATH', ROOT . 'backend/');
define('TEMPLATE_PATH', ROOT . 'templates/');
define('COMPONENT_PATH', ROOT . 'components/');
define('CONFIG_PATH', ROOT . 'config/');

require_once CONFIG_PATH . 'config.php';
require_once BACKEND_PATH . 'functions.php';
require_once BACKEND_PATH . 'exceptions.php';
require_once BACKEND_PATH . 'post.php';
require_once BACKEND_PATH . 'image.php';
require_once BACKEND_PATH . 'imagefile.php';

# establish database connection
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<base href="/admin/">
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="../resources/css/admin.css">
	</head>
	<body>
		<header>
			<a href="./">Startseite</a>
			<a href="all_posts">Posts</a>
			<a href="all_images">Bilder</a>
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
	</body>
</html>
