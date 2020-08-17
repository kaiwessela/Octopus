<?php
namespace Blog\Config;

class Routes {
	const ROUTES = [
		[
			'path' => '/^\/$/',
			'template' => 'index',
			'controllers' => [
				'PostListController' => [
					'limit' => 5,
					'offset' => 0
				]
			]
		],
		[
			'path' => '/^posts\/[0-9]{0,8}$/',
			'template' => 'posts',
			'controllers' => [
				'PostListController' => []
			]
		],
		[
			'path' => '/^posts\/.{9,}$/',
			'template' => 'post',
			'controllers' => [
				'PostController' => []
			]
		],
		[
			'path' => '/^p\/.{8}$/',
			'template' => 'post',
			'controller' => [
				'PostController' => []
			]
		]
	];

	const STATIC_ROUTE = [
		'template' => 'static',
		'controllers' => [
			'StaticController' => []
		]
	];

	const TITLES = [
		#'path' => 'Titel'
	];
}
?>
