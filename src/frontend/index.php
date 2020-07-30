<?php
/* ################################
TODO change htaccess based on api htaccess

# HOW THIS SYSTEM WORKS
This lightweight system provides a simple content management system and basic blog functionality.
Some essential pages like the startpage are predefined and cannot be removed.
You can add as much additional pages as you want simply by adding a template file. The name of that
file determines the path of that page (test.tmp.php -> example.org/test; see ROUTING for details).

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
?.comp.php	–	Component; there are no set rules for component structure or predefined components.
				Structure your site and components how it fits best for you

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

define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');

require_once ROOT . 'config/endpoint_common.php';

require_once BACKEND_PATH . 'post.php';
require_once BACKEND_PATH . 'image.php';
require_once FRONTEND_INCLUDES . 'functions.php';
require_once FRONTEND_INCLUDES . 'pagination.php';

require_once LIBS_PATH . 'parsedown/Parsedown.php';

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
	if(!isset($qs_post) || strlen($qs_post) < 8){
		# posts list
		try {
			$post_count = Post::count();
			$page_requested = (int)($qs_post ?? 1);
			$pagination = new Pagination($post_count, PREVIEW_POSTS_PER_PAGE, $page_requested);
		} catch(InvalidArgumentException $e){
			return_404();
		} catch(Exception $e){
			// 500 page
		}

		// BUG this leads to 404 if no posts are available, but should show a 'zero posts' found
		if($pagination->current_page_exists()){
			include TEMPLATE_PATH . '_posts.tmp.php';
		} else {
			return_404();
		}

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
		# redirect to the regular posts list page because /p is not intended to be used as a
		# regular page
		http_response_code(308);
		header('Location: /posts');
		exit;

	}
} else {
	# some other page is requested
	# check if the requested page exists as a template file
	# check also if qs_page contains any illegal characters to prevent an injection
	$template_file = TEMPLATE_PATH . $qs_page . '.tmp.php';
	if(preg_match('/^[a-zA-Z0-9_.-]*$/', $qs_page) && file_exists($template_file)){
		include $template_file;

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
