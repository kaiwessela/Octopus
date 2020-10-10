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

			<?php if($Post->action == 'list'){ ?>
				<a href="<?= $server->url ?>/admin/posts/new" class="button new green">Neuen Post schreiben</a>
			<?php } else { ?>
				<a href="<?= $server->url ?>/admin/posts" class="button back">Zurück zu allen Posts</a>
			<?php } ?>

			<?php foreach($Post->errors as $error){ ?>
			<div class="message red">
				<?= $error->getMessage(); ?>
			</div>
			<?php } ?>

			<?php if($Post->action != 'list' && $Post->action != 'new'){ ?>
			<div>

				<?php if($Post->action != 'show'){ ?>
				<a class="button blue" href="<?= $server->url ?>/admin/posts/<?= $Post->object->id ?>">Ansehen</a>
				<?php } ?>

				<a class="button blue" href="<?= $server->url ?>/<?= $Post->object->longid ?>">Vorschau</a>

				<?php if($Post->action != 'edit'){ ?>
				<a class="button yellow" href="<?= $server->url ?>/admin/posts/<?= $Post->object->id ?>/edit">Bearbeiten</a>
				<?php } ?>

				<?php if($Post->action != 'delete'){ ?>
				<a class="button red" href="<?= $server->url ?>/admin/posts/<?= $Post->object->id ?>/delete">Löschen</a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if(($Post->action == 'new' || $Post->action == 'edit') && $Post->action->completed()){ ?>
			<div class="message green">
				Post <code><?= $Post->object->longid ?></code> wurde erfolgreich gespeichert.
			</div>
			<?php } ?>

			<?php if($Post->action == 'list'){ ?>
				<?php
				$pagination = $Post->pagination;
				include COMPONENT_PATH . 'admin/pagination.comp.php';
				?>

				<?php if(empty($Post->objects)){ ?>
				<div class="message yellow">
					Es sind noch keine Posts vorhanden.
				</div>

				<?php } else { foreach($Post->objects as $obj){ ?>
				<article>
					<code><?= $obj->longid ?></code>
					<h2><?= $obj->headline ?></h2>
					<strong><?= $obj->subline ?></strong>
					<small>
						Von <?= $obj->author ?> –
						<time datetime="<?= $timeformat::html_time($obj->timestamp) ?>">
							<?= $timeformat::date_and_time($obj->timestamp) ?>
						</time>
					</small>
					<div>
						<a class="button blue"
							href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>">Ansehen</a>
						<a class="button yellow"
							href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit">Bearbeiten</a>
						<a class="button red"
							href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/delete">Löschen</a>
					</div>
				</article>
				<?php }} ?>
			<?php } ?>

			<?php if($Post->action == 'show'){ ?>
				<?php $obj = $Post->object; ?>
				<article>
					<code><?= $obj->longid ?></code>
					<b><?= $obj->overline ?></b>
					<h1><?= $obj->headline ?></h1>
					<strong><?= $obj->subline ?></strong>
					<p><?= $obj->teaser ?></p>
					<small>
						Von <?= $obj->author ?> –
						<time datetime="<?= $timeformat::html_time($obj->timestamp) ?>">
							<?= $timeformat::date_and_time($obj->timestamp) ?>
						</time>
					</small>

					<?php if($obj->image){ ?>
					<div>
						Bild: <code><?= $obj->image->longid ?></code>
						<a href="<?= $server->url ?>/admin/images/<?= $obj->image->id ?>">ansehen</a>
						<img src="<?= $server->url . $server->dyn_img_path . $obj->image->longid . '/original.'
							. $obj->image->extension ?>" alt="<?= $obj->image->description ?>">
					</div>
					<?php } ?>

					<p><?= $obj->content ?></p>
				</article>
			<?php } ?>

			<?php if($Post->action == 'edit' && !$Post->action->completed()){ ?>
				<?php $obj = $Post->object; ?>
				<form action="#" method="post">
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">

					<label for="overline">
						<span class="name">Dachzeile</span>
						<span class="conditions">optional, bis zu 25 Zeichen</span>
						<span class="infos">
							Die Dachzeile steht direkt über der Überschrift und beinhaltet meist ein kurzes
							Stichwort, das das Thema des Artikels angibt.
						</span>
					</label>
					<input type="text" id="overline" name="overline" value="<?= $obj->overline ?>" size="20" maxlength="25">

					<label for="headline">
						<span class="name">Schlagzeile</span>
						<span class="conditions">erforderlich, 1 bis 60 Zeichen</span>
						<span class="infos">
							Die Schlagzeile ist die Überschrift des Artikels und fasst die Kernaussage prägnant
							zusammen.
						</span>
					</label>
					<input type="text" id="headline" name="headline" value="<?= $obj->headline ?>" size="40" required maxlength="60">

					<label for="subline">
						<span class="name">Unterzeile</span>
						<span class="conditions">optional, bis zu 40 Zeichen</span>
						<span class="infos">
							Die Unterzeile steht unterhalb der Schlagzeile und ergänzt diese um weitere
							Informationen.
						</span>
					</label>
					<input type="text" id="subline" name="subline" value="<?= $obj->subline ?>" size="30" maxlength="40">

					<label for="teaser">
						<span class="name">Teaser</span>
						<span class="conditions">optional</span>
						<span class="infos">
							Der Teaser wird nur in der Artikelvorschau angezeigt. Er fasst den Artikel kurz
							zusammen und soll zum Weiterlesen anregen.
						</span>
					</label>
					<textarea id="teaser" name="teaser" cols="50" rows="3"><?= $obj->teaser ?></textarea>

					<label for="author">
						<span class="name">Autor</span>
						<span class="conditions">erforderlich, 1 bis 128 Zeichen</span>
						<span class="infos">Der Autor des Artikels.</span>
					</label>
					<input type="text" id="author" name="author" required size="30" maxlength="50" value="<?= $obj->author ?>">

					<label for="timestamp">
						<span class="name">Veröffentlichungsdatum und -uhrzeit</span>
						<span class="conditions">erforderlich</span>
						<span class="infos">
							Datum und Uhrzeit der Veröffentlichung. Hat derzeit nur eine informierende Funktion,
							Artikel mit Datum in der Zukunft werden trotzdem angezeigt. Es ist aber eine Funktion
							zur terminierten Veröffentlichung geplant.
						</span>
					</label>
					<input type="number" class="timeinput" id="timestamp" name="timestamp" required size="10" value="<?= $obj->timestamp ?>">

					<label for="image_id">
						<span class="name">Artikelbild</span>
						<span class="conditions">optional</span>
						<span class="infos">
							Das Artikelbild wird prominent zwischen Überschrift und Inhalt sowie in der
							Artikelvorschau angezeigt.
						</span>
					</label>
					<input type="text" class="imageinput" id="image_id" name="image_id" size="8" minlength="8" maxlength="8" value="<?= $obj->image->id ?>">

					<label for="content">
						<span class="name">Inhalt</span>
						<span class="conditions">
							optional, Markdown-Schreibweise möglich
							(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
						</span>
						<span class="infos">Der eigentliche Inhalt des Artikels</span>
					</label>
					<textarea id="content" name="content" cols="80" rows="20"><?= $obj->content ?></textarea>

					<button type="submit" class="green">Speichern</button>
				</form>
			<?php } ?>

			<?php if($Post->action == 'new' && !$Post->action->completed()){ ?>
				<?php $obj = $Post->object; ?>
				<form action="#" method="post">
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
					<input type="text" id="longid" name="longid" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]$" autocomplete="off">

					<label for="overline">
						<span class="name">Dachzeile</span>
						<span class="conditions">optional, bis zu 25 Zeichen</span>
						<span class="infos">
							Die Dachzeile steht direkt über der Überschrift und beinhaltet meist ein kurzes
							Stichwort, das das Thema des Artikels angibt.
						</span>
					</label>
					<input type="text" id="overline" name="overline" size="20" maxlength="25">

					<label for="headline">
						<span class="name">Schlagzeile</span>
						<span class="conditions">erforderlich, 1 bis 60 Zeichen</span>
						<span class="infos">
							Die Schlagzeile ist die Überschrift des Artikels und fasst die Kernaussage prägnant
							zusammen.
						</span>
					</label>
					<input type="text" id="headline" name="headline" required size="40" maxlength="60">

					<label for="subline">
						<span class="name">Unterzeile</span>
						<span class="conditions">optional, bis zu 40 Zeichen</span>
						<span class="infos">
							Die Unterzeile steht unterhalb der Schlagzeile und ergänzt diese um weitere
							Informationen.
						</span>
					</label>
					<input type="text" id="subline" name="subline" size="30" maxlength="40">

					<label for="teaser">
						<span class="name">Teaser</span>
						<span class="conditions">optional</span>
						<span class="infos">
							Der Teaser wird nur in der Artikelvorschau angezeigt. Er fasst den Artikel kurz
							zusammen und soll zum Weiterlesen anregen.
						</span>
					</label>
					<textarea id="teaser" name="teaser" cols="50" rows="3"></textarea>

					<label for="author">
						<span class="name">Autor</span>
						<span class="requirements">erforderlich, 1 bis 50 Zeichen</span>
						<span class="infos">Der Autor des Artikels.</span>
					</label>
					<input type="text" id="author" name="author" required size="30" maxlength="50">

					<label for="timestamp">
						<span class="name">Veröffentlichungsdatum und -uhrzeit</span>
						<span class="conditions">erforderlich</span>
						<span class="infos">
							Datum und Uhrzeit der Veröffentlichung. Hat derzeit nur eine informierende Funktion,
							Artikel mit Datum in der Zukunft werden trotzdem angezeigt. Es ist aber eine Funktion
							zur terminierten Veröffentlichung geplant.
						</span>
					</label>
					<input type="number" class="timeinput" id="timestamp" name="timestamp" required size="10">

					<label for="image_id">
						<span class="name">Artikelbild</span>
						<span class="conditions">optional</span>
						<span class="infos">
							Das Artikelbild wird prominent zwischen Überschrift und Inhalt sowie in der
							Artikelvorschau angezeigt.
						</span>
					</label>
					<input type="text" class="imageinput" id="image_id" name="image_id" size="8" minlength="8" maxlength="8">

					<label for="content">
						<span class="name">Inhalt</span>
						<span class="conditions">
							optional, Markdown-Schreibweise möglich
							(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
						</span>
						<span class="infos">Der eigentliche Inhalt des Artikels</span>
					</label>
					<textarea id="content" name="content" cols="80" rows="20"></textarea>

					<button type="submit" class="green">Speichern</button>
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
					<button type="submit" class="red">Löschen</button>
				</form>
			<?php } ?>

			<?php if($Post->action == 'new' || $Post->action == 'edit'){
				include COMPONENT_PATH . 'admin/imageinput.comp.php';
				include COMPONENT_PATH . 'admin/timeinput.comp.php';
			} ?>

			<script src="<?= $server->url ?>/resources/js/admin/validate.js"></script>
		</main>
	</body>
</html>
