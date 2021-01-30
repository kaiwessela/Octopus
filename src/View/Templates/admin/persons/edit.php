<form action="#" method="post" class="persons edit">

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

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Person?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Person?->longid ?>" size="40" readonly>

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

	<!-- GROUPS -->
	<label>
		<span class="name">Gruppenmitgliedschaften</span>
		<span class="conditions">optional, Mehrfacheintrag möglich</span>
		<span class="info">
			Änderungen werden lokal zwischengespeichert und beim Abschicken übernommen.
		</span>
	</label>
	<div class="relationinput nojs" data-type="Group" data-unique="false" data-for="groups" data-selectmodal="groups-select">
		<div class="objects">
			<?php if($Person?->grouprelations){ foreach($Person->grouprelations as $i => $rel){ ?>
				<div class="relation" data-i="<?= $i ?>" data-exists="true">
					<input type="hidden" name="grouprelations[<?= $i ?>][id]" value="<?= $rel->id ?>">
					<input type="hidden" name="grouprelations[<?= $i ?>][action]" class="action" value="ignore">
					<input type="hidden" name="grouprelations[<?= $i ?>][group_id]" class="objectId" value="<?= $rel->group->id ?>">
					<input type="hidden" name="grouprelations[<?= $i ?>][person_id]" value="<?= $Person?->id ?>">
					<p class="title"><span><?= $rel->group->name ?></span> – <code><?= $rel->group->longid ?></code></p>
					<button type="button" class="red" data-action="remove">Entfernen</button>
					<button type="button" data-action="restore">Entf. rückgängig</button>
				</div>
			<?php }} ?>
			<template>
				<div class="relation" data-i="{{i}}" data-exists="false">
					<input type="hidden" name="grouprelations[{{i}}][action]" class="action" value="new">
					<input type="hidden" name="grouprelations[{{i}}][group_id]" class="objectId" value="{{id}}">
					<input type="hidden" name="grouprelations[{{i}}][person_id]" value="<?= $Person?->id ?>">
					<p class="title"><span>{{name}}</span> – <code>{{longid}}</code></p>
					<button type="button" class="red" data-action="remove">Entfernen</button>
				</div>
			</template>
		</div>
		<button type="button" class="new blue" data-action="select">Gruppe(n) hinzufügen</button>
	</div>


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

<div class="modal multiselectmodal nojs" data-name="groups-select" data-type="Group" data-objectsperpage="20">
	<div class="box">
		<h2>Gruppen auswählen</h2>
		<form action="#" method="GET">
			<section class="objects">
				<template>
					<article>
						<label class="checkbodge turn-around">
							<span class="label-field">{{name}} – {{longid}}</span>
							<input type="checkbox" name="result" value="{{id}}">
							<span class="bodgecheckbox">
								<span class="bodgetick">
									<span class="bodgetick-down"></span>
									<span class="bodgetick-up"></span>
								</span>
							</span>
						</label>
					</article>
				</template>
			</section>
			<button type="button" data-action="loadmore">Weitere Gruppen laden</button><br>
			<button type="submit" data-action="submit" class="blue">Auswählen</button>
			<button type="button" data-action="close" class="red">Schließen</button>
		</form>
	</div>
</div>
