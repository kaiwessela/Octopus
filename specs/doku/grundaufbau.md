# Grundaufbau

## Kern
Der Octopus-Kern ist eine Einheit aus PHP-Skripten, die die eigentliche Logik von Octopus
beinhaltet. Er besteht im Wesentlichen aus einer Datenbankkommunikations- und Abstraktionsschicht
und einer Controller-Schicht, die sich um Empfang, Bearbeitung und Beantwortung der einzelnen
Anfragen (z.B. Seitenaufrufe) kümmert. Dazu kommen Konfigurationsdateien.

Die Architektur des Kerns orientiert sich am Model-View-Controller-Modell, wobei der Kern vor allem
die Bereiche Model und Controller abdeckt. Der View-Bereich, also die Darstellungsebene, muss vom
Seitenentwickler individuell geschrieben werden.

Hauptaufgabe des Kerns ist es, die angefragten Objekte aus der Datenbank zu laden und so
auszuliefern, dass sie einfach in die Templates eingebunden werden können.

## Endpunkte
Der Kern tut von sich selbst aus überhaupt nichts, sondern muss, sobald er eine Anfrage bearbeiten
soll, von einem anderen Skript aufgerufen werden. Ein Skript, das eine solche Aufgabe hat, nennen
wir Endpunkt. Es können mehrere Endpunkte an beliebigen Stellen in der Serverstruktur platziert
werden. Üblicherweise gibt es mindestens einen User-Endpoint, der aufgerufen wird, wenn ein
Endnutzer eine Seite aufruft; einen Admin-Endpoint, über den die Adminstrationsseiten zugänglich
sind und einen API-Endpunkt, über den eine REST-API erreichbar ist.

Der Endpoint selbst besteht aus einer PHP-Datei, die in dem entsprechenden Webserver-Ordner
abgelegt wird. Der Aufbau wird im folgenden Abschnitt behandelt. Eine einfache Website könnte
folgende Server-Struktur haben:

	/
		index.php				=> User-Endpoint
		admin/
			index.php			=> Admin-Endpoint
		api/
			v1/
				index.php		=> API-Endpoint

### Die index.php-Datei
Angenommen, unser User-Endpoint liegt im Hauptordner des Webservers. Ruft ein Seitenbesucher nun
die URL unserer Seite, etwa »https://example.org/« auf, führt der Webserver die index.php-Datei
im Hauptordner aus und sendet das Ergebnis zurück an den Besucher. Es wird also der User-Endpoint
aufgerufen. Analog verhält es sich mit den anderen Endpoints, wenn man den ensprechenden Pfad an
die URL anhängt.

Damit Octopus sich nun um die Bearbeitung der Anfrage kümmern kann, müssen eine Reihe von
Anweisungen abgearbeitet werden. Zuerst muss eine PHP-Session gestartet werden und Octopus
eingebunden werden. Das geschieht folgendermaßen:

	<?php
	session_start();
	require_once __DIR__ . '/Octopus/autoloader.php';

	$endpoint = new \Octopus\Endpoint();

Der Autoloader lädt sämtliche benötigten Octopus-Dateien automatisch. Der Pfad muss entsprechend
angepasst werden, sodass die autoloader.php-Datei, die sich im Octopus-Kernordner befindet,
geladen wird.  
Außerdem wird die Endpoint-Klasse von Octopus aufgerufen und das damit instanziierte Endpoint-Objekt
in der Variable $endpoint abgespeichert. Darüber wird der weitere Ablauf konfiguriert und gesteuert.
Nun ist Octopus geladen und in der Lage, die Anfrage zu bearbeiten.
(Link zur detaillierten Endpoint-Klassenbeschreibung)

### Die Routing-Tabelle
Nun besteht unser Endpoint selten aus einer einzigen, statischen Seite, sondern oftmals sogar aus
Gruppen von tausenden einzelnen, dynamischen Seiten. Wir betrachten erneut unseren User-Endpoint,
der beispielhaft folgende Anfrage-URLs bearbeiten soll:

	/					=> Startseite
	/blog				=> Liste mit allen Blogartikeln
	/blog/{Artikel-ID} 	=> Einzelner Blogartikel

Das wäre ein typischer Aufbau eines kleinen Blogs. All diese verschiedenen Seiten sollen aber nicht
statisch auf dem Server vorliegen, sondern CMS-artig von Octopus über den User-Endpoint bearbeitet
werden. Damit Octopus aber weiß, was genau bei einem Aufruf einer dieser URLs geladen und angezeigt
werden soll, muss eine Routing-Datei angelegt werden. Diese sähe für dieses Beispiel folgendermaßen
aus:

	[
		'/' => [ # Startseite
			'template' => 'start' # Es soll das Template für die Startseite geladen werden
		],
		'blog' => [ # Liste mit allen Blogeinträgen
			'template' => 'bloglist', # Es soll das Template für die Blog-Liste geladen werden
			'objects' => [ # Es sollen folgende Objekte aus der Datenbank geladen werden:
				'Post' => [ # Objekte der Klasse »Post« (= Blogeintrag)
					'action' => 'list', # Aktion: lade eine Liste davon
				]
			]
		],
		'blog/\*' => [ # Einzelner Blogartikel
			'template' => 'blogpost', # Es soll das Template für den Einzelartikel geladen werden
			'objects' => [ # Es sollen folgende Objekte aus der Datenbank geladen werden:
				'Post' => [ # Objekte der Klasse »Post«
					'action' => 'show', # Aktion: zeige das einzelne Objekt…
					'identifier' => '/2' # …mit der ID, die im 2. Pfadsegment steht
				]
			]
		]
	]

Diese Routing-Datei ist als PHP-Array aufgeschrieben und kann entweder direkt in die index.php
geschrieben oder – für größere Dateien – als separate PHP-Datei per include eingebunden werden.

Nun kann die Routing-Konfiguration dem Endpoint-Objekt übergeben werden. Dies geschieht
folgendermaßen:

	$endpoint->set_routes(require 'routes.php'); # für eine separate Routing-Datei





## Routing

TODO .htaccess FallbackResource
