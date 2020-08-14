<?php
namespace Blog\Config;

class PaginationConfig {
	const OBJECTS_PER_PAGE = 5;

	const STRUCTURE = [
		[
			'type' => 'absolute',
			'page' => 'first',
			'hide_on_void' => false,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.5,
			'template' => 'first',
			'title' => 'Erste Seite'
		],
		[
			'type' => 'relative',
			'page' => -10,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0,
			'template' => 'default',
			'title' => 'Zehn Seiten zurück'
		],
		[
			'type' => 'relative',
			'page' => -3,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'template' => 'default',
			'title' => 'Drei Seiten zurück'
		],
		[
			'type' => 'relative',
			'page' => -2,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'template' => 'default',
			'title' => 'Zwei Seiten zurück'
		],
		[
			'type' => 'relative',
			'page' => -1,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'template' => 'default',
			'title' => 'Eine Seite zurück'
		],
		[
			'type' => 'absolute',
			'page' => 'current',
			'hide_on_void' => false,
			'hide_on_duplicate' => false,
			'duplicate_priority' => 1,
			'template' => 'current',
			'title' => 'Aktuelle Seite'
		],
		[
			'type' => 'relative',
			'page' => 1,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'template' => 'default',
			'title' => 'Eine Seite vor'
		],
		[
			'type' => 'relative',
			'page' => 2,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'template' => 'default',
			'title' => 'Zwei Seiten vor'
		],
		[
			'type' => 'relative',
			'page' => 3,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'template' => 'default',
			'title' => 'Drei Seiten vor'
		],
		[
			'type' => 'relative',
			'page' => 10,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0,
			'template' => 'default',
			'title' => 'Zehn Seiten vor'
		],
		[
			'type' => 'absolute',
			'page' => 'last',
			'hide_on_void' => false,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.5,
			'template' => 'last',
			'title' => 'Letzte Seite'
		]
	];
}
?>
