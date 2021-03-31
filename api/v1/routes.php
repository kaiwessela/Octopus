<?php
return [
	'api/v1' => [
		'template' => '200',
		'methods' => ['GET', 'POST']
	],
	'api/v1/*' => [
		'template' => '200',
		'methods' => ['GET'],
		// TODO alias table
		'objects' => [
			'/3' => [
				'as' => 'Object',
				'action' => 'list',
				'amount' => '?amount|10',
				'page' => '?page|1'
			]
		]
	],
	'api/v1/*/new' => [
		'template' => '200',
		'methods' => ['POST'],
		'objects' => [
			'/3' => [
				'as' => 'Object',
				'action' => 'new'
			]
		],
		'require_auth' => true
	],
	'api/v1/*/count' => [
		'template' => '200',
		'methods' => ['GET'],
		'objects' => [
			'/3' => [
				'as' => 'Object',
				'action' => 'count'
			]
		]
	],
	'api/v1/*/*{8,60}' => [
		'template' => '200',
		'methods' => ['GET'],
		'objects' => [
			'/3' => [
				'as' => 'Object',
				'action' => 'show',
				'identifier' => '/4',

				'amount' => '?amount|10',
				'page' => '?page|1'
			]
		]
	],
	'api/v1/*/*{8,60}/count' => [
		'template' => '200',
		'methods' => ['GET'],
		'objects' => [
			'/3' => [
				'as' => 'Object',
				'action' => 'count',
				'identifier' => '/4'
			]
		]
	],
	'api/v1/*/*{8,60}/edit' => [
		'template' => '200',
		'methods' => ['POST'],
		'objects' => [
			'/3' => [
				'as' => 'Object',
				'action' => 'edit',
				'identifier' => '/4'
			]
		],
		'require_auth' => true
	],
	'api/v1/*/*{8,60}/delete' => [
		'template' => '200',
		'methods' => ['POST'], // TODO check if GET works too
		'objects' => [
			'/3' => [
				'as' => 'Object',
				'action' => 'delete',
				'identifier' => '/4'
			]
		],
		'require_auth' => true
	]
];
?>
