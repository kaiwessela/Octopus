<?php use \Blog\Config\MediaConfig; ?>

<form action="#" method="post" enctype="multipart/form-data" class="images edit">

<?php if($ApplicationController->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Dokumenten-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Dokumenten-ID wird in der URL verwendet und sollte auf Titel oder Inhalt hinweisen.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Application?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Application?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Application?->longid ?>" size="40" readonly>

	<p><a href="<?= $Application->src() ?>">Datei: <?= $Application->longid.'.'.$Application->extension ?></a></p>

<?php } ?>

	<!-- TITLE -->
	<label for="title">
		<span class="name">Titel</span>
		<span class="conditions">optional, bis zu 60 Zeichen</span>
		<span class="infos">
			Der Titel des Dokuments.
		</span>
	</label>
	<input type="text" size="40"
		id="title" name="title" value="<?= $Application?->title ?>"
		maxlength="60">

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional, bis zu 250 Zeichen</span>
		<span class="infos">
			Eine kurze Beschreibung des Dokumentinhalts.
		</span>
	</label>
	<input type="text" size="60"
		id="description" name="description" value="<?= $Application?->description ?>"
		maxlength="250">

	<!-- COPYRIGHT -->
	<label for="copyright">
		<span class="name">Urheberrechtshinweis</span>
		<span class="conditions">optional, bis zu 250 Zeichen</span>
		<span class="infos">
			Der Urheberrechtshinweis kann verwendet werden, um den Urheber des Dokuments und die
			Lizenz, unter der es zur Verfügung steht, anzugeben.
		</span>
	</label>
	<input type="text" size="50"
		id="copyright" name="copyright" value="<?= $Application?->copyright ?>"
		maxlength="250">


<?php if($ApplicationController->request->action == 'new'){ // TODO TODO TODO see in backend how invalid image requests are handled ?>

	<!-- IMAGEFILE -->
	<label for="file">
		<span class="name">Datei</span>
		<span class="conditions">erforderlich</span>
	</label>
	<input type="file" class="file"
		id="file" name="file" required
		accept="<?= implode(', ', MediaConfig::APPLICATION_TYPES); ?>">

	<?php } else if($ApplicationController->request->action == 'edit'){ ?>

		<!-- REWRITE -->
		<label for="rewrite">
			<span class="name">Datei wiederherstellen</span>
			<span class="infos">
				Damit wird die Dokumentdatei aus der Datenbank geladen und neu im Dateisystem
				abgespeichert. Auswählen, falls die Datei nicht mehr abrufbar ist.
			</span>
		</label>
		<label class="checkbodge turn-around">
			<span class="label-field">Datei wiederherstellen</span>
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
