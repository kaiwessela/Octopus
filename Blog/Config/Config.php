<?php
return [
	'@include' => [
		'Modules' => __DIR__ . '/ModuleConfig.php',
		'Octopus' => __DIR__ . '/../Core/Version.php',
	],
	'Server' => [
		'lang' => 'de_DE',
		'debug_mode' => false,
		'base_urls' => [
			'blog.localhost',
			'blog.local',
			'localhost/octopus',
		],
	],
	'Database' => [
		'host' => 'localhost',
		'name' => 'blog',
		'user' => 'user',
		'pass' => 'password',
	],
];
?>
