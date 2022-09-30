<h1>Blog-Admin-Bereich</h1>
<p>Guten Tag.</p>

<h2>Version 0.12.2-beta: Changelogs</h2>
<ul>
	<li>
		<h3>Neue Funktionen und Objekte</h3>
		<ul>
			<li>Neue Klasse Medium mit Unterklassen Image, Application (Dateien/Dokumente), (Audio und Video noch nicht aktiviert)</li>
			<li>Klasse "Motion" für Anträge</li>
			<li>Intuiviverer Admin-Bereich</li>
		</ul>
	</li>
	<li>
		<h3>Neuer FileManager</h3>
		<ul>
			<li>Upload von Dokumenten (z.B. PDF) ermöglicht</li>
			<li>Bild-Upload mit weniger Fehlern</li>
			<li>Bild-Skalierung neu programmiert</li>
			<li>Bild-Neuskalierung auch im Nachhinein möglich</li>
			<li>Dateimanagement verbessert</li>
		</ul>
	</li>
	<li>
		<h3>Controller neu geschrieben</h3>
		<ul>
			<li>API- und HTML-Endpoint zusammengefasst</li>
			<li>Neues Routing</li>
			<li>Neue Objekt- und Controller-Registrierung</li>
			<li>Besseres Error-Handling</li>
			<li>Verbesserte Pfadnotation</li>
			<li>Erweiterte Platzhalter für Routing-Tabellen</li>
			<li>Objekt-Controller in einen DataObjectController zusammengefasst</li>
			<li>Neue, intuitivere Pagination</li>
		</ul>
	</li>
	<li>
		<h3>Neue Rest-API</h3>
		<ul>
			<li>Count-Funktionen für Objektlisten und Paginatables</li>
			<li>Verbessertes Error-Handling: Fehler werden jetzt als JSON angezeigt</li>
			<li>Kleinere Änderungen im Aussehen der Antworten</li>
		</ul>
	</li>
	<li>
		<h3>Umstrukturierung</h3>
		<ul>
			<li>Routing-Tabellen aus Config entfernt</li>
			<li>Pagination-Config aus Config entfernt</li>
			<li>Templates dezentralisiert; werden jetzt beim jeweiligen Endpoint abgelegt</li>
			<li>Ressourcen dezentralisiert; werden jetzt beim jeweiligen Endpoint abgelegt</li>
		</ul>
	</li>
	<li>Viele viele Bugfixes</li>
</ul>

<h2>Geplant für die nächsten Versionen</h2>
<ul>
	<li>DataObjectCollection: Sammlung verschiedener DataObjects, die in ein Markdown-Feld eingebunden werden können (z.B. einen Artikel)</li>
