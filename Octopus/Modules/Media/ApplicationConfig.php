[
	'list' => ApplicationList::class,
	'controller' => DataObjectController::class,
	'directory' => 'media/documents/{longid}.{extension}',
	'max_size' => 100000000,
	'allowed_mime_types' => [
		'application/zip',
		'application/gzip',
		'application/pdf',
	],
]
