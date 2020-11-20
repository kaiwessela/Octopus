<?php
namespace Blog\Config;

class Controllers {
	const REGISTERED = [
		'EventController',
		'ImageController',
		'PageController',
		'PersonController',
		'PostController',
		'ColumnController'
	];

	const ALIASES = [
		'event'		=> 'EventController',
		'events'	=> 'EventController',
		'image'		=> 'ImageController',
		'images'	=> 'ImageController',
		'page'		=> 'PageController',
		'pages'		=> 'PageController',
		'person'	=> 'PersonController',
		'persons'	=> 'PersonController',
		'post'		=> 'PostController',
		'posts'		=> 'PostController'
	];
}
?>
