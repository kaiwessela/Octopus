<?php
namespace Blog\Config;

class PaginationConfig {
	const STRUCTURE = [
		[
			'type' => 'absolute',
			'number' => 'first',
			'hide_on_void' => false,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.5,
			'title' => 'Erste Seite'
		],
		[
			'type' => 'relative',
			'number' => -10,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0,
			'title' => 'Zehn Seiten zurück'
		],
		[
			'type' => 'relative',
			'number' => -3,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'title' => 'Drei Seiten zurück'
		],
		[
			'type' => 'relative',
			'number' => -2,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'title' => 'Zwei Seiten zurück'
		],
		[
			'type' => 'relative',
			'number' => -1,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'title' => 'Eine Seite zurück'
		],
		[
			'type' => 'absolute',
			'number' => 'current',
			'hide_on_void' => false,
			'hide_on_duplicate' => false,
			'duplicate_priority' => 1,
			'title' => 'Aktuelle Seite'
		],
		[
			'type' => 'relative',
			'number' => 1,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'title' => 'Eine Seite vor'
		],
		[
			'type' => 'relative',
			'number' => 2,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'title' => 'Zwei Seiten vor'
		],
		[
			'type' => 'relative',
			'number' => 3,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.75,
			'title' => 'Drei Seiten vor'
		],
		[
			'type' => 'relative',
			'number' => 10,
			'hide_on_void' => true,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0,
			'title' => 'Zehn Seiten vor'
		],
		[
			'type' => 'absolute',
			'number' => 'last',
			'hide_on_void' => false,
			'hide_on_duplicate' => true,
			'duplicate_priority' => 0.5,
			'title' => 'Letzte Seite'
		]
	];
}
?>
