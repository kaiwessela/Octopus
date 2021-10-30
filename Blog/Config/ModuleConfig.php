<?php
namespace Octopus\Config\ModuleConfig; // TODO necessary?
// TODO use

return [

	Event::class => [
		'list' => EventList::class,
		'controller' => DataObjectController::class,
	],

	Motion::class => [
		'list' => MotionList::class,
		'controller' => DataObjectController::class,
	],

	Page::class => [
		'list' => PageList::class,
		'controller' => DataObjectController::class,
	],

	Person::class => [
		'list' => PersonList::class,
		'controller' => DataObjectController::class,
	],

	Group::class => [
		'list' => GroupList::class,
		'controller' => DataObjectController::class,
	],

	Post::class => [
		'list' => PostList::class,
		'controller' => DataObjectController::class,
	],

	Column::class => [
		'list' => ColumnList::class,
		'controller' => DataObjectController::class,
	],

	Application::class => [
		'list' => ApplicationList::class,
		'controller' => DataObjectController::class,
		'directory' => 'media/documents/{longid}.{extension}',
		'max_size' => 100000000,
		'allowed_mime_types' => [
			'application/zip',
			'application/gzip',
			'application/pdf',
		],
	],

	Audio::class => [
		'list' => AudioList::class,
		'controller' => DataObjectController::class,
		'directory' => 'media/audios/{longid}.{extension}',
		'max_size' => 100000000,
		'allowed_mime_types' => [
			'audio/mpeg',
			'audio/mp4',
			'audio/ogg',
			'audio/wav',
		],
	],

	Image::class => [
		'list' => ImageList::class,
		'controller' => DataObjectController::class,
		'directory' => 'media/images/{longid}/{longid}{_variant}.{extension}',
		'max_size' => 100000000,
		'allowed_mime_types' => [
			'image/gif',
			'image/jpeg',
			'image/png',
			'image/svg+xml',
			'image/tiff'
		],
		'sizes' => 	[
			'w200'	=> [ 'width' => 200, 'quality' => 90 ],
			'w300'	=> [ 'width' => 200, 'quality' => 90 ],
			'w400'	=> [ 'width' => 200, 'quality' => 90 ],
			'w600'	=> [ 'width' => 200, 'quality' => 90 ],
			'w800'	=> [ 'width' => 200, 'quality' => 90 ],
			'w1000' => [ 'width' => 200, 'quality' => 90 ],
			'w1600' => [ 'width' => 200, 'quality' => 90 ],
			'w2400' => [ 'width' => 200, 'quality' => 90 ],
		],
		'autoversion_rules' => [
			'image/gif' 	=> [ 'resize' => 'all', 'convert' = 'none' ],
			'image/jpeg' 	=> [ 'resize' => 'all', 'convert' = 'none' ],
			'image/png' 	=> [ 'resize' => 'all', 'convert' = 'none' ],
			'image/svg+xml' => [ 'resize' => 'all', 'convert' = 'none' ],
			'image/tiff' 	=> [ 'resize' => 'all', 'convert' = 'none' ],
		],
	],

	Video::class => [
		'list' => VideoList::class,
		'controller' => DataObjectController::class,
		'directory' => 'media/videos/{longid}.{extension}',
		'max_size' => 100000000,
		'allowed_mime_types' => [
			'video/mpeg',
			'video/mp4',
			'video/ogg',
			'video/webm',
		],
	],
];
