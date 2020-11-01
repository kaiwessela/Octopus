<div class="imageinput template main">
	<div class="pseudoinput">
		<template class="empty">
			<div>Kein Bild ausgewählt.</div>
		</template>
		<template class="filled">
			<img src="<?= $server->url . $server->dyn_img_path ?>{{longid}}/original.{{extension}}"
				alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden.">
			<code>{{longid}}</code>
		</template>
		<div class="preview">

		</div>
		<button type="button" class="red" data-action="clear">entfernen</button>
		<button type="button" class="blue" data-action="picker">aus Vorhandenen auswählen</button>
		<button type="button" class="green" data-action="uploader">Neues hochladen</button>
	</div>
</div>

<div class="imageinput template picker">
	<div class="dialog">
		<form>
			<h2>Bild auswählen</h2>
			<div class="grid">

			</div>
			<button type="button" class="red" data-action="close">Abbrechen</button>
			<template>
				<button type="button" name="{{id}}">
					<img src="<?= $server->url . $server->dyn_img_path ?>{{longid}}/original.{{extension}}" alt="[ANZEIGEFEHLER]">
					<code>{{longid}}</code>
				</button>
			</template>
		</form>
	</div>
</div>

<div class="imageinput template uploader">
	<div class="dialog">
		<form>
			<h2>Bild hochladen</h2>
			<label for="imageinput-uploader-longid">
				<span class="name">Bild-ID</span>
				<span class="conditions">
					erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
					Bindestriche (-)
				</span>
				<span class="infos">
					Die Bild-ID wird in der URL verwendet und sollte den Bildinhalt kurz
					beschreiben.
				</span>
			</label>
			<input type="text" id="imageinput-uploader-longid" class="longid" name="longid" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" autocomplete="off">
			<label for="imageinput-uploader-description">
				<span class="name">Beschreibung</span>
				<span class="conditions">optional</span>
				<span class="infos">
					Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
					werden kann. Sie sollte den Bildinhalt wiedergeben.
				</span>
			</label>
			<input type="text" id="imageinput-uploader-description" class="description" name="description" size="60" maxlength="100">
			<label for="copyright">
				<span class="name">Urheberrechtshinweis</span>
				<span class="conditions">optional</span>
				<span class="infos">
					Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
					zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
				</span>
			</label>
			<input type="text" id="imageinput-uploader-copyright" class="copyright" name="copyright" size="50" maxlength="100">
			<label for="imageinput-uploader-file">
				<span class="name">Datei</span>
				<span class="conditions">erforderlich; PNG, JPEG oder GIF</span>
			</label>
			<input type="file" id="imageinput-uploader-file" class="file" name="imagefile">
			<button type="submit" class="green" data-action="submit">Hochladen</button>
			<button type="button" class="red" data-action="close">Abbrechen</button>
		</form>
	</div>
</div>

<script src="<?= $server->url ?>/resources/js/admin/image.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/picker.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/uploader.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/imageinput.js"></script>
<script>
	var imageinput = new ImageInput(document.querySelector('.imageinput'));
	imageinput.invoke();
</script>
