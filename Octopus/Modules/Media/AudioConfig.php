[
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
]
