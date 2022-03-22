<?php
use \Octopus\Modules\Events\Event;
use \Octopus\Modules\Media\Application;
use \Octopus\Modules\Media\Audio;
use \Octopus\Modules\Media\Image;
use \Octopus\Modules\Media\Video;
use \Octopus\Modules\Motions\Motion;
use \Octopus\Modules\Pages\Page;
use \Octopus\Modules\Persons\Person;
use \Octopus\Modules\Persons\Group;
use \Octopus\Modules\Posts\Post;
use \Octopus\Modules\Posts\Column;

return [
	'default' => [
		'Event' 		=> Event::class,
		'Application' 	=> Application::class,
		'Audio' 		=> Audio::class,
		'Image' 		=> Image::class,
		'Video' 		=> Video::class,
		'Motion' 		=> Motion::class,
		'Page' 			=> Page::class,
		'Person' 		=> Person::class,
		'Group' 		=> Group::class,
		'Post' 			=> Post::class,
		'Column' 		=> Column::class
	],
	'default-url' => [
		'event' 		=> Event::class,
		'application' 	=> Application::class,
		'audio' 		=> Audio::class,
		'image' 		=> Image::class,
		'video' 		=> Video::class,
		'motion' 		=> Motion::class,
		'page' 			=> Page::class,
		'person' 		=> Person::class,
		'group' 		=> Group::class,
		'post' 			=> Post::class,
		'column' 		=>	Column::class
	]
];
?>
