<?php
use \Blog\Config\Config;
?>

<template id="iit-basic">
	<div class="ii-imagebox">

	</div>
	<input type="hidden" id="ii-value" name="" value="">
	<button type="button" id="ii-basic-clear">entfernen</button>
	<button type="button" id="ii-basic-pick">aus Liste auswählen</button>
	<button type="button" id="ii-basic-upload">hochladen</button>
</template>

<template id="iit-imagebox-filled">
	<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH ?>%II.image.longid%/original.%II.image.extension%"
		alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden.">
	<p>Bild-ID: <span class="code">%II.image.longid%</span></p>
</template>

<template id="iit-imagebox-empty">
	<div class="ii-imagebox-empty">Kein Bild ausgewählt.</div>
</template>

<template id="iit-picker">
	<div class="dialog">
		<div>
			<h2>Bild auswählen</h2>
			<div class="grid">

			</div>
			<button type="button" id="ii-picker-cancel">Abbrechen</button>
		</div>
	</div>
</template>

<template id="iit-uploader">
	<div class="dialog">
		<form>
			<h2>Bild hochladen</h2>
			<label for="ii-uploader-longid">
				<span class="name">Bild-ID</span>
				<span class="requirements">
					erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
					Bindestriche (-)
				</span>
				<span class="description">
					Die Bild-ID wird in der URL verwendet und sollte den Bildinhalt kurz
					beschreiben.
				</span>
			</label>
			<input type="text" id="ii-uploader-longid" class="longid" name="longid">
			<label for="ii-uploader-description">
				<span class="name">Beschreibung</span>
				<span class="requirements">optional</span>
				<span class="description">
					Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
					werden kann. Sie sollte den Bildinhalt wiedergeben.
				</span>
			</label>
			<input type="text" id="ii-uploader-description" class="description" name="description">
			<label for="copyright">
				<span class="name">Urheberrechtshinweis</span>
				<span class="requirements">optional</span>
				<span class="description">
					Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
					zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
				</span>
			</label>
			<input type="text" id="ii-uploader-copyright" class="copyright" name="copyright">
			<label for="ii-uploader-file">
				<span class="name">Datei</span>
				<span class="requirements">erforderlich; PNG, JPEG oder GIF</span>
			</label>
			<input type="file" id="ii-uploader-file" class="file" name="imagefile">
			<input type="submit" value="Hochladen">
			<button type="button" id="ii-uploader-cancel">Abbrechen</button>
		</form>
	</div>
</template>

<template id="iit-selectableimage">
	<article class="image preview">
		<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH ?>%II.image.longid%/original.%II.image.extension%" alt="[ANZEIGEFEHLER]">
		<span class="longid">%II.image.longid%</span>
	</article>
</template>

<script src="<?= Config::SERVER_URL ?>/resources/js/selectableimage.js"></script>
<script src="<?= Config::SERVER_URL ?>/resources/js/imageinputpicker.js"></script>
<script src="<?= Config::SERVER_URL ?>/resources/js/imageinputuploader.js"></script>
<script src="<?= Config::SERVER_URL ?>/resources/js/imageinput.js"></script>
<script src="<?= Config::SERVER_URL ?>/resources/js/admin.js"></script>
