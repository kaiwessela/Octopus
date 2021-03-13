<?php
namespace Blog\Config;

class Controllers {
	const REGISTERED = [
		'EventController',
		'ImageController',
		'ApplicationController',
		'PageController',
		'PersonController',
		'PostController',
		'ColumnController',
		'GroupController',
		'MotionController'
	];

	const ALIASES = [
		'event'		=> 'EventController',
		'events'	=> 'EventController',
		'image'		=> 'ImageController',
		'images'	=> 'ImageController',
		'application' => 'ApplicationController',
		'applications' => 'ApplicationController',
		'page'		=> 'PageController',
		'pages'		=> 'PageController',
		'person'	=> 'PersonController',
		'persons'	=> 'PersonController',
		'post'		=> 'PostController',
		'posts'		=> 'PostController',
		'column' 	=> 'ColumnController',
		'columns' 	=> 'ColumnController',
		'group' 	=> 'GroupController',
		'groups' 	=> 'GroupController',
		'motion' 	=> 'MotionController',
		'motions' 	=> 'MotionController'
	];
}
?>
