<form action="#" method="post">

<?php if($PersonController->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Personen-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Personen-ID wird in der URL verwendet und entspricht meistens dem Namen.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Person?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<input type="hidden" name="id" value="<?= $Person?->id ?>">
	<input type="hidden" name="longid" value="<?= $Person?->longid ?>">

<?php } ?>

	<!-- NAME -->
	<label for="name">
		<span class="name">Name</span>
		<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
		<span class="infos">
			Der vollständige Name der Person.
		</span>
	</label>
	<input type="text" size="30"
		id="name" name="name" value="<?= $Person?->name ?>"
		maxlength="50" required>

	<!-- IMAGE -->
	<label for="image_id">
		<span class="name">Profilbild</span>
		<span class="conditions">optional</span>
		<span class="infos">
			Das Profilbild sollte ein Portrait der Person sein.
		</span>
	</label>
	<input type="text" class="imageinput" size="8"
		id="image_id" name="image_id" value="<?= $Person?->image?->id ?>"
		minlength="8" maxlength="8">

	<button type="submit" class="green">Speichern</button>
</form>

<div class="modal selectmodal nojs" data-name="image-select" data-type="Image" data-objectsperpage="20">
	<div class="box">
		<h2>Bild auswählen</h2>
		<div class="pagination">
			<template>
				<button type="button" data-action="paginate" data-page="{{page}}">{{page}}</button>
			</template>
		</div>
		<form action="#" method="GET">
			<section class="objects">
				<template>
					<article>
						<label>
							<input type="radio" name="result" value="{{id}}" {{current}}>
							<img src="<?= $server->url ?>/<?= $server->dyn_img_path ?>/{{longid}}/original.{{extension}}">
						</label>
					</article>
				</template>
			</section>
			<button type="submit" data-action="submit" class="blue">Auswählen</button>
			<button type="button" data-action="close" class="red">Schließen</button>
		</form>
	</div>
</div>

<div class="modal uploadmodal nojs" data-name="image-upload" data-type="Image">
	<div class="box">
		<h2>Neues Bild hochladen</h2>
		<form action="#" method="GET">
			<label for="image-upload-longid">
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
				id="image-upload-longid" name="longid"
				minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

			<label for="image-upload-description">
				<span class="name">Beschreibung</span>
				<span class="conditions">optional, bis zu 100 Zeichen</span>
				<span class="infos">
					Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
					werden kann. Sie sollte den Bildinhalt wiedergeben.
				</span>
			</label>
			<input type="text" size="60"
				id="image-upload-description" name="description"
				maxlength="100">

			<label for="image-upload-copyright">
				<span class="name">Urheberrechtshinweis</span>
				<span class="conditions">optional, bis zu 100 Zeichen</span>
				<span class="infos">
					Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
					zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
				</span>
			</label>
			<input type="text" size="50"
				id="image-upload-copyright" name="copyright"
				maxlength="100">

			<label for="image-upload-imagedata">
				<span class="name">Datei</span>
				<span class="conditions">erforderlich; PNG, JPEG oder GIF</span>
			</label>
			<input type="file" class="file"
				id="image-upload-imagedata" name="imagedata" required>

			<button type="submit" data-action="submit" class="green">Hochladen</button>
			<button type="button" data-action="close" class="red">Schließen</button>
		</form>
	</div>
</div>

<div class="pseudoinput nojs" data-type="Image" data-for="image_id" data-selectmodal="image-select" data-uploadmodal="image-upload">
	<div class="object"></div>
	<template data-state="empty">
		<p>Kein Bild ausgewählt.</p>
	</template>
	<template data-state="set">
		<figure>
			<img src="<?= $server->url ?>/<?= $server->dyn_img_path ?>/{{longid}}/original.{{extension}}" alt="{{description}}">
			<figcaption>{{longid}}</figcaption>
		</figure>
	</template>
	<button type="button" class="blue" data-action="select">Aus vorhandenen Bildern auswählen</button>
	<button type="button" class="green" data-action="upload">Neues Bild hochladen</button>
	<button type="button" class="red" data-action="clear">Bild entfernen</button>
</div>
