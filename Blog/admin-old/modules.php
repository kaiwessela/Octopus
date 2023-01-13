<?php
return [
	'controllers' => [
		'BasicEntityController' => '\Octopus\Modules\BasicEntityController\BasicEntityController',
	],
	'entities' => [
		'events' 		=> '\Blog\Modules\Events\Event',
		'applications' 	=> '\Blog\Modules\Media\Application',
		'audios' 		=> '\Blog\Modules\Media\Audio',
		'images' 		=> '\Blog\Modules\Images\Image',
		'videos' 		=> '\Blog\Modules\Media\Video',
		'motions' 		=> '\Blog\Modules\Motions\Motion',
		'pages' 		=> '\Blog\Modules\Pages\Page',
		'persons' 		=> '\Blog\Modules\Persons\Person',
		'groups' 		=> '\Blog\Modules\Persons\Group',
		'posts' 		=> '\Blog\Modules\Posts\Post',
		'columns' 		=> '\Blog\Modules\Posts\Column'
	],
	'default_entity_controllers' => [
		'@all' => 'BasicEntityController'
	]
];
?>
