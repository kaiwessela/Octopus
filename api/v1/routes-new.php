<?php
return [
	'GET /' => [ # welcome
		'template' => 'status'
	],
	'GET /*' => [ # entity list
		'entities' => [
			'Entities' => [
				'class' => '/1',
				'action' => 'list',
				'amount' => '?amount|10',
				'page' => '?page|1'
			]
		]
	],
	'POST /*' => [ # new entity
		'entities' => [
			'Entity' => [
				'class' => '/1',
				'action' => 'new'
			]
		]
	],
	'GET /*/*' => [ # show entity
		'entities' => [
			'Entity' => [
				'class' => '/1',
				'action' => 'show',
				'identifier' => '/2',
				'identify_by' => '?by|id',
				'amount' => '?amount|10', // TODO allow amount=all
				'page' => '?page|1'
			]
		]
	],
	'PUT /*/*' => [ # edit entity
		'entities' => [
			'Entity' => [
				'class' => '/1',
				'action' => 'edit',
				'identifier' => '/2',
				'identify_by' => '?by|id'
			]
		]
	],
	'DELETE /*/*' => [ # delete entity
		'entities' => [
			'Entity' => [
				'class' => '/1',
				'action' => 'delete',
				'identifier' => '/2',
				'identify_by' => '?by|id'
			]
		]
	]
]
?>
