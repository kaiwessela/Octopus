<?php
namespace Blog\Config;

class Routes {
	const ROUTES = [
		[
			'path' => '/^$/',
			'template' => 'index',
			'handler' => 'Index'
		],
		[
			'path' => '/^posts\/[0-9]{0,8}$/',
			'template' => 'posts',
			'handler' => 'PostList'
		],
		[
			'path' => '/^posts\/.{9,}$/',
			'template' => 'post',
			'handler' => 'PostController'
		],
		[
			'path' => '/^p\/.{8}$/',
			'template' => 'post',
			'handler' => 'PostController'
		]
	];

	const DEFAULT_ROUTE = [
		'template' => 'default',
		'handler' => 'Controller'
	];
}
?>
