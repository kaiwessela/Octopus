<?php
use \Blog\Config\Config;
?>

<h1>Neuen Post schreiben</h1>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, den neuen Post zu speichern.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Post erfolgreich gespeichert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<form action="#" method="post">
	<label for="longid">
		<span class="name">Post-ID</span>
		<span class="requirements">
			erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="description">
			Die Post-ID wird in der URL verwendet und entspricht oftmals ungefähr der Überschrift.
		</span>
	</label>
	<input type="text" id="longid" class="longid" name="longid" required>

	<label for="overline">
		<span class="name">Dachzeile</span>
		<span class="requirements">optional, bis zu 64 Zeichen</span>
		<span class="description">
			Die Dachzeile steht direkt über der Überschrift und beinhaltet meist ein kurzes
			Stichwort, das das Thema des Artikels angibt.
		</span>
	</label>
	<input type="text" id="overline" class="overline" name="overline">

	<label for="headline">
		<span class="name">Schlagzeile</span>
		<span class="requirements">erforderlich, 1 bis 256 Zeichen</span>
		<span class="description">
			Die Schlagzeile ist die Überschrift des Artikels und fasst die Kernaussage prägnant
			zusammen.
		</span>
	</label>
	<input type="text" id="headline" class="headline" name="headline" required>

	<label for="subline">
		<span class="name">Unterzeile</span>
		<span class="requirements">optional, bis zu 256 Zeichen</span>
		<span class="description">
			Die Unterzeile steht unterhalb der Schlagzeile und ergänzt diese um weitere
			Informationen.
		</span>
	</label>
	<input type="text" id="subline" class="subline" name="subline">

	<label for="teaser">
		<span class="name">Teaser</span>
		<span class="requirements">optional</span>
		<span class="description">
			Der Teaser wird nur in der Artikelvorschau angezeigt. Er fasst den Artikel kurz
			zusammen und soll zum Weiterlesen anregen.
		</span>
	</label>
	<textarea id="teaser" class="teaser" name="teaser"></textarea>

	<label for="author">
		<span class="name">Autor</span>
		<span class="requirements">erforderlich, 1 bis 128 Zeichen</span>
		<span class="description">Der Autor des Artikels.</span>
	</label>
	<input type="text" id="author" class="author" name="author" required>

	<label>
		<span class="name">Veröffentlichungsdatum und -uhrzeit</span>
		<span class="requirements">erforderlich</span>
		<span class="description">
			Datum und Uhrzeit der Veröffentlichung. Hat derzeit nur eine informierende Funktion,
			Artikel mit Datum in der Zukunft werden trotzdem angezeigt. Es ist aber eine Funktion
			zur terminierten Veröffentlichung geplant.
		</span>
	</label>
	<div id="timeinput" data-value="" data-name="timestamp"></div>

	<label>
		<span class="name">Artikelbild</span>
		<span class="requirements">optional</span>
		<span class="description">
			Das Artikelbild wird prominent zwischen Überschrift und Inhalt sowie in der
			Artikelvorschau angezeigt.
		</span>
	</label>
	<div id="imageinput" data-value="" data-longid="" data-name="image_id"></div>

	<label for="content">
		<span class="name">Inhalt</span>
		<span class="requirements">
			optional, Markdown-Schreibweise möglich
			(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
		</span>
		<span class="description">Der eigentliche Inhalt des Artikels</span>
	</label>
	<textarea id="content" class="content" name="content"></textarea>

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/posts">Zurück zu allen Posts</a>

<?php include __DIR__ . '/components/imageinput.comp.php'; ?>
<?php include __DIR__ . '/components/timeinput.comp.php'; ?>
