<?php
return [
	'admin' => [
		'template' => 'wrapper',
	],
	'admin/*/#?' => [
		'template' => 'wrapper',
		'objects' => [
			'/2' => [
				'as' => 'Object',
				'action' => 'list',
				'amount' => 20,
				'page' => '/3',
				'options' => [
					'pagination' => 'admin/{/2}/{page}'
				]
			]
		],
		'require_auth' => true
	],
	'admin/*/new' => [
		'template' => 'wrapper',
		'objects' => [
			'/2' => [
				'as' => 'Object',
				'action' => 'new'
			]
		],
		'require_auth' => true
	],
	'admin/*/*{8}' => [
		'template' => 'wrapper',
		'objects' => [
			'/2' => [
				'as' => 'Object',
				'action' => 'show',
				'identifier' => '/3'
			]
		],
		'require_auth' => true
	],
	'admin/*/*{8}/edit' => [
		'template' => 'wrapper',
		'objects' => [
			'/2' => [
				'as' => 'Object',
				'action' => 'edit',
				'identifier' => '/3'
			]
		],
		'require_auth' => true
	],
	'admin/*/*{8}/delete' => [
		'template' => 'wrapper',
		'objects' => [
			'/2' => [
				'as' => 'Object',
				'action' => 'delete',
				'identifier' => '/3'
			]
		],
		'require_auth' => true
	]
];
?>
