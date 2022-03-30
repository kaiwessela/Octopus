[
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
]
