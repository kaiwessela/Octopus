# Blog
Mein persönliches Blog-System

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
