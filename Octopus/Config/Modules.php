<?php
return [
	'controllers' => [
		'BasicEntityController' => '\Octopus\Modules\BasicEntityController\BasicEntityController'
	],
	'entities' => [
		'Event' 		=> '\Octopus\Modules\Events\Event',
		'Application' 	=> '\Octopus\Modules\Media\Application',
		'Audio' 		=> '\Octopus\Modules\Media\Audio',
		'Image' 		=> '\Octopus\Modules\Media\Image',
		'Video' 		=> '\Octopus\Modules\Media\Video',
		'Motion' 		=> '\Octopus\Modules\Motions\Motion',
		'Page' 			=> '\Octopus\Modules\Pages\Page',
		'Person' 		=> '\Octopus\Modules\Persons\Person',
		'Group' 		=> '\Octopus\Modules\Persons\Groups\Group',
		'Post' 			=> '\Octopus\Modules\Posts\Post',
		'Column' 		=> '\Octopus\Modules\Posts\Columns\Column'
	],
	'default_entity_controllers' => [
		'@all' => 'BasicEntityController'
	]
];
?>
