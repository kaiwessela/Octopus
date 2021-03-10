<?php
namespace Blog\Config;

class MediaConfig {

	const APPLICATION_TYPES = [
		'application/zip',
		'application/gzip',
		'application/pdf'
	];

	const AUDIO_TYPES = [
		'audio/mpeg',
		'audio/mp4',
		'audio/ogg',
		'audio/wav'
	];

	const IMAGE_TYPES = [
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/svg+xml',
		'image/tiff'
	];

	const VIDEO_TYPES = [
		'video/mpeg',
		'video/mp4',
		'video/ogg',
		'video/webm'
	];

	const RESIZABLE_IMAGE_TYPES = [
		'image/jpeg',
		'image/png',
		'image/tiff'
	];

	const IMAGE_RESIZE_WIDTHS = [
	#	NAME			WIDTH	JPEG QUALITY
		'w200'	=> [	200,	90				],
		'w300'	=> [	300,	90				],
		'w400'	=> [	400,	90				],
		'w600'	=> [	600,	90				],
		'w800'	=> [	800,	90				],
		'w1000'	=> [	1000,	90				],
		'w1600' => [	1600,	90				],
		'w2400' => [	2400,	90				]
	];

	const DIRECTORIES = [
	#						BASEDIR						FILENAME
		'image' 		=> ['resources/images/dynamic',	'$LONGID/$LONGID&VARIANT.$EXTENSION'	],
		'audio' 		=> ['resources/audio/dynamic',	'$LONGID.$EXTENSION'					],
		'video' 		=> ['resources/video/dynamic',	'$LONGID.$EXTENSION'					],
		'application' 	=> ['resources/files/dynamic',	'$LONGID.$EXTENSION'					]
		# possible values: $ID, $LONGID, $ID&VARIANT, $LONGID&VARIANT, $EXTENSION
		# required (not checked): (id&variant | longid&variant), extension
	];

	const MAX_UPLOAD_SIZE = 10000000;
}
?>
