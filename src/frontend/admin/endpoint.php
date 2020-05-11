<?php
// DEPRECATED

# ADMIN BACKEND ENDPOINT

session_start();

# define constants
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
define('BACKEND_PATH', ROOT . 'backend/');
define('TEMPLATE_PATH', ROOT . 'templates/');
define('COMPONENT_PATH', ROOT . 'components/');
define('CONFIG_PATH', ROOT . 'config/');

require_once CONFIG_PATH . 'config.php';
require_once BACKEND_PATH . 'exceptions.php';
require_once BACKEND_PATH . 'post.php';
require_once BACKEND_PATH . 'image.php';
require_once BACKEND_PATH . 'imagefile.php';

# establish database connection
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

if($_SERVER['REQUEST_METHOD'] == 'GET'){
	$mode = 'get';
} else if($_SERVER['CONTENT_TYPE'] == 'application/json') {
	$mode = 'post';
	$type = 'json';
} else if($_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded'){
	$mode = 'post';
	$type = 'urlencoded';
} else if($_SERVER['CONTENT_TYPE'] == 'multipart/form-data'){
	$mode = 'post';
	$type = 'multipart';
} else {
	send_response(415);
	exit;
}

function send_response($code) {
	http_response_code($code);

	switch($code){
		case 400:
			echo 'Invalid or No Class';
			break;
		case 404:
			echo 'Object Not Found';
			break;
		case 415:
			echo 'Invalid Content Type;' . PHP_EOL;
			echo 'Allowed Content Types: application/json, application/x-ww-form-urlencoded, multipart/form-data';
			break;
		default:
			break;
	}
}
?>
