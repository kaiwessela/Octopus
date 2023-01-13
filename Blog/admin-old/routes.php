<?php
return [
	// '@all' => [
	// 	'controllers' => [
	// 		'AC' => [
	// 			'class' => 'BasicEntityController',
	// 			'config' => '{ENDPOINT_DIR}/config/config.php',
	// 			'entity_class' => '/1'
	// 		]
	// 	]
	// ],
	'GET /' => [
		'template' => 'wrapper'
	],
	'GET /*' => [
		'template' => 'wrapper',
		'entities' => [
			'Object' => [
				'class' => '/1',
				'action' => 'list',
				'amount' => '?amount|20',
				'page' => '?page|1'
			]
		]
	],
	'GET|POST /*/new' => [
		'template' => 'wrapper',
		'entities' => [
			'Object' => [
				'class' => '/1',
				'action' => 'new'
			]
		]
	],
	'GET|POST /*/*/edit' => [
		'template' => 'wrapper',
		'entities' => [
			'Object' => [
				'class' => '/1',
				'action' => 'edit',
				'identifier' => '/2',
				'identify_by' => 'id'
				// TODO amount,page
			]
		]
	],
	'GET|POST /*/*/delete' => [
		'template' => 'wrapper',
		'entities' => [
			'Object' => [
				'class' => '/1',
				'action' => 'delete',
				'identifier' => '/2',
				'identify_by' => 'id'
			]
		]
	]
]
?>
