<?php
return [
	'controllers' => [
		'BasicEntityController' => '\Octopus\Modules\BasicEntityController\BasicEntityController',
		'AdminController' => '\Octopus\Modules\AdminController\AdminController',
	],
	'entities' => [
		'events' 		=> '\Octopus\Modules\Events\Event',
		// 'applications' 	=> '\Octopus\Modules\Media\Application',
		// 'audios' 		=> '\Octopus\Modules\Media\Audio',
		'images' 		=> '\Octopus\Modules\Images\Image',
		// 'videos' 		=> '\Octopus\Modules\Media\Video',
		// 'motions' 		=> '\Octopus\Modules\Motions\Motion',
		'pages' 		=> '\Octopus\Modules\Pages\Page',
		'persons' 		=> '\Octopus\Modules\Persons\Person',
		// 'groups' 		=> '\Octopus\Modules\Persons\Groups\Group',
		'posts' 		=> '\Octopus\Modules\Posts\Post',
		// 'columns' 		=> '\Octopus\Modules\Posts\Columns\Column'
	],
	'default_entity_controllers' => [
		'@all' => 'BasicEntityController'
	]
];
?>
