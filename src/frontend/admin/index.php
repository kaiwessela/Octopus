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
	</head>
	<body>
		<header>
			<a href="all_posts">Alle Posts</a>
			<a href="all_images">Alle Bilder</a>
		</header>
		<main>
			<?php
			if(isset($_GET['page']) && file_exists('pages/' . $_GET['page'] . '.adm.php')){
				include 'pages/' . $_GET['page'] . '.adm.php';
			} else {
				echo 'Startseite Admin';
			}
			?>
		</main>
	</body>
</html>
