<?php
return [
	'@include' => [
		\Octopus\Modules\Events\Event::class 		=> 'Modules/Events/EventConfig.php',
		\Octopus\Modules\Media\Application::class 	=> 'Modules/Media/ApplicationConfig.php',
		\Octopus\Modules\Media\Audio::class 		=> 'Modules/Media/AudioConfig.php',
		\Octopus\Modules\Media\Image::class 		=> 'Modules/Media/ImageConfig.php',
		\Octopus\Modules\Media\Video::class 		=> 'Modules/Media/VideoConfig.php',
		\Octopus\Modules\Motions\Motion::class 		=> 'Modules/Motions/MotionConfig.php',
		\Octopus\Modules\Pages\Page::class 			=> 'Modules/Pages/PageConfig.php',
		\Octopus\Modules\Persons\Person::class 		=> 'Modules/Persons/PersonConfig.php',
		\Octopus\Modules\Persons\Group::class 		=> 'Modules/Persons/GroupConfig.php',
		\Octopus\Modules\Posts\Post::class 			=> 'Modules/Posts/PostConfig.php',
		\Octopus\Modules\Posts\Column::class 		=> 'Modules/Posts/ColumnConfig.php',
	]
];
?>
