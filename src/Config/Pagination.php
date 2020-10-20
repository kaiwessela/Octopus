<?php
namespace Blog\Config;

class PaginationConfig {
	const STRUCTURES = [
		'default' => [
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
		],
		'admin' => [
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
				'number' => -5,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
				'title' => 'Fünf Seiten zurück'
			],
			[
				'type' => 'relative',
				'number' => -4,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
				'title' => 'Vier Seiten zurück'
			],
			[
				'type' => 'relative',
				'number' => -3,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
				'title' => 'Drei Seiten zurück'
			],
			[
				'type' => 'relative',
				'number' => -2,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
				'title' => 'Zwei Seiten zurück'
			],
			[
				'type' => 'relative',
				'number' => -1,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
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
				'duplicate_priority' => 0,
				'title' => 'Eine Seite vor'
			],
			[
				'type' => 'relative',
				'number' => 2,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
				'title' => 'Zwei Seiten vor'
			],
			[
				'type' => 'relative',
				'number' => 3,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
				'title' => 'Drei Seiten vor'
			],
			[
				'type' => 'relative',
				'number' => 4,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
				'title' => 'Vier Seiten vor'
			],
			[
				'type' => 'relative',
				'number' => 5,
				'hide_on_void' => true,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0,
				'title' => 'Fünf Seiten vor'
			],
			[
				'type' => 'absolute',
				'number' => 'last',
				'hide_on_void' => false,
				'hide_on_duplicate' => true,
				'duplicate_priority' => 0.5,
				'title' => 'Letzte Seite'
			]
		]
	];
}
?>
