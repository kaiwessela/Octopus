<?php
namespace Blog\Config;

class Routes {
	const ROUTES = [
		[
			'path' => '/^\/$/',
			'template' => 'index',
			'controller' => 'IndexController'
		],
		[
			'path' => '/^posts\/[0-9]{0,8}$/',
			'template' => 'posts',
			'controller' => 'PostListController'
		],
		[
			'path' => '/^posts\/.{9,}$/',
			'template' => 'post',
			'controller' => 'PostController'
		],
		[
			'path' => '/^p\/.{8}$/',
			'template' => 'post',
			'controller' => 'PostController'
		]
	];

	const DEFAULT_ROUTE = [
		'template' => 'default',
		'controller' => 'Controller'
	];

	const TITLES = [
		# 'path (not as regex)' => 'Title',
	];
}
?>
