# Routing
Octopus erlaubt es dem Anwender, frei festzulegen, wie die URLs seiner Seite strukturiert sein
sollen. Damit Octopus dennoch Anfragen beantworten kann, muss für jeden Endpoint konfiguriert
werden, wie verschiedene Anfragen an diesen zu bearbeiten sind. Dies geschieht in Routing-
Konfigurationen.

Eine Routing-Konfiguration ist ein Array, das aus einzelnen Routen besteht. Eine Route gilt immer
für einen einzelnen oder eine Gruppe von Pfaden. In ihr wird festgelegt, welches Template, welche
Objekte und welche Controller geladen werden sollen.

Eine Route ist ein Schlüssel-Wert-Paar, das folgendermaßen aufgebaut ist:

	*Pfadnotation* => [
		'template' => *Template-Name*,
		'methods' => *erlaubte HTTP-Methoden*,
		'contentTypes' => *erlaubte HTTP-Content-Types*,
		'objects' => [
			*Array von Objekt-Aufrufen*
		],
		'controllers' => [
			*Array von Controller-Aufrufen*
		]
	]

## Pfadnotation
Eine Route beginnt immer mit der Notation des zugehörigen Pfades bzw. der zugehörigen Gruppe von
Pfaden. Um eine Gruppe von Pfaden handelt es sich dann, wenn die Pfadnotation Unbekannte enthält.
Die Pfadnotation ist im Routing-Array im der der Schlüssel; der Wert ist die Konfiguration der
jeweiligen Route.

// TODO Matching-Ablauf

Es gibt verschiedene Möglichkeiten, Pfade und Pfadgruppen zu notieren:

### Statische Form
Die statische Notation ist die einfachste; man schreibt den Pfad einfach so, wie er in der URL
aussehen soll, beispielsweise:

	[
		'blog' => […],
		'blog/mein-erster-blogartikel' => […]
	]

und so weiter. Die Schrägstriche (/) am Anfang und Ende werden übrigens ausgelassen.

Diese Notation eignet sich gut für Routen, die eine einzigartige Seite beschreiben, beispielsweise
die Seite `blog`, auf der alle Blogeinträge aufgelistet werden. Eine Seite dieser Art gibt es nur
ein Mal.

Schlecht geeignet ist diese Notation für sehr viele gleichartige Seiten, beispielsweise für einzelne
Blogartikel. Man müsste für jeden neuen Artikel eine neue Route anlegen, die sich auch noch kaum
von den anderen unterscheidet. Das wäre äußerst unpraktisch. Deshalb legt man für eine Gruppe
gleichartiger Seiten, also beispielsweise für alle Einzel-Blogartikel, nur eine einzelne Route an,
deren variable Teile durch Platzhalter ersetzt werden. Der folgende Abschnitt behandelt eine solche
Notationsweise.

### PathPatterns
Ein PathPattern ist eine Pfadnotation, die einfache Platzhalter ermöglicht. So würde man unsere
obige Route für den einzelnen Blogartikel besser mit dem PathPattern `blog/*` versehen. So muss
man nur eine Route beschreiben, die für mehrere gleichartige Seiten, z.B.
`blog/mein-erster-blogartikel`, `blog/urlaubsgruesse` und `blog/mein-geburtstag`, gilt.

*Zum Überprüfen:* Ein gültiges PathPattern wird durch diesen regulären Ausdruck definiert:
`^([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?))*([\*#]*\??)?$`

#### Segmente
Um zu verstehen, wie PathPatterns arbeiten, hilft es, sich einen Pfad als eine Liste von Segmenten,
die durch Schrägstriche voneinander getrennt sind, vorzustellen. Der Pfad `blog/mein-geburtstag`
besteht aus den Segmenten `blog` und `mein-geburtstag`. PathPatterns ermöglichen es nun, einzelne
Segmente durch Platzhalter zu ersetzen.

#### Platzhalter
Folgende Zeichen dienen als Platzhalter für ein Segment:

- `*` steht für beliebige Zeichen, also Buchstaben, Zahlen, Sonderzeichen usw.
- `#` steht für beliebige Zahlzeichen, also 0–9.

Ein Platzhalter bezieht sich immer auf genau ein Segment des Pfad-Abschnitts einer URL. Ein
Platzhalter kann weder mehr als ein Segment (und damit auch keine Schrägstriche) umfassen,
noch kann er sich auf den Query-String (der Teil mit dem Fragezeichen hinter dem Pfad) o.ä.
beziehen.  
Außerdem darf es nur einen Platzhalter pro Segment geben und es dürfen keine anderen Zeichen in
diesen Segmenten stehen (außer den im Folgenden beschriebenen Quantifikatoren).

#### Quantifikatoren
Stehen `*` oder `#` alleine in einem Segment, trifft der jeweilige Platzhalter auf Zeichenketten
beliebiger Länge zu. Das PathPattern `blog/*` würde also auf die URL `https://example.org/blog/a`
genauso zutreffen wie auf `https://example.org/blog/abcdefghijklmnopqrstuvwxyz1234567890`.
Nun gibt es aber Fälle, in denen es sinnvoll ist, die erlaubte Länge eines Platzhalters zu
begrenzen.

Dafür gibt es Quantifikatoren. Diese werden dem Platzhalter angehängt. Folgende Varianten sind
möglich:

- `{n}`, wobei *n* für eine natürliche Zahl steht, also z.B. `{1}`, `{20}` oder `{340}`. Dieser
Quantifikator schreibt vor, dass der davorstehende Platzhalter nur für *n* Zeichen stehen darf.
- `{n,m}`, wobei *n* und *m* für natürliche Zahlen stehen, bei *n* inklusive der 0; also z.B.
`{0,5}`, `{6,40}` oder `{128,256}`. Schreibt vor, dass der davorstehende Platzhalter für *n bis m*
Zeichen stehen darf.
- `{n,}`: Im vorherigen Fall darf der zweite Parameter *m* auch weggelassen werden. Dies steht dann
für *mindestens n* Zeichen. Die umgekehrte Schreibweise, also *n* wegzulassen, ist nicht erlaubt.
- `?` ist ein Spezialfall. Es darf nur hinter dem letzten Segment stehen und macht dieses optional.
Das kann hilfreich sein, wenn man beispielsweise auf der Blogartikel-Liste (`blog`) nicht alle
Artikel auf einmal anzeigen möchte, sondern diese auf mehrere Seiten zu je x Artikeln aufteilen
möchte (sog. *pagination*). Dann würde man das PathPattern `blog/#?` verwenden, das sowohl auf
`https://example.org/blog` (für die erste Seite) als auch auf `https://example.org/blog/10` (für
die 10. Seite) zutrifft.

Quantifikatoren dürfen nur hinter Platzhaltern stehen; niemals davor, alleine oder hinter anderen
Zeichen!

#### Leerer Pfad und Wildcard
Es gibt zwei wesentliche Fälle, die man bisher mit PathPatterns nicht abbilden kann: Leere Pfade,
also etwa die URL `https://example.org/`, und eine Notation für jeden beliebigen Pfad (Wildcard).
Dafür können folgende Notationen verwendet werden, die selbsterklärend nur vollkommen alleinstehend
gültig sind:

- `/` steht für den leeren Pfad, üblicherweise also die Startseite.
- `?` steht für jeden beliebigen Pfad. Es ist also eine Art *Catch-All*. Aufgrund der
Matching-Reihenfolge des Routers sollte die Route mit dieser Notation die letzte im Routen-Array
sein (nachfolgende Routen können gar nicht mehr aufgerufen werden). Sie wird häufig für dynamische,
also von Octopus verwaltete und in der Datenbank gespeicherte Seiten (pages) verwendet.

### Reguläre Ausdrücke (RegEx)
Reguläre Ausdrücke sind die komplizierteste, aber auch mächtigste Form für die Pfadnotation.
Anfänger werden sie wahrscheinlich noch nicht benötigen, geübten Nutzern ermöglichen sie aber
fast absolute Freiheit in der Gestaltung ihrer Pfadkonfiguration.
Sie beginnen mit `/^`, was den Anfang des Pfades kennzeichnet, und enden mit `$/` für das Ende.
Dazwischen können alle bekannten RegEx-Suchmuster eingesetzt werden.

Zur Prüfung wird ausschließlich der Pfadabschnitt der URL herangezogen, also nicht der Host, nicht
der Query-String (`?xy=z`) und auch nicht das Fragment (`#abc`). Außerdem werden eventuell
vorhandene Schrägstriche an Anfang und Ende abgeschnitten. Der RegEx prüft aus der URL
`https://example.org/blog/mein-erster-blogartikel/?queryString=true#kapitel-2` also nur den
Abschnitt `blog/mein-erster-blogartikel`. Bei der Pfadnotation mittels RegEx sollte dies bedacht
werden.

__Wichtig:__ Die Schrägstriche (`/`), die die Pfadsegmente trennen, müssen mit Backslashes (`\`)
escapt werden, weil sie sonst als RegEx-Endzeichen missinterpretiert würden.

Octopus verwendet beim internen Matching nur RegEx, schreibt also statische Notationen und
PathPatterns in reguläre Ausdrücke um. Daher dürften Teile der PathPatterns den RegEx-Experten
auch schon bekannt vorkommen; sie wurden einfach davon übernommen.
Beispiele für die interne Umschreibung sind:

- `blog/mein-erster-blogartikel` wird zu `/^blog\/mein-erster-blogartikel$/`
- `blog/*` wird zu `/^blog\/[^\/]+$/`
- `blog/*{0,8}` wird zu `/^blog\/[^\/]{0,8}$/`
- `blog/#?` wird zu `/^blog(\/[0-9]+)?$/`
- `/` (leerer Pfad) wird zu `/^$/`
- `*` (Wildcard) wird zu `/^.*$/`


## Eigenschaften der Route
Nachdem mit der Pfadnotation der Schlüssel einer Route festgelegt wurde, müssen wir nun den Wert
der Route beschreiben. Dieser ist ein Array, in dem Eigenschaften aufgeführt sind. Wir behandeln
hier erst einmal die Eigenschaften `template`, `methods` und `contentTypes`; für die Objekte und
Controller gibt es eigene Unterkapitel.

- `template` (optional): Gibt den Dateinamen des Templates an. Folgende Datei wird damit geladen:
`{Template-Pfad}/{Template-Name}.php`. Der Template-Pfad wird im Endpoint festgelegt; der
Template-Name darf auch Schrägstriche (/) enthalten, falls das Template in einem Unterordner liegt.
Es darf aber nicht in übergeordnete Ordner (z.B. mittels ../) gesprungen werden.
- `methods` (optional): Gibt an, welche HTTP-Methoden in der Anfrage akzeptiert werden. Erwartet
wird ein Array mit allen erlaubten Methoden (`GET` und `POST`). Standardmäßig werden alle Methoden
akzeptiert.
- `contentTypes` (optional): Gibt an, welche HTTP-Content-Types in der Anfrage akzeptiert werden.
Erwartet wird ein Array mit den erlaubten MIME-Typen. Standardmäßig werden alle MIME-Typen
akzeptiert. Wird nur überprüft, wenn die Anfrage als POST gesendet wurde; ansonsten ignoriert.

## Objekte und Controller





















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
