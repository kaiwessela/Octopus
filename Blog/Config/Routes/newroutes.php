<?php
$routes = [
	'/' => [
		'template' => 'index',
		'objects' => [
			'PostList' => [
				'amount' => 5
			],
			'EventList' => [
				'amount' => 5
			]
		]
	],
	'posts/#?' => [
		'template' => 'posts',
		'objects' => [
			'PostList' => [
				'amount' => 5,
				'page' => '?2'
			]
		]
	],
	'posts/*{9,}' => [
		'template' => 'post',
		'objects' => [
			'Post' => [
				'identifier' => '?2'
			]
		]
	],
	'columns/*{9,}/#?' => [
		'template' => 'column',
		'objects' => [
			'Column' => [
				'identifier' => '?2',
				'amount' => 1,
				'page' => '?3'
			]
		]
	],
	'events/#?' => [
		'template' => 'events',
		'objects' => [
			'EventList' => [
				'amount' => 10,
				'page' => '?2',
				'options' => ['future']
			]
		]
	],
	'p/*{8}' => [
		'template' => 'post',
		'objects' => [
			'Post' => [
				'identifier' => '?2'
			]
		]
	],
	'*' => [
		'template' => 'page',
		'objects' => [
			'Page' => [
				'identifier' => '?1'
			]
		]
	]
];
?>
