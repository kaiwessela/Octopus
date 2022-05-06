<?php
return [
	'pages' => [
		'entity-class' => '\Octopus\Modules\Pages\Page',
		'live-view-url' => '/{longid}',
		'lang' => [
			'plural' => 'Seiten',
			'list.title' => 'Alle Seiten',
			'list.add-new' => 'Neue Seite erstellen',
			'list.is-empty' => 'Keine Seiten vorhanden.',
		]
	],
	'posts' => [
		'entity-class' => '\Octopus\Modules\Posts\Post',
		'live-view-url' => '/posts/{longid}',
		'lang' => [
			'plural' => 'Artikel',
			'list.title' => 'Alle Artikel',
			'list.add-new' => 'Neuen Artikel schreiben',
			'list.is-empty' => 'Keine Artikel vorhanden.',
		]
	],
	'persons' => [
		'entity-class' => '\Octopus\Modules\Persons\Person',
		'live-view-url' => null,
		'lang' => [
			'plural' => 'Personen',
			'list.title' => 'Alle Personen',
			'list.add-new' => 'Neue Person hinzufügen',
			'list.is-empty' => 'Keine Personen vorhanden.',
		]
	],
	'images' => [
		'entity-class' => '\Octopus\Modules\Media\Image',
		'live-view-url' => null,
		'lang' => [
			'plural' => 'Bilder',
			'list.title' => 'Alle Bilder',
			'list.add-new' => 'Neues Bild hochladen',
			'list.is-empty' => 'Keine Bilder vorhanden.',
		]
	],
	'events' => [
		'entity-class' => '\Octopus\Modules\Events\Event',
		'live-view-url' => null,
		'lang' => [
			'plural' => 'Veranstaltungen',
			'list.title' => 'Alle Veranstaltungen',
			'list.add-new' => 'Neue Veranstaltung hinzufügen',
			'list.is-empty' => 'Keine Veranstaltungen vorhanden.',
		]
	],
];
?>
