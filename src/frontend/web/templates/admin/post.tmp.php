<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.comp.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.comp.php'; ?>
		<main>
			<?php if($Post->action == 'list'){ ?>
			<h1>Alle Posts</h1>
			<?php } else if($Post->action == 'show'){ ?>
			<h1>Post ansehen</h1>
			<?php } else if($Post->action == 'new'){ ?>
			<h1>Neuen Post schreiben</h1>
			<?php } else if($Post->action == 'edit'){ ?>
			<h1>Post bearbeiten</h1>
			<?php } else if($Post->action == 'delete'){ ?>
			<h1>Post löschen</h1>
			<?php } ?>

			<?php if(!$Post->action == 'list'){ ?>
			<a href="<?= $server->url ?>/admin/posts" class="button">&laquo; Zurück zu allen Posts</a>
			<?php } ?>

			<?php foreach($Post->errors as $error){ ?>
			<span class="message error">

			</span>
			<p>Details: <span class="code"></span></p>
			<?php } ?>

			<?php if($Post->action == 'list'){ ?>
				<?php foreach($Post->objects as $obj){ ?>
				<article class="post preview">
					<p class="longid"><?= $obj->longid ?></p>
					<p class="overline"><?= $obj->overline ?></p>
					<h3 class="headline"><?= $obj->headline ?></h3>
					<p class="subline"><?= $obj->subline ?></p>
					<p>
						<span class="author"><?= $obj->author ?></span> –
						<span class="timestamp"><?= date('d.m.Y, H:i \U\h\r', $obj->timestamp) ?></span>
					</p>
					<div>
						<a href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>" class="view">Ansehen</a>
						<a href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</div>
					<p class="teaser"><?= $obj->teaser ?></p>
				</article>
				<?php } ?>
			<?php } ?>

			<?php if($Post->action == 'show'){ ?>
				<?php $obj = $Post->object; ?>
				<article class="post">
					<p>
						<a href="<?= $server->url ?>/posts/<?= $obj->longid ?>">Blogansicht</a>
						<a href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</p>
					<p class="longid"><?= $obj->longid ?></p>
					<p class="overline"><?= $obj->overline ?></p>
					<h1 class="headline"><?= $obj->headline ?></h1>
					<p class="subline"><?= $obj->subline ?></p>
					<p class="teaser"><?= $obj->teaser ?></p>
					<p>
						Von <span class="author"><?= $obj->author ?></span> –
						<span class="timestamp"><?= $obj->timestamp ?></span>
					</p>

					<?php if($obj->image){ ?>
					<div>
						Bild: <span class="code"><?= $obj->image->longid ?></span>
						<a href="<?= $server->url ?>/admin/images/<?= $obj->image->id ?>">ansehen</a>
						<img src="<?= $server->url . Config::DYNAMIC_IMAGE_PATH . $obj->image->longid . '/original.'
							. $obj->image->extension ?>" alt="<?= $obj->image->description ?>">
					</div>
					<?php } ?>

					<p class="content"><?= $obj->content ?></p>
				</article>
			<?php } ?>

			<?php if($Post->action == 'edit' && !$Post->action->completed()){ ?>
				<?php $obj = $Post->object; ?>
				<form action="#" method="post">
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">

					<label for="overline">
						<span class="name">Dachzeile</span>
						<span class="requirements">optional, bis zu 64 Zeichen</span>
						<span class="description">
							Die Dachzeile steht direkt über der Überschrift und beinhaltet meist ein kurzes
							Stichwort, das das Thema des Artikels angibt.
						</span>
					</label>
					<input type="text" id="overline" class="overline" name="overline" value="<?= $obj->overline ?>">

					<label for="headline">
						<span class="name">Schlagzeile</span>
						<span class="requirements">erforderlich, 1 bis 256 Zeichen</span>
						<span class="description">
							Die Schlagzeile ist die Überschrift des Artikels und fasst die Kernaussage prägnant
							zusammen.
						</span>
					</label>
					<input type="text" id="headline" class="headline" name="headline" value="<?= $obj->headline ?>" required>

					<label for="subline">
						<span class="name">Unterzeile</span>
						<span class="requirements">optional, bis zu 256 Zeichen</span>
						<span class="description">
							Die Unterzeile steht unterhalb der Schlagzeile und ergänzt diese um weitere
							Informationen.
						</span>
					</label>
					<input type="text" id="subline" class="subline" name="subline" value="<?= $obj->subline ?>">

					<label for="teaser">
						<span class="name">Teaser</span>
						<span class="requirements">optional</span>
						<span class="description">
							Der Teaser wird nur in der Artikelvorschau angezeigt. Er fasst den Artikel kurz
							zusammen und soll zum Weiterlesen anregen.
						</span>
					</label>
					<textarea id="teaser" class="teaser" name="teaser"><?= $obj->teaser ?></textarea>

					<label for="author">
						<span class="name">Autor</span>
						<span class="requirements">erforderlich, 1 bis 128 Zeichen</span>
						<span class="description">Der Autor des Artikels.</span>
					</label>
					<input type="text" id="author" class="author" name="author" required value="<?= $obj->author ?>">

					<label>
						<span class="name">Veröffentlichungsdatum und -uhrzeit</span>
						<span class="requirements">erforderlich</span>
						<span class="description">
							Datum und Uhrzeit der Veröffentlichung. Hat derzeit nur eine informierende Funktion,
							Artikel mit Datum in der Zukunft werden trotzdem angezeigt. Es ist aber eine Funktion
							zur terminierten Veröffentlichung geplant.
						</span>
					</label>
					<div id="timeinput" data-value="<?= $obj->timestamp ?>" data-name="timestamp"></div>

					<label>
						<span class="name">Artikelbild</span>
						<span class="requirements">optional</span>
						<span class="description">
							Das Artikelbild wird prominent zwischen Überschrift und Inhalt sowie in der
							Artikelvorschau angezeigt.
						</span>
					</label>
					<div id="imageinput" data-value="<?= $obj->image->id ?? '' ?>" data-longid="<?= $obj->image->longid ?? '' ?>" data-name="image_id"
						data-extension="<?= $obj->image->extension ?? '' ?>"></div>

					<label for="content">
						<span class="name">Inhalt</span>
						<span class="requirements">
							optional, Markdown-Schreibweise möglich
							(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
						</span>
						<span class="description">Der eigentliche Inhalt des Artikels</span>
					</label>
					<textarea id="content" class="content" name="content" class="long-text"><?= $obj->content ?></textarea>

					<input type="submit" value="Speichern">
				</form>
			<?php } ?>

			<?php if($Post->action == 'new' && !$Post->action->completed()){ ?>
				<?php $obj = $Post->object; ?>
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

			<?php if($Post->action == 'delete' && !$Post->action->completed()){ ?>
				<?php $obj = $Post->object; ?>
				<p>
					<a href="<?= $server->url ?>/posts/<?= $obj->longid ?>">Blogansicht</a>
					<a href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
				</p>
				<p>Post <span class="code"><?= $obj->longid ?></span> löschen?</p>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<input type="submit" value="Löschen">
				</form>
			<?php } ?>

			<?php if($Post->action == 'new' || $Post->action == 'edit'){
				include COMPONENT_PATH . 'admin/imageinput.comp.php';
				include COMPONENT_PATH . 'admin/timeinput.comp.php';
			} ?>

		</main>
	</body>
</html>
