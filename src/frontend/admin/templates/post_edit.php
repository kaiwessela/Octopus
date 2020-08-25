<?php
use \Blog\Config\Config;
?>

<h1>Post bearbeiten</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Post nicht gefunden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_err_invalid){ ?>
<span class="message error">
	Fehler beim Versuch, den Post zu bearbeiten.
</span>
<p>Details: <span class="code"><?= $controller->err_invalid_msg ?></span></p>
<?php } ?>

<?php if($controller->show_success){ ?>
<span class="message success">
	Post erfolgreich geändert.
</span>
<?php } ?>

<?php if($controller->show_form){ ?>
<?php $post = $controller->post; ?>

<p>Post-ID: <span class="code"><?= $post->longid ?></span></p>

<p>
	<a href="<?= Config::SERVER_URL ?>/posts/<?= $post->longid ?>">Blogansicht</a>
	<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>/delete" class="delete">Löschen</a>
</p>

<form action="#" method="post">
	<input type="hidden" name="id" value="<?= $post->id ?>">
	<input type="hidden" name="longid" value="<?= $post->longid ?>">

	<label for="overline">
		<span class="name">Dachzeile</span>
		<span class="requirements">optional, bis zu 64 Zeichen</span>
		<span class="description">
			Die Dachzeile steht direkt über der Überschrift und beinhaltet meist ein kurzes
			Stichwort, das das Thema des Artikels angibt.
		</span>
	</label>
	<input type="text" id="overline" class="overline" name="overline" value="<?= $post->overline ?>">

	<label for="headline">
		<span class="name">Schlagzeile</span>
		<span class="requirements">erforderlich, 1 bis 256 Zeichen</span>
		<span class="description">
			Die Schlagzeile ist die Überschrift des Artikels und fasst die Kernaussage prägnant
			zusammen.
		</span>
	</label>
	<input type="text" id="headline" class="headline" name="headline" value="<?= $post->headline ?>" required>

	<label for="subline">
		<span class="name">Unterzeile</span>
		<span class="requirements">optional, bis zu 256 Zeichen</span>
		<span class="description">
			Die Unterzeile steht unterhalb der Schlagzeile und ergänzt diese um weitere
			Informationen.
		</span>
	</label>
	<input type="text" id="subline" class="subline" name="subline" value="<?= $post->subline ?>">

	<label for="teaser">
		<span class="name">Teaser</span>
		<span class="requirements">optional</span>
		<span class="description">
			Der Teaser wird nur in der Artikelvorschau angezeigt. Er fasst den Artikel kurz
			zusammen und soll zum Weiterlesen anregen.
		</span>
	</label>
	<textarea id="teaser" class="teaser" name="teaser"><?= $post->teaser ?></textarea>

	<label for="author">
		<span class="name">Autor</span>
		<span class="requirements">erforderlich, 1 bis 128 Zeichen</span>
		<span class="description">Der Autor des Artikels.</span>
	</label>
	<input type="text" id="author" class="author" name="author" required value="<?= $post->author ?>">

	<label>
		<span class="name">Veröffentlichungsdatum und -uhrzeit</span>
		<span class="requirements">erforderlich</span>
		<span class="description">
			Datum und Uhrzeit der Veröffentlichung. Hat derzeit nur eine informierende Funktion,
			Artikel mit Datum in der Zukunft werden trotzdem angezeigt. Es ist aber eine Funktion
			zur terminierten Veröffentlichung geplant.
		</span>
	</label>
	<div id="timeinput" data-value="<?= $post->timestamp ?>" data-name="timestamp"></div>

	<label>
		<span class="name">Artikelbild</span>
		<span class="requirements">optional</span>
		<span class="description">
			Das Artikelbild wird prominent zwischen Überschrift und Inhalt sowie in der
			Artikelvorschau angezeigt.
		</span>
	</label>
	<div id="imageinput" data-value="<?= $post->image->id ?? '' ?>" data-longid="<?= $post->image->longid ?? '' ?>" data-name="image_id"
		data-extension="<?= $post->image->extension ?>"></div>

	<label for="content">
		<span class="name">Inhalt</span>
		<span class="requirements">
			optional, Markdown-Schreibweise möglich
			(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
		</span>
		<span class="description">Der eigentliche Inhalt des Artikels</span>
	</label>
	<textarea id="content" class="content" name="content" class="long-text"><?= $post->content ?></textarea>

	<input type="submit" value="Speichern">
</form>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/posts">Zurück zu allen Posts</a>

<?php include __DIR__ . '/components/imageinput.comp.php'; ?>
<?php include __DIR__ . '/components/timeinput.comp.php'; ?>
