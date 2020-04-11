<?php
/* ###

/					– Startseite
/posts				– Alle Posts
/posts/[longid]		– Einzelner Post
/p/[id]				– Shortlink auf einzelnen Post
/*					– Statische Seite

*/ ###
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

# establish database connection
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

# read query string
$qs_page = $_GET['page'] ?? null;
$qs_post = $_GET['post'] ?? null;

# routing mechanism
if(!isset($qs_page)){
	# main page is requested
	include TEMPLATE_PATH . '_index.tmp.php';

} else if($qs_page == 'posts') {
	# determine whether a single post or a posts list is requested
	if(!isset($qs_post)){
		# posts list
		include TEMPLATE_PATH . '_posts.tmp.php';

	} else {
		# single post
		# try to pull the requested post
		try {
			$post = Post::pull_by_longid($qs_post);
		} catch(ObjectNotFoundException $e){
			# requested post was not found
			return_404();
		}

		include TEMPLATE_PATH . '_post.tmp.php';

	}
} else if($qs_page == 'p') {
	if(isset($qs_post)){
		// IDEA redirect to the long version by default
		# try to pull the requested post
		try {
			$post = Post::pull_by_id($qs_post);
		} catch(ObjectNotFoundException $e){
			# requested post was not found
			return_404();
		}

		include TEMPLATE_PATH . '_post.tmp.php';

	} else {
		# redirect to the regular posts list page because /p is not intended to be used as a regular page
		http_status_code(308);
		header('Location: /posts');
		exit;

	}
} else {
	# some other page is requested
	# check if the requested page exists as a template file
	if(file_exists(TEMPLATE_PATH . '/' . $qs_page . '.tmp.php')){
		include TEMPLATE_PATH . '/' . $qs_page . '.tmp.php';

	} else {
		# the requested page could not be found
		return_404();

	}
}

function return_404() {
	http_response_code(404);
	include TEMPLATE_PATH . '_404.tmp.php';
	exit;
}

echo $qs_page;
echo $qs_post;
?>
