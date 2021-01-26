<form action="#" method="post" enctype="multipart/form-data">

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

	<input type="hidden" id="id" name="id" value="<?= $Image?->id ?>">
	<input type="hidden" id="longid" name="longid" value="<?= $Image?->longid ?>">

<?php } ?>

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional, bis zu 100 Zeichen</span>
		<span class="infos">
			Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
			werden kann. Sie sollte den Bildinhalt wiedergeben.
		</span>
	</label>
	<input type="text" size="60"
		id="description" name="description" value="<?= $Image?->description ?>"
		maxlength="100">

	<!-- COPYRIGHT -->
	<label for="copyright">
		<span class="name">Urheberrechtshinweis</span>
		<span class="conditions">optional, bis zu 100 Zeichen</span>
		<span class="infos">
			Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
			zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
		</span>
	</label>
	<input type="text" size="50"
		id="copyright" name="copyright" value="<?= $Image?->copyright ?>"
		maxlength="100">


<?php if($ImageController->request->action == 'new'){ // TODO TODO TODO see in backend how invalid image requests are handled ?>

	<!-- IMAGEFILE -->
	<label for="imagefile">
		<span class="name">Datei</span>
		<span class="conditions">erforderlich; PNG, JPEG oder GIF</span>
	</label>
	<input type="file" class="file"
		id="imagefile" name="imagedata" required>

<?php } ?>

	<button type="submit" class="green">Speichern</button>
</form>

<?php if($ImageController->request->action == 'edit'){ ?>
<br>
<img src="<?= $Image->src() ?>" alt="[ANZEIGEFEHLER]">
<?php } ?>
