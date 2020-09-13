<?php
namespace Blog\Config;

class Routes {
	const ROUTES = [
		[
			'path' => '/^\/$/',
			'template' => 'index',
			'controllers' => [
				'Post' => [
					'mode' => 'multi',
					'amount' => 5
				],
				'Event' => [
					'mode' => 'multi',
					'amount' => 5
				]
			]
		],
		[
			'path' => '/^posts\/[0-9]{0,8}$/',
			'template' => 'posts',
			'controllers' => [
				'Post' => [
					'mode' => 'multi',
					'amount' => 5,
					'page' => '?2'
				]
			]
		],
		[
			'path' => '/^posts\/.{9,}$/',
			'template' => 'post',
			'controllers' => [
				'Post' => [
					'mode' => 'single',
					'identifier' => '?2'
				]
			]
		],
		[
			'path' => '/^p\/.{8}$/',
			'template' => 'post',
			'controllers' => [
				'Post' => [
					'mode' => 'single',
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
		# TODO add 404
	];
}
?>
