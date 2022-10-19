<?php
return [
	'Server' => [
		'lang' => 'de_DE',
		'debug_mode' => true,
		'safemode' => false, // see ConfigLoader
		'base_urls' => [
			'blog.localhost',
			'blog.local',
			'octopus.localhost',
			'localhost/octopus',
		],
	],
	'Database' => [
		'host' => 'localhost',
		'name' => 'octopus',
		'user' => 'user',
		'pass' => 'password',
	],
];
?>
