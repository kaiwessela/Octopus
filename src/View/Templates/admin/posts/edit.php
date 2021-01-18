<form action="#" method="post">

<?php if($Controller->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Post-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Post-ID wird in der URL verwendet und entspricht oftmals ungefähr der Überschrift.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Post->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<input type="hidden" name="id" value="<?= $Post->id ?>">
	<input type="hidden" name="longid" value="<?= $Post->longid ?>">

<?php } ?>

	<!-- OVERLINE -->
	<label for="overline">
		<span class="name">Dachzeile</span>
		<span class="conditions">optional, bis zu 25 Zeichen</span>
		<span class="infos">
			Die Dachzeile steht direkt über der Überschrift und beinhaltet meist ein kurzes
			Stichwort, das das Thema des Artikels angibt.
		</span>
	</label>
	<input type="text" size="20"
		id="overline" name="overline" value="<?= $Post->overline ?>"
		maxlength="25">

	<!-- HEADLINE -->
	<label for="headline">
		<span class="name">Schlagzeile</span>
		<span class="conditions">erforderlich, 1 bis 60 Zeichen</span>
		<span class="infos">
			Die Schlagzeile ist die Überschrift des Artikels und fasst die Kernaussage prägnant
			zusammen.
		</span>
	</label>
	<input type="text" size="40"
		id="headline" name="headline" value="<?= $Post->headline ?>"
		maxlength="60" required>

	<!-- SUBLINE -->
	<label for="subline">
		<span class="name">Unterzeile</span>
		<span class="conditions">optional, bis zu 40 Zeichen</span>
		<span class="infos">
			Die Unterzeile steht unterhalb der Schlagzeile und ergänzt diese um weitere
			Informationen.
		</span>
	</label>
	<input type="text" size="30"
		id="subline" name="subline" value="<?= $Post->subline ?>"
		maxlength="40">

	<!-- TEASER -->
	<label for="teaser">
		<span class="name">Teaser</span>
		<span class="conditions">optional</span>
		<span class="infos">
			Der Teaser wird nur in der Artikelvorschau angezeigt. Er fasst den Artikel kurz
			zusammen und soll zum Weiterlesen anregen.
		</span>
	</label>
	<textarea id="teaser" name="teaser"
		cols="50" rows="3">
		<?= $Post->teaser ?>
	</textarea>

	<!-- AUTHOR -->
	<label for="author">
		<span class="name">Autor</span>
		<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
		<span class="infos">Der Autor des Artikels.</span>
	</label>
	<input type="text" size="30"
		id="author" name="author" value="<?= $Post->author ?>"
		maxlength="50" required>

	<!-- TIMESTAMP -->
	<label for="timestamp">
		<span class="name">Veröffentlichungsdatum und -uhrzeit</span>
		<span class="conditions">erforderlich</span>
		<span class="infos">
			Datum und Uhrzeit der Veröffentlichung. Hat derzeit nur eine informierende Funktion,
			Artikel mit Datum in der Zukunft werden trotzdem angezeigt. Es ist aber eine Funktion
			zur terminierten Veröffentlichung geplant.
		</span>
	</label>
	<input type="number" class="timeinput" size="10"
		id="timestamp" name="timestamp" value="<?= $Post->timestamp ?>"
		required>

	<!-- IMAGE -->
	<label for="image_id">
		<span class="name">Artikelbild</span>
		<span class="conditions">optional</span>
		<span class="infos">
			Das Artikelbild wird prominent zwischen Überschrift und Inhalt sowie in der
			Artikelvorschau angezeigt.
		</span>
	</label>
	<input type="text" class="imageinput" size="8"
		id="image_id" name="image_id" value="<?= $Post->image->id ?>"
		minlength="8" maxlength="8">

	<!-- CONTENT -->
	<label for="content">
		<span class="name">Inhalt</span>
		<span class="conditions">
			optional, Markdown-Schreibweise möglich
			(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
		</span>
		<span class="infos">Der eigentliche Inhalt des Artikels</span>
	</label>
	<textarea id="content" name="content"
		cols="80" rows="20">
		<?= $Post->content ?>
	</textarea>

	<!-- COLUMNS -->
	<label>
		<span class="name">Rubriken</span>
		<span class="conditions">optional</span>
	</label>


	<button type="submit" class="green">Speichern</button>
</form>

<div class="modal selectmodal" data-name="image-select" data-type="Image" data-objectsperpage="10">
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
							<figure>
								<img src="<?= $server->url ?>/<?= $server->dyn_img_path ?>/{{longid}}/original.{{extension}}">
								<figcaption>{{longid}}</figcaption>
							</figure>
							<input type="radio" name="result" value="{{id}}" {{current}}>
						</label>
					</article>
				</template>
			</section>
			<button type="button" data-action="close" class="red">Schließen</button>
			<button type="submit" data-action="submit" class="blue">Auswählen</button>
		</form>
	</div>
</div>

<div class="modal uploadmodal" data-name="image-upload" data-type="Image">
	<div class="box">
		<h2>Neues Bild hochladen</h2>
		<form action="#" method="GET">
			<label>Longid</label>
			<input type="text" name="longid">

			<label>Description</label>
			<input type="text" name="description">

			<label>Copyright</label>
			<input type="text" name="copyright">

			<label>File</label>
			<input type="file" name="imagedata">

			<button type="button" data-action="close" class="red">Schließen</button>
			<button type="submit" data-action="submit" class="green">Hochladen</button>
		</form>
	</div>
</div>

<div class="pseudoinput" data-type="Image" data-for="image_id" data-selectmodal="image-select" data-uploadmodal="image-upload">
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

<script src="<?= $server->url ?>/resources/js/admin/GetClass.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/DataObject.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/DataObjects/Image.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/Modal.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/Pagination.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/SelectModal.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/UploadModal.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/PseudoInput.js"></script>
<script src="<?= $server->url ?>/resources/js/admin/invoke.js"></script>
