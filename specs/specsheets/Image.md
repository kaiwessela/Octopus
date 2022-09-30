
## Array-Version
[
	'id' => string(8),
	'longid' => string(9-128),
	'type' => string(1-80): mime type,
	'extension' => string(1-10): filename extension,
	'title' => ?string(0-140),
	'description' => ?string(0-250),
	'copyright' => ?string(0-250),
	'alternative' => ?string(0-250),
	'variants' => [
		'original',
		other variant names
	],
	'computed' => ?[
		'sources' => [
			'original' => string: computed by src(),
			other sources computed by src(variant)
		],
		'src' => string: computed by src(),
		'srcset' => string: computed by srcset()
	]
]
