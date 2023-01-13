<?php
return [
	'GET /' => [
		'template' => 'start',
		'entities' => [
			'Posts' => [
				'class' => 'Post',
				'action' => 'list',
				'amount' => 5
			],
			// 'Events' => [
			// 	'class' => 'Event',
			// 	'action' => 'list',
			// 	'amount' => 5
			// ]
		]
	],
	'GET /posts' => [
		'template' => 'posts',
		'entities' => [
			'Posts' => [
				'class' => 'Post',
				'action' => 'list',
				'amount' => 10,
				'page' => '?page|1'
			]
		]
	],
	'GET /posts/*' => [
		'template' => 'post',
		'entities' => [
			'Post' => [
				'class' => 'Post',
				'action' => 'show',
				'identifier' => '/2',
				'identify_by' => 'longid'
			]
		]
	],
	'GET /columns/*/#?' => [
		'template' => 'column',
		'entities' => [
			'Column' => [
				'class' => 'Column',
				'action' => 'show',
				'identifier' => '/2',
				'identify_by' => 'longid',
				'amount' => 10,
				'page' => '/3'
			]
		]
	],
	'GET /events/#?' => [
		'template' => 'events',
		'entities' => [
			'Events' => [
				'class' => 'Event',
				'action' => 'list',
				'amount' => 10,
				'page' => '/2'
			]
		]
	],
	'GET /p/*' => [
		'template' => 'post',
		'entities' => [
			'Post' => [
				'class' => 'Post',
				'action' => 'show',
				'identifier' => '/2'
			]
		]
	],
	'GET /**' => [
		'template' => 'page',
		'entities' => [
			'Page' => [
				'class' => 'Page',
				'action' => 'show',
				'identifier' => '/1+',
				'identify_by' => 'longid'
			]
		]
	]
];
?>
