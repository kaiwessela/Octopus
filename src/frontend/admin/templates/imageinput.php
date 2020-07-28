<template id="iit-basic">
	<div class="ii-imagebox">

	</div>
	<input type="hidden" id="ii-value" name="" value="">
	<button type="button" id="ii-basic-clear">Bild entfernen</button>
	<button type="button" id="ii-basic-pick">Aus vorhandenen Bildern auswählen</button>
	<button type="button" id="ii-basic-upload">Neues Bild hochladen</button>
</template>

<template id="iit-imagebox-filled">
	<img src="<?= DYN_IMG_PATH ?>%II.image.longid%/original.jpg" alt="Ausgewähltes Bild">
</template>

<template id="iit-imagebox-empty">
	<div class="ii-imagebox-empty">Kein Bild ausgewählt</div>
</template>

<template id="iit-picker">

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
