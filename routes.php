<?php
return [
	'/' => [
		'template' => 'start',
		'objects' => [
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
	'posts/#?' => [
		'template' => 'posts',
		'objects' => [
			'Post' => [
				'action' => 'list',
				'amount' => 10,
				'page' => '/2',
				'options' => [
					'pagination' => 'posts/{page}'
				]
			]
		]
	],
	'posts/*{9,}' => [
		'template' => 'post',
		'objects' => [
			'Post' => [
				'action' => 'show',
				'identifier' => '/2'
			]
		]
	],
	'columns/*{9,}/#?' => [
		'template' => 'column',
		'objects' => [
			'Column' => [
				'action' => 'show',
				'identifier' => '/2',
				'amount' => 10,
				'page' => '/3',
				'options' => [
					'pagination' => 'columns/{/2}/{page}'
				]
			]
		]
	],
	'events/#?' => [
		'template' => 'events',
		'objects' => [
			'Event' => [
				'action' => 'list',
				'amount' => 10,
				'page' => '/2',
				'options' => ['future']
			]
		]
	],
	'p/*{8}' => [
		'template' => 'post',
		'objects' => [
			'Post' => [
				'action' => 'show',
				'identifier' => '/2'
			]
		]
	],
	'?' => [
		'template' => 'page',
		'objects' => [
			'Page' => [
				'action' => 'show',
				'identifier' => '/1'
			]
		]
	]
];
?>
