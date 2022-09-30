<?php
namespace Blog\Modules\Images;

class ImageConfig {

	const DIRECTORY = '{DOCUMENT_ROOT}/media/images/{longid}/{longid}{_variant}.{extension}';

	const MAX_FILESIZE = 100000000;

	const ALLOWED_MIME_TYPES = [
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/svg+xml',
		'image/tiff'
	];

	const SIZES = [
		'w200'	=> [ 'width' => 200, 'quality' => 90 ],
		'w300'	=> [ 'width' => 200, 'quality' => 90 ],
		'w400'	=> [ 'width' => 200, 'quality' => 90 ],
		'w600'	=> [ 'width' => 200, 'quality' => 90 ],
		'w800'	=> [ 'width' => 200, 'quality' => 90 ],
		'w1000' => [ 'width' => 200, 'quality' => 90 ],
		'w1600' => [ 'width' => 200, 'quality' => 90 ],
		'w2400' => [ 'width' => 200, 'quality' => 90 ],
	];

	const AUTOVERSION_RULES = [
		'image/gif' 	=> [ 'resize' => 'all', 'convert' => 'none' ],
		'image/jpeg' 	=> [ 'resize' => 'all', 'convert' => 'none' ],
		'image/png' 	=> [ 'resize' => 'all', 'convert' => 'none' ],
		'image/svg+xml' => [ 'resize' => 'all', 'convert' => 'none' ],
		'image/tiff' 	=> [ 'resize' => 'all', 'convert' => 'none' ],
	];

}
?>
