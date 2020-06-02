<?php
/* ################################

# HOW THIS SYSTEM WORKS
This lightweight system provides a simple content management system and basic blog functionality.
Some essential pages like the startpage are predefined and cannot be removed.
You can add as much additional pages as you want simply by adding a template file. The name of that file
determines the path of that page (i.e. test.tmp.php -> example.org/test; see ROUTING for details).

# TEMPLATE / COMPONENT NOMENCLATURE
## Difference between Template and Component:
A Template file contains a complete page.
A Component file only contains a part of a page.
Static parts of a page (i.e. header, footer ...) can be outsourced into Component files.
Components can then be included back into Templates.
Components cannot act as a single page.
Templates cannot include Templates.
Components can include other Components.

## Nomenclature
_?.tmp.php	–	predefined Template; used for essential functionality and cannot be removed
?.tmp.php	–	custom Template; use this for your own pages
?.comp.php	–	Component; there are no set rules for component structure or predefined components. Structure your
				site and components how it fits best for you

Template files must be located in the frontend/templates/ folder.
Component files must be located in the frontend/components/ folder.

## Predefined Templates
_404.tmp.php
_index.tmp.php
_post.tmp.php
_posts.tmp.php

# ROUTING
/					– startpage -> _index.tmp.php
/posts				– all posts -> _posts.tmp.php
/posts/[longid]		– single post -> _post.tmp.php
/p/[id]				– shortlink to a single post, same content as /posts/[longid]
/[page]				– static page -> [page].tmp.php

*/ ################################

session_start();

setlocale(LC_ALL, 'de_DE.utf-8');

# define constants
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
define('BACKEND_PATH', ROOT . 'backend/');
define('TEMPLATE_PATH', ROOT . 'templates/');
define('COMPONENT_PATH', ROOT . 'components/');
define('CONFIG_PATH', ROOT . 'config/');
define('LIBS_PATH', ROOT . 'libs/');

require_once CONFIG_PATH . 'config.php';
require_once BACKEND_PATH . 'functions.php';
require_once BACKEND_PATH . 'exceptions.php';
require_once BACKEND_PATH . 'contentobject.php';
require_once BACKEND_PATH . 'post.php';
require_once BACKEND_PATH . 'image.php';
require_once 'functions.php';

require_once LIBS_PATH . 'parsedown/Parsedown.php';

# establish database connection
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

# create a parsedown instance
$parsedown = new Parsedown();

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
		http_response_code(308);
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
?>
