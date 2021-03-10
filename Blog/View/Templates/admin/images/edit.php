<form action="#" method="post" enctype="multipart/form-data" class="images edit">

<?php if($ImageController->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Bild-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Bild-ID wird in der URL verwendet und sollte den Bildinhalt kurz
			beschreiben.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Image?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Image?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Image?->longid ?>" size="40" readonly>

	<img src="<?= $Image->src() ?>" alt="[ANZEIGEFEHLER]">

<?php } ?>

	<!-- TITLE -->
	<label for="title">
		<span class="name">Titel</span>
		<span class="conditions">optional, bis zu 60 Zeichen</span>
		<span class="infos">
			Der Titel des Bildes.
		</span>
	</label>
	<input type="text" size="50"
		id="title" name="title" value="<?= $Image?->title ?>"
		maxlength="60">

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional, bis zu 250 Zeichen</span>
		<span class="infos">
			Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
			werden kann. Sie sollte den Bildinhalt wiedergeben.
		</span>
	</label>
	<input type="text" size="60"
		id="description" name="description" value="<?= $Image?->description ?>"
		maxlength="250">

	<!-- COPYRIGHT -->
	<label for="copyright">
		<span class="name">Urheberrechtshinweis</span>
		<span class="conditions">optional, bis zu 250 Zeichen</span>
		<span class="infos">
			Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
			zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
		</span>
	</label>
	<input type="text" size="50"
		id="copyright" name="copyright" value="<?= $Image?->copyright ?>"
		maxlength="250">

	<!-- ALTERNATIVE -->
	<label for="copyright">
		<span class="name">Alternativtext</span>
		<span class="conditions">optional, bis zu 250 Zeichen</span>
		<span class="infos">
			Der Alternativtext wird angezeigt, wenn das Bild nicht geladen werden kann oder der
			Benutzer einen Screenreader nutzt. Er sollte den Bildinhalt möglichst genau wiedergeben.
		</span>
	</label>
	<input type="text" size="50"
		id="alternative" name="alternative" value="<?= $Image?->alternative ?>"
		maxlength="250">


<?php if($ImageController->request->action == 'new'){ ?>

	<!-- IMAGEFILE -->
	<label for="file">
		<span class="name">Datei</span>
		<span class="conditions">erforderlich; PNG, JPEG oder GIF</span>
	</label>
	<input type="file" class="file"
		id="file" name="file" required>

<?php } else if($ImageController->request->action == 'edit'){ ?>

	<!-- REWRITE -->
	<label for="rewrite">
		<span class="name">Bildgrößen neu berechnen</span>
		<span class="infos">
			Damit werden die automatisch verkleinerten Bildversionen, die im Dateisystem gespeichert
			sind, neu berechnet. Auswählen, wenn Fehler bei den Versionen erkannt wurden.
		</span>
	</label>
	<label class="checkbodge turn-around">
		<span class="label-field">Neu berechnen</span>
		<input type="checkbox" id="rewrite" name="rewrite" value="true">
		<span class="bodgecheckbox">
			<span class="bodgetick">
				<span class="bodgetick-down"></span>
				<span class="bodgetick-up"></span>
			</span>
		</span>
	</label>

<?php } ?>

	<button type="submit" class="green">Speichern</button>
</form>
