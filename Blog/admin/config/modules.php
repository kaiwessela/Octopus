<?php
return [
	'controllers' => [
		'BasicEntityController' => '\Octopus\Modules\BasicEntityController\BasicEntityController',
		'AdminController' => '\Octopus\Modules\AdminController\AdminController',
	],
	'entities' => [
		'events' 		=> '\Blog\Modules\Events\Event',
		// 'applications' 	=> '\Octopus\Modules\Media\Application',
		// 'audios' 		=> '\Octopus\Modules\Media\Audio',
		'images' 		=> '\Blog\Modules\Images\Image',
		// 'videos' 		=> '\Octopus\Modules\Media\Video',
		// 'motions' 		=> '\Octopus\Modules\Motions\Motion',
		'pages' 		=> '\Blog\Modules\Pages\Page',
		'persons' 		=> '\Blog\Modules\Persons\Person',
		// 'groups' 		=> '\Octopus\Modules\Persons\Groups\Group',
		'posts' 		=> '\Blog\Modules\Posts\Post',
		// 'columns' 		=> '\Octopus\Modules\Posts\Columns\Column'
	],
	'default_entity_controllers' => [
		'@all' => 'BasicEntityController'
	]
];
?>
