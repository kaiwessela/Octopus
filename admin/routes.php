<?php
return [
	'admin' => [],
	'admin/*/#?' => [
		'objects' => [
			'/2' => [
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
		'objects' => [
			'/2' => [
				'action' => 'new'
			]
		],
		'require_auth' => true
	],
	'admin/*/*{8}' => [
		'objects' => [
			'/2' => [
				'action' => 'show',
				'identifier' => '/3'
			]
		],
		'require_auth' => true
	],
	'admin/*/*{8}/edit' => [
		'objects' => [
			'/2' => [
				'action' => 'edit',
				'identifier' => '/3'
			]
		],
		'require_auth' => true
	],
	'admin/*/*{8}/delete' => [
		'objects' => [
			'/2' => [
				'action' => 'delete',
				'identifier' => '/2'
			]
		],
		'require_auth' => true
	]
];
?>
