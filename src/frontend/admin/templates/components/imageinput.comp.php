<?php
use \Blog\Config\Config;
?>

<template id="iit-basic">
	<div class="ii-imagebox">

	</div>
	<input type="hidden" id="ii-value" name="" value="">
	<button type="button" class="negative" id="ii-basic-clear">Bild entfernen</button>
	<button type="button" id="ii-basic-pick">Aus vorhandenen Bildern auswählen</button>
	<button type="button" id="ii-basic-upload">Neues Bild hochladen</button>
</template>

<template id="iit-imagebox-filled">
	<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH ?>%II.image.longid%/original.jpg" alt="Ausgewähltes Bild">
</template>

<template id="iit-imagebox-empty">
	<div class="ii-imagebox-empty">Kein Bild ausgewählt</div>
</template>

<template id="iit-picker">
	<div class="dialog">
		<div class="masonry">

		</div>
		<button type="button" id="ii-picker-cancel">Abbrechen</button>
	</div>
</template>

<template id="iit-uploader">
	<div class="dialog">
		<form>
			<label for="ii-uploader-longid">URL</label>
			<input type="text" id="ii-uploader-longid" name="longid">
			<label for="ii-uploader-description">Beschreibung</label>
			<input type="text" id="ii-uploader-description" name="description">
			<label for="ii-uploader-file">Datei</label>
			<input type="file" id="ii-uploader-file" name="imagefile">
			<input type="submit" value="Hochladen">
			<button type="button" id="ii-uploader-cancel">Abbrechen</button>
		</form>
	</div>
</template>

<template id="iit-selectableimage">
	<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH ?>%II.image.longid%/original.jpg" alt="Bild">
</template>

<script src="<?= Config::SERVER_URL ?>/admin/resources/js/selectableimage.js"></script>
<script src="<?= Config::SERVER_URL ?>/admin/resources/js/imageinputpicker.js"></script>
<script src="<?= Config::SERVER_URL ?>/admin/resources/js/imageinputuploader.js"></script>
<script src="<?= Config::SERVER_URL ?>/admin/resources/js/imageinput.js"></script>
<script src="<?= Config::SERVER_URL ?>/admin/resources/js/script.js"></script>
