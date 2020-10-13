<?php
namespace Blog\Config;

/*
REGEX REWRITER

"/^…$/" remains the same

"…" => /^…$/
"/" => /^$/
"*" => /^.+$/
/ => \/
# => [0-9] (+ except if next char is +, ?, * or {)
* => [^\/] (+ except if next char is +, ?, * or {)
*/

class Routes {
	const ROUTES = [
		[
			'path' => '/^$/',
			'template' => 'index',
			'controllers' => [
				'PostController' => [
					'action' => 'list',
					'amount' => 5
				],
				'EventController' => [
					'action' => 'list',
					'amount' => 5
				]
			]
		],
		[
			'path' => '/^posts(\/[0-9]{0,8})?$/',
			'template' => 'posts',
			'controllers' => [
				'PostController' => [
					'action' => 'list',
					'amount' => 5,
					'page' => '?2',
					'pagination' => [
						'structure' => 'default',
						'base_path' => 'posts'
					]
				]
			]
		],
		[
			'path' => '/^posts\/.{9,}$/',
			'template' => 'post',
			'controllers' => [
				'PostController' => [
					'action' => 'show',
					'identifier' => '?2'
				]
			]
		],
		[
			'path' => '/^p\/.{8}$/',
			'template' => 'post',
			'controllers' => [
				'PostController' => [
					'action' => 'show',
					'identifier' => '?2'
				]
			]
		],
		[
			'path' => '/^admin$/',
			'template' => 'admin/main',
			'controllers' => [

			],
			'auth' => true
		],
		[
			'path' => '/^admin\/[a-z]+(\/[0-9]{0,8})?$/',
			'template' => 'admin/?2',
			'controllers' => [
				'?2' => [
					'action' => 'list',
					'amount' => 20,
					'page' => '?3',
					'pagination' => [
						'structure' => 'admin',
						'base_path' => 'admin/?2'
					]
				]
			],
			'auth' => true
		],
		[
			'path' => '/^admin\/[a-z]+\/new$/',
			'template' => 'admin/?2',
			'controllers' => [
				'?2' => [
					'action' => 'new'
				]
			],
			'auth' => true
		],
		[
			'path' => '/^admin\/[a-z]+\/[^\/]+$/',
			'template' => 'admin/?2',
			'controllers' => [
				'?2' => [
					'action' => 'show',
					'identifier' => '?3'
				]
			],
			'auth' => true
		],
		[
			'path' => '/^admin\/[a-z]+\/[^\/]+\/edit$/',
			'template' => 'admin/?2',
			'controllers' => [
				'?2' => [
					'action' => 'edit',
					'identifier' => '?3',
				]
			],
			'auth' => true
		],
		[
			'path' => '/^admin\/[a-z]+\/[^\/]+\/delete$/',
			'template' => 'admin/?2',
			'controllers' => [
				'?2' => [
					'action' => 'delete',
					'identifier' => '?3',
				]
			],
			'auth' => true
		],
		[
			'path' => '/^.+$/',
			'template' => 'page',
			'controllers' => [
				'PageController' => [
					'action' => 'show',
					'identifier' => '?1'
				]
			]
		]
	];
}
?>
