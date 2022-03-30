<?php
return [
	/*'@general' => [
		'controllers' => [
			'AdminConfig' => [
				'class' => 'AdminConfigController'
			]
		]
	],*/
	'GET /' => [
		'template' => 'start'
	],
	'GET /*' => [
		'template' => 'list',
		'entities' => [
			'Entities' => [
				'class' => '/1',
				'action' => 'list',
				'amount' => '?amount|20',
				'page' => '?page|1'
			]
		]
	],
	'GET|POST /*/new' => [
		'template' => 'edit',
		'entities' => [
			'Entity' => [
				'class' => '/1',
				'action' => 'new'
			]
		]
	],
	'GET|POST /*/*/edit' => [
		'template' => 'edit',
		'entities' => [
			'Entity' => [
				'class' => '/1',
				'action' => 'edit',
				'identifier' => '/2',
				'identify_by' => 'id'
				// TODO amount,page
			]
		]
	],
	'GET|POST /*/*/delete' => [
		'template' => 'delete',
		'entities' => [
			'Entity' => [
				'class' => '/1',
				'action' => 'delete',
				'identifier' => '/2',
				'identify_by' => 'id'
			]
		]
	]
]
?>
