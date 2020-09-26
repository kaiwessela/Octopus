<?php
namespace Blog\Config;

class Routes {
	const ROUTES = [
		[
			'path' => '/^$/',
			'template' => 'index',
			'controllers' => [
				'Post' => [
					'action' => 'list',
					'amount' => 5
				],
				'Event' => [
					'action' => 'list',
					'amount' => 5
				]
			]
		],
		[
			'path' => '/^posts(\/[0-9]{0,8})?$/',
			'template' => 'posts',
			'controllers' => [
				'Post' => [
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
				'Post' => [
					'action' => 'show',
					'identifier' => '?2'
				]
			]
		],
		[
			'path' => '/^p\/.{8}$/',
			'template' => 'post',
			'controllers' => [
				'Post' => [
					'action' => 'show',
					'identifier' => '?2'
				]
			]
		],
		[
			'path' => '@else',
			'template' => 'static',
			'controllers' => [
				'Page' => [
					'name' => '?1'
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
					'page' => '?3'
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
		]
	];
}
?>
