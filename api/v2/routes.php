<?php
return [
	'/' => [
		'methods' => ['GET', 'POST']
	],
	'*' => [
		'methods' => ['GET'],
		// TODO alias table
		'objects' => [
			'/1' => [
				'action' => 'list',
				'amount' => '?amount|10',
				'page' => '?page|1'
			]
		]
	],
	'*/new' => [
		'methods' => ['POST'],
		'objects' => [
			'/1' => [
				'action' => 'new'
			]
		]
	],
	'*/count' => [
		'methods' => ['GET'],
		'objects' => [
			'/1' => [
				'action' => 'count'
			]
		]
	],
	'*/*{8,60}' => [
		'methods' => ['GET'],
		'objects' => [
			'/1' => [
				'action' => 'show',
				'identifier' => '/2',

				'amount' => '?amount|10',
				'page' => '?page|1'
			]
		]
	],
	'*/*{8,60}/edit' => [
		'methods' => ['POST'],
		'objects' => [
			'/1' => [
				'action' => 'edit',
				'identifier' => '/2'
			]
		]
	],
	'*/*{8,60}/delete' => [
		'methods' => ['POST'], // TODO check if GET works too
		'objects' => [
			'/1' => [
				'action' => 'delete',
				'identifier' => '/2'
			]
		]
	]
];
?>
