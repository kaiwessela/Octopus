# Blog
Mein persönliches Blog-System

## Einführung
### Für wen ist Blog gemacht?
Blog ist ein kleines, leichtgewichtiges Content-Management-System, das für Menschen geeignet ist,
die bereits Erfahrung mit HTML und CSS haben und die Freiheit und Möglichkeiten dieser Sprachen
bei der Gestaltung ihrer Seite voll ausnutzen möchten, andererseits aber nicht auf die Verwaltung
ihrer Inhalte durch ein ordentliches CMS verzichten können.

Die meisten bekannten Content-Management-Systeme sind mittlerweile völlig überladen. Wer eine
Website mit Wordpress erstellen möchte, hat die Wahl zwischen vorhandenen Themes, die leider fast
immer gleich aussehen – wer Ahnung hat, erkennt eine Wordpress-Seite oft schon auf den ersten
Blick – und oftmals auch qualitativ zu Wünschen übrig lassen, oder man erstellt sein eigenes Theme
und arbeitet sich in eine komplizierte und undurchsichtige Templating-Sprache ein – die aber am
Ende auch nur zu HTML geparst wird. Warum nicht direkt HTML und CSS benutzen?

Aus diesem Wunsch heraus entstand Blog. Mein Ziel war und ist es, ein System zu entwickeln, mit dem
man mit geringer Einarbeitungszeit und bekannten Werkzeugen eine selbstgeschriebene Website mit
einer funktionalen, einfach zu bedienenden Inhaltsverwaltung kombinieren kann.

### Prinzipien
#### Trennung von Logik und Darstellung
Blog ist nach dem bekannten und bewährten
[Model-View-Controller](https://de.wikipedia.org/Model_View_Controller)-Modell aufgebaut. Dieses
Modell teilt ein Programm in drei Schichten ein: Die Datenbankschicht *(Model)*, die sich um die
Kommunikation mit der Datenbank kümmert, die Logikschicht *(Controller)*, in der die eigentlichen
Berechnungen ausgeführt werden, und die Darstellungsschicht *(View)*, die sich ausschließlich um
die Darstellung kümmert.

Die Darstellungsschicht besteht in Blog aus den Templates. Die Trennung von Logik und Darstellung
bedeutet, dass in den Templates nichts mehr programmiert wird, sondern nur die im Controller
berechneten Inhalte und Variablen eingefügt werden. Dadurch bleiben die Templates aufgeräumt und
einfach zu verstehen.

#### Benutzung bekannter Techniken
Blog zwingt den Benutzer nicht, irgendeine unnötige neue Templating-Sprache zu erlernen oder sich
in komplizierte Texteditoren einzuarbeiten, sondern setzt lediglich Kenntnisse in HTML, CSS,
Markdown und ein Minimum an PHP voraus (letzteres ist im Zweifelsfall in einer halben Stunde
erlernbar).

Neue Blogeinträge oder ähnliches schreibt man ganz einfach in Markdown oder wahlweise in HTML und
muss sich nicht auf einen komplizierten WYSIWYG-Editor einstellen, bei dem man oft genug eben nicht
das bekommt, was man sieht (oder zumindest nicht was man möchte).

Die Templates verwenden, ganz klassisch, HTML mit Inline-PHP. Durch die Trennung von Logik und
Darstellung muss man nicht mehr PHP beherrschen als einfache, einteilige `if`-Abfragen,
`foreach`-Schleifen, `include`-Anweisungen (für Untertemplates) und die Inline-Ausgabe-Syntax
`<?= $variable ?>` zum Ausgeben des Variablenwertes. Das ist wirklich schon alles.

## Grundaufbau
### Objekte und Klassen
Datensätze, z.B. Blogeinträge, Seiten und Termine, werden als Objekte gespeichert. Es gibt
verschiedene Klassen von Objekten, beispielsweise die Klasse *»Post«* für Blogeinträge, *»Page«* für
statische Seiten und *»Event«* für Termine.

Ein Objekt hat immer mehrere Eigenschaften. Manche Eigenschaften, z.B. die ID, besitzen alle
Objekte, egal welcher Klasse, andere, z.B. die Eigenschaft `title`, sind klassenspezifisch, werden
also nur von Objekten einer bestimmten Klasse, in diesem Fall *»Post«*, verwendet.

#### Allgemeine Eigenschaften
Die Eigenschaften `id` und `longid` sind allgemein, das heißt, dass sie von Objekten aller Klassen
verwendet werden. Im Folgenden erkläre ich diese beiden wichtigen Eigenschaften genauer.

##### id
Bei der Erstellung eines Objekts, also zum Beispiel beim Anlegen einer neuen Seite oder beim
Schreiben eines neuen Blogeintrags, wird eine eindeutige `id` automatisch generiert und dem neuen
Objekt zugewiesen. Sie ist zufällig, nicht änderbar und besteht aus 8 hexadezimalen Zeichen.

Die `id` lässt sich sehr gut für Shortlinks verwenden, beispielsweise könnte man über
https://example.org/a/9ac4fb1e zum Eintrag *Mein erster Artikel* gelangen.

##### longid
Die `longid` hat ebenso wie die `id` die Funktion, ein Objekt eindeutig zu identifizieren. Allerdings
kann der Ersteller des Objekts, also der Autor eines Blogeintrags etwa, die `longid` selbst festlegen.
Zweck der `longid` ist, ein eindeutiger, aber gut lesbarer Identifikator eines Objekts zu sein.
Deshalb wird sie beispielsweise in der URL-Leiste zum Aufrufen eines Objekts verwendet. Die URL
https://example.org/artikel/mein-erster-artikel lässt den Inhalt eben viel besser erahnen als
https://example.org/artikel/9ac4fb1e.

Die `longid` muss, um eindeutig von einer `id` unterscheidbar zu sein, mindestens 9 Zeichen lang sein
(maximal 60) und darf nur aus lateinischen Buchstaben *(a-z/A-Z)*, den Ziffern *0-9* und Bindestrichen
*(-)* bestehen. Sie kann, nachdem sie einmal gesetzt wurde, nicht mehr geändert werden. Dies würde dem
Webstandard zuwiderlaufen, dass eine Ressource immer über die gleiche Adresse erreichbar sein
sollte.

## Routing
Blog erlaubt es dem Anwender, frei festzulegen, wie die URLs seiner Seite strukturiert sein sollen.
Aus diesem Grund gibt es die Routing-Konfiguration *»routes.json«*. Sie sorgt dafür, dass ein
bestimmter Pfad (z.B. */artikel/mein-erster-artikel*) der richtigen Seite zugeordnet wird.

Außerdem kann der Anwender entscheiden, wie die Seite aufgebaut sein soll, beispielsweise welches
Template und welche Controller geladen werden sollen.

### Pfadnotation
Eine Route beginnt immer mit der Notation des zugehörigen Pfades. So soll z.B. beim Aufruf von
`artikel` eine Liste von Blogeinträgen angezeigt werden, bei `artikel/mein-erster-artikel` jedoch
der einzelne Artikel mit der longid mein-erster-artikel.

#### Statische Form
Im einfachsten Fall schreibt man also:

	{
		"artikel": {
			…
		},
		"artikel/mein-erster-artikel": {
			…
		},
		…
	}

Damit hat man den beiden Routen gültige Pfade zugewiesen. Nun wäre es aber sehr aufwändig und
unpraktisch, jeden neuen Artikel einzeln in die Routing-Tabelle einzutragen. Deshalb gibt es
verschiedene Arten, Platzhalter in die Pfadnotation einzubauen.

#### Wildcards
Es gibt folgende Wildcards, die alleinstehend gültige Pfadnotationen sind: `/` und `*`.

`/` steht dabei für einen leeren Pfad, wird also in dem Fall aufgerufen, wenn der Benutzer
https://example.org/ aufruft. Typischerweise wird dann die Startseite angezeigt.

`*` steht für alle Pfade, die es irgendwie geben könnte. Es ist also eine *Catch-All*-Notation, die
dann aufgerufen wird, wenn die Pfadnotationen aller vorherigen Routen nicht zutreffend waren.
Deshalb sollte die Route mit dieser Wildcard ganz am Ende der Routing-Datei stehen, weil eventuell
nachfolgende Routen sonst gar nicht aufgerufen werden könnten.

#### PathPatterns
Ein PathPattern ist eine Pfadnotation, die einfache Platzhalter ermöglicht. So würde man unser
obiges Beispiel `artikel/mein-erster-artikel` eher mit dem PathPattern `artikel/*` beschreiben.
Dieses PathPattern trifft auf alle Artikel zu, also auch beispielsweise auf
`artikel/urlaubsgruesse`, `artikel/meine-lebensgeschichte` und `artikel/zum-geburtstag`.
Im Folgenden beschreibe ich diese Notation genauer.

*Zum Überprüfen:* Ein gültiges PathPattern wird durch diesen regulären Ausdruck definiert:
`^([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?))*([\*#]*\??)?$`

##### Segmente
Ein Pfad besteht aus mehreren Segmenten. In unserem Beispiel `artikel/urlaubsgruesse` sind `artikel`
und `urlaubsgruesse` jeweils ein Segment. Segmente werden durch Schrägstriche (`/`) verbunden. Am
Anfang und am Ende stehen übrigens keine Schrägstriche.

##### Platzhalter
Im PathPattern können Segmente nun durch Platzhalterzeichen ersetzt werden:

- `*` steht für jedes beliebige Zeichen innerhalb eines Segments.
- `#` steht für eine Zahl beliebiger Größe.

__Vorsicht:__ Es darf immer nur genau ein Platzhalter für genau ein Segment stehen, niemals mehrere
Platzhalter im gleichen Segment, Platzhalter gemeinsam mit anderen Zeichen im gleichen Segment oder
ein Platzhalter für mehrere Segmente.

So würde das PathPattern `artikel/*` also, wie schon oben gezeigt, auf alle beliebigen Pfade
`artikel/[artikel-longid]` zutreffen, während das PathPattern `artikel/#` nur auf die Pfade
`artikel/1`, `artikel/2`, `artikel/3`, … zutrifft. Letzteres nutzt man beispielsweise, wenn
die Artikelliste grundsätzlich über die URL https://example.org/artikel zu erreichen ist,
es jedoch so viele Artikel gibt, dass man sie auf mehrere Seiten aufteilen muss und die zweite,
dritte, vierte usw. Seite der Artikelliste einfach über eine angehängte Zahl (z.B.
https://example.org/artikel/2) zu erreichen sein soll.

##### Quantifikatoren
Nun haben wir im vorherigen Beispiel aber ein Problem: Wenn wir das PathPattern `artikel/#`
für die Artikelliste verwenden, landen Aufrufe von https://example.org/artikel (ohne Nummer) im
Leeren. Wir müssten nun zwei Routen mit den Pfadnotationen `artikel` und `artikel/#` einrichten,
obwohl sie eigentlich das gleiche Ziel haben (oder fast das gleiche, aber dazu kommen wir später).

Um dieses Problem zu lösen, gibt es Quantifikatoren. Sie stehen hinter den Platzhaltern und
bestimmen, für welche Anzahl von Buchstaben oder Zahlen oder Zeichen der Platzhalter stehen soll.

Folgende Quantifikatoren gibt es:

- `{n}`, also `{1}`, `{2}` usw. bedeutet, dass der davorstehende Platzhalter nur für *n* Zeichen
stehen darf, `artikel/#{1}` trifft also auf __artikel/1__, __artikel/2__ oder __artikel/3__ zu,
nicht aber auf __artikel/11__ oder __artikel/123__.
- `{n,m}`, also `{1,2}`, `{2,4}` usw. bedeutet, dass der davorstehende Platzhalter nur für *n bis m*
Zeichen stehen darf. `artikel/#{2,4}` trifft also auf __artikel/12__, __artikel/567__ oder
__artikel/9999__ zu, nicht aber auf __artikel/2__ oder __artikel/12345__.
- `{n,}`: *m*, also der zweite Parameter, kann auch weggelassen werden. Dann steht dieser Quantifikator
für *mindestens n* Zeichen. Die Umkehrung, die Zahl vor dem Komma (*n*) wegzulassen, funktioniert
allerdings nicht.
- `?` ist ein Spezialfall. Dieser Quantifikator darf nur am Ende des letzten Segmentes stehen. Er
macht den Platzhalter des letzten Segmentes optional. `artikel/#?` würde also sowohl auf die URL
https://example.org/artikel als auch auf https://example.org/artikel/2 zutreffen.

__Vorsicht:__ Quantifikatoren dürfen nur hinter Platzhaltern stehen, niemals davor, alleine oder
hinter sonstigen Zeichen.

#### Reguläre Ausdrücke (RegEx)
Reguläre Ausdrücke sind die komplizierteste, aber auch mächtigste Form für die Pfadnotation.
Anfänger werden sie wahrscheinlich noch nicht benötigen, geübten Nutzern ermöglichen sie aber
fast absolute Freiheit in der Gestaltung ihrer Pfadkonfiguration.
Sie beginnen mit `/^`, was den Anfang des Pfades kennzeichnet, und enden mit `$/` für das Ende.
Dazwischen können alle bekannten RegEx-Suchmuster eingesetzt werden.

Zur Prüfung wird ausschließlich der Pfadabschnitt der URL herangezogen, also nicht der Host, nicht
der Query-String (`?xy=z`) und auch nicht das Fragment (`#abc`). Außerdem werden eventuell
vorhandene Schrägstriche an Anfang und Ende abgeschnitten. Der RegEx prüft aus der URL
`https://example.org/artikel/mein-erster-artikel/?queryString=true#kapitel-2` also nur den
Abschnitt `artikel/mein-erster-artikel`. Bei der Pfadnotation mittels RegEx sollte dies bedacht
werden. 

__Wichtig:__ Die Schrägstriche (`/`), die die Pfadsegmente trennen, müssen doppelt mit Backslashes (`\`)
escapt werden, einmal weil sie durch den JSON-Parser laufen, zum Zweiten, weil sie sonst als
RegEx-Endzeichen missinterpretiert würden.

Blog verwendet intern nur RegEx, schreibt also statische Notationen, Wildcards und
PathPatterns in reguläre Ausdrücke um. Daher dürften Teile der PathPatterns den RegEx-Experten
auch schon bekannt vorkommen, sie wurden einfach davon übernommen.
Beispiele für die interne Umschreibung sind:

- `artikel/mein-erster-artikel` wird zu `/^artikel\/mein-erster-artikel$/`
- `artikel/*` wird zu `/^artikel\/[^\/]+$/`
- `artikel/*{0,8}` wird zu `/^artikel\/[^\/]{0,8}$/`
- `artikel/#?` wird zu `/^artikel(\/[0-9]+)?$/`
- `/` (Wildcard) wird zu `/^$/`
- `*` (Wildcard) wird zu `/^.*$/`

### Ersetzungszeichen
In unserem Beispiel trifft die Route mit der Pfadnotation `artikel/#?` sowohl auf die URL
https://example.org/artikel als auch auf die URL https://example.org/artikel/2 zu. Das ist auch
so gewollt. Allerdings möchten wir, dass beim Aufruf der zweiten URL eine andere Artikelliste
angezeigt wird als in der ersten URL, schließlich führt die erste auf die erste Seite und die
zweite auf die zweite Seite.

Wir haben bereits gelernt, dass wir `page`-Attribut dem Controller mitteilen können, welche Seite
einer Liste wir erhalten möchten. Allerdings können wir das bisher nur statisch, wir können also
nur die Werte `1`, `2`, `3`, … , `n` eintragen. In diesem Fall muss das `page`-Attribut aber
dynamisch bestimmt werden, denn die gleiche Route trifft auf verschiedene Seiten zu.

Wenn wir uns noch einmal den Pfad anschauen, sehen wir ja, dass im zweiten Segment eigentlich schon
steht, welche Seite wir aufrufen möchten. Wir müssen dem Router nur mitteilen, dass er dieses
URL-Segment in das `page`-Attribut einsetzen soll. Dafür gibt es Ersetzungszeichen.

Ein Ersetzungszeichen entspricht dem Muster `?n`, ist also ein Fragezeichen gefolgt von einer Zahl.
Die Zahl gibt an, auf welches Pfadsegment sich das Ersetzungszeichen bezieht. Im Falle von unserer
Beispiel-Pfadnotation `artikel/#?` schreiben wir also `"page": "?2"`, weil im zweiten Pfadsegment
die Information steht, die wir in das `page`-Attribut einsetzen möchten. Der Router erkennt nun
dieses Ersetzungszeichen und setzt den Inhalt des Pfadsegmentes in das Attribut ein.

Das Ersetzungszeichen kann nicht überall verwendet werden, sondern bisher nur in den Attributen
`template`, `identifier`, `page` und als Controller-Name (siehe Kapitel soundso). Allerdings kann
es auch zwischen anderen Zeichen stehen, `"template": "seite-?2"` wäre also auch gültig.

*Übrigens:* Dass im Falle von https://example.org/artikel das zweite Pfadsegment fehlt, ist kein
Problem. Der Router setzt für das `page`-Attribut automatisch den Wert `1` ein, falls das
Pfadsegment leer ist.

## Templating
Wie schon beschrieben, benutzt Blog ausschließlich HTML mit Inline-PHP, um Templates zu erstellen.
Es ist nicht nötig, eine neue und komplizierte Templating-Sprache zu erlernen, die am Ende sowieso
wieder zu HTML geparst wird.

Grundsätzlich gilt die Regel »Eine Route – Ein Template«. Für jede Route (also für jede
unterschiedliche Seitenstruktur) soll es auch ein eigenes Template geben. Ein einfaches Template
sieht vielleicht folgendermaßen aus:

	<!DOCTYPE html>
	<html lang="de">
		<head>
			<meta charset="utf-8">
			<title><?= $site->title ?></title>
			<link rel="stylesheet" type="text/css" href="<?= $server->url ?>/resources/css/style.css">
		</head>
		<body>
			<header>
				…
			</header>
			<main>
				<h1>Herzlich Willkommen</h1>
				<section>
					<h2>Neueste Blogeinträge</h2>

					<?php foreach($Post->objects as $post){ ?>
						<article>
							<h3><?= $post->headline ?></h3>
							<p><?= $post->author ?></p>
							<p><?= $post->teaser ?></p>
							<a href="<?= $server->url ?>/posts/<?= $post->longid ?>">weiterlesen</a>
						</article>
					<?php } ?>

				</section>
			</main>
			<footer>
				…
			</footer>
		</body>
	</html>

Dieses Template könnte die Startseite sein, auf der die neuesten Blogartikel angezeigt werden. Man
sieht bereits, dass ein Blog-Template eigentlich keine komplizierte Sache ist.
