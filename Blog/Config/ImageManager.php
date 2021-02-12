<?php
namespace Blog\Config;

class ImageManager { # as ImageManagerConfig | IMConfig

	const SCALINGS = [
	#	NAME				   --------RATIO--------	JPEG
	#						   <=1:2   ~1:1	   >=2:1	quality
		'extrasmall'	=> [	200,	300,	400,	90		],
		'small'			=> [	400,	600,	800,	90		],
		'middle'		=> [	800,	1200,	1600,	90		],
		'large'			=> [	1600,	2400,	3200,	90		],
		'extralarge'	=> [	3200,	4800,	6400,	90		]
	];

	# relative to DOCUMENT_ROOT
	const BASEDIR = 'resources/images/dynamic'; # without slashes (/)
}
?>
