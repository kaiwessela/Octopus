<?php use \Blog\Config\MediaConfig; ?>

<form action="#" method="post" enctype="multipart/form-data" class="images edit">

<?php if($Controller->get_action() == 'empty'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Bild-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Bild-ID wird in der URL verwendet und sollte auf den Titel oder Inhalt hinweisen.
		</span>
	</label>
	<input type="text" size="60" autocomplete="off"
		id="longid" name="longid" value="<?= $Object?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Object?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Object?->longid ?>" size="60" readonly>

	<img src="<?= $Object->src() ?>" alt="<?= $Object->alternative ?>">

<?php } ?>

	<!-- TITLE -->
	<label for="title">
		<span class="name">Titel</span>
		<span class="conditions">optional, bis zu 140 Zeichen</span>
		<span class="infos">
			Der Titel des Bildes.
		</span>
	</label>
	<input type="text" size="100"
		id="title" name="title" value="<?= $Object?->title ?>"
		maxlength="140">

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional, bis zu 250 Zeichen</span>
		<span class="infos">
			Eine kurze Beschreibung des Bildes.
		</span>
	</label>
	<input type="text" size="100"
		id="description" name="description" value="<?= $Object?->description ?>"
		maxlength="250">

	<!-- COPYRIGHT -->
	<label for="copyright">
		<span class="name">Urheberrechtshinweis</span>
		<span class="conditions">optional, bis zu 250 Zeichen</span>
		<span class="infos">
			Der Urbeherrechtshinweis kann genutzt werden, um den Urheber des Bildes und die Lizenz,
			unter der es zur Verfügung steht, anzugeben.
		</span>
	</label>
	<input type="text" size="100"
		id="copyright" name="copyright" value="<?= $Object?->copyright ?>"
		maxlength="250">

	<!-- ALTERNATIVE -->
	<label for="alternative">
		<span class="name">Alternativtext</span>
		<span class="conditions">optional, bis zu 250 Zeichen</span>
		<span class="infos">
			Der Alternativtext wird angezeigt, wenn das Bild nicht geladen werden kann oder der
			Benutzer einen Screenreader nutzt. Er sollte den Bildinhalt wiedergeben.
		</span>
	</label>
	<input type="text" size="100"
		id="alternative" name="alternative" value="<?= $Object?->alternative ?>"
		maxlength="250">


<?php if($Controller->call->action == 'new'){ ?>

	<!-- IMAGEFILE -->
	<label for="file">
		<span class="name">Datei</span>
		<span class="conditions">erforderlich</span>
	</label>
	<input type="file" class="file"
		id="file" name="file" required
		accept="<?= implode(', ', MediaConfig::IMAGE_TYPES); ?>">

<?php } else if($Controller->call->action == 'edit'){ ?>

	<!-- REWRITE -->
	<label for="rewrite">
		<span class="name">Bilddateien wiederherstellen und neu berechnen</span>
		<span class="infos">
			Damit werden die automatisch herunterskalierten Bildversionen, die im Dateisystem gespeichert
			sind, neu berechnet und gespeichert. Auswählen, falls Fehler bei den Versionen erkannt
			wurden.
		</span>
	</label>
	<label class="checkbodge turn-around">
		<span class="label-field">Dateien neu berechnen</span>
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
