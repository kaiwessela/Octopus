<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.php'; ?>
		<main>
			<?php if($PostController->request->action == 'list'){ ?>
			<h1>Alle Posts</h1>
			<?php } else if($PostController->request->action == 'show'){ ?>
			<h1>Post ansehen</h1>
			<?php } else if($PostController->request->action == 'new'){ ?>
			<h1>Neuen Post schreiben</h1>
			<?php } else if($PostController->request->action == 'edit'){ ?>
			<h1>Post bearbeiten</h1>
			<?php } else if($PostController->request->action == 'delete'){ ?>
			<h1>Post löschen</h1>
			<?php } ?>

			<?php if($PostController->request->action == 'list'){ ?>
				<a href="<?= $server->url ?>/admin/posts/new" class="button new green">Neuen Post schreiben</a>
			<?php } else { ?>
				<a href="<?= $server->url ?>/admin/posts" class="button back">Zurück zu allen Posts</a>
			<?php } ?>

			<?php if($PostController->created()){ ?>
				<div class="message green">
					Post <code><?= $Post->longid ?></code> wurde erfolgreich hinzugefügt.
				</div>
			<?php } else if($PostController->edited()){ ?>
				<div class="message green">
					Post <code><?= $Post->longid ?></code> wurde erfolgreich bearbeitet.
				</div>
			<?php } else if($PostController->deleted()){ ?>
				<div class="message green">
					Post <code><?= $Post->longid ?></code> wurde erfolgreich gelöscht.
				</div>
			<?php } else if($PostController->empty() && $PostController->request->action == 'list'){ ?>
				<div class="message yellow">
					Es sind noch keine Posts vorhanden.
				</div>
			<?php } else if($PostController->unprocessable()){ ?>
				<div class="message red">
					Die hochgeladenen Daten sind fehlerhaft.
				</div>
				<ul>
				<?php foreach($PostController->errors['import'] as $error){ ?>
					<li><code><?= $error['field'] ?></code>: <?= $error['type'] ?></li>
				<?php } ?>
				</ul>
			<?php } else if($PostController->internal_error()){ ?>
				<div class="message red">
					Es ist ein interner Serverfehler aufgetreten.
				</div>
			<?php } ?>

			<?php if($PostController->request->action != 'list' && $PostController->request->action != 'new'){ ?>
			<div>
				<?php if($PostController->request->action != 'show'){ ?>
				<a class="button blue" href="<?= $server->url ?>/admin/posts/<?= $Post->id ?>">Ansehen</a>
				<?php } ?>

				<a class="button blue" href="<?= $server->url ?>/artikel/<?= $Post->longid ?>">Vorschau</a>

				<?php if($PostController->request->action != 'edit'){ ?>
				<a class="button yellow" href="<?= $server->url ?>/admin/posts/<?= $Post->id ?>/edit">Bearbeiten</a>
				<?php } ?>

				<?php if($PostController->request->action != 'delete'){ ?>
				<a class="button red" href="<?= $server->url ?>/admin/posts/<?= $Post->id ?>/delete">Löschen</a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if($PostController->request->action == 'list' && $PostController->found()){ ?>
				<?php
				$pagination = $PostController->pagination;
				include COMPONENT_PATH . 'admin/pagination.php';
				?>

				<?php foreach($Post as $obj){ ?>
				<article>
					<code><?= $obj->longid ?></code>
					<h2><?= $obj->headline ?></h2>
					<strong><?= $obj->subline ?></strong>
					<small>
						Von <?= $obj->author ?> –
						<time datetime="<?= $obj->timestamp->iso ?>">
							<?= $obj->timestamp->datetime ?>
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
				<?php } ?>
			<?php } ?>

			<?php if($PostController->request->action == 'show' && $PostController->found()){ ?>
				<?php $obj = $Post; ?>
				<article>
					<code><?= $obj->longid ?></code>
					<b><?= $obj->overline ?></b>
					<h1><?= $obj->headline ?></h1>
					<strong><?= $obj->subline ?></strong>
					<p><?= $obj->teaser ?></p>
					<small>
						Von <?= $obj->author ?> –
						<time datetime="<?= $obj->timestamp->iso ?>">
							<?= $obj->timestamp->datetime ?>
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

			<?php if(($PostController->request->action == 'edit' && !$PostController->edited()) || ($PostController->request->action == 'new' && !$PostController->created())){ ?>
				<?php $obj = $Post; ?>
				<form action="#" method="post">

					<?php if($PostController->request->action == 'new'){ ?>
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
					<input type="text" id="longid" name="longid" value="<?= $obj->longid ?>" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" autocomplete="off">
					<?php } else { ?>
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">
					<?php } ?>

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
						<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
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

					<label>
						<span class="name">Rubriken</span>
						<span class="conditions">optional</span>
						<span class="infos">
							<strong>Beta-Test:</strong><br>
							„Beibehalten“ bedeutet keine Änderung, das Backend ignoriert diese Relation dann einfach.<br>
							„Hinzufügen“ fügt die Relation neu hinzu. Logischerweise deaktiviert für bereits bestehende Relationen.<br>
							„Bearbeiten“ ist hier deaktiviert und wird später für kompliziertere Relationen mit Zusatzfeldern verwendet.<br>
							„Löschen“ löscht eine Relation. Deaktiviert für neue Relationen, da diese in der Datenbank noch nicht existieren.</br>
						</span>
					</label>
					<div class="pseudoinput relationlist">
						<div class="objectbox" data-count="<?= count($obj->columns) + 1 ?>">
						<?php foreach($obj->columns as $i => $column){ ?>
							<?php /* FIXME */ if(empty($column->id)){ continue; } ?>
							<div class="listitem">
								<p><strong><?= $column->name ?></strong><code><?= $column->longid ?></code></p>
								<input type="hidden" name="columns[<?= $i ?>][id]" value="<?= $obj->relations[$i]['id'] ?>">
								<input type="hidden" name="columns[<?= $i ?>][column_id]" value="<?= $column->id ?>">
								<input type="hidden" name="columns[<?= $i ?>][post_id]" value="<?= $obj->id ?>">

								<label class="radiobodge turn-around blue">
									<span class="label-field">Beibehalten</span>
									<input type="radio" name="columns[<?= $i ?>][action]" value="nothing" checked>
									<span class="bodgeradio">
										<span class="bodgetick"></span>
									</span>
								</label>

								<label class="radiobodge turn-around green">
									<span class="label-field">Hinzufügen</span>
									<input type="radio" name="columns[<?= $i ?>][action]" value="new" disabled>
									<span class="bodgeradio">
										<span class="bodgetick"></span>
									</span>
								</label>

								<label class="radiobodge turn-around yellow">
									<span class="label-field">Bearbeiten</span>
									<input type="radio" name="columns[<?= $i ?>][action]" value="edit" disabled>
									<span class="bodgeradio">
										<span class="bodgetick"></span>
									</span>
								</label>

								<label class="radiobodge turn-around red">
									<span class="label-field">Entfernen</span>
									<input type="radio" name="columns[<?= $i ?>][action]" value="delete">
									<span class="bodgeradio">
										<span class="bodgetick"></span>
									</span>
								</label>
							</div>
						<?php } ?>
						</div>
						<template>
							<div class="listitem">
								<p><strong>{{name}}</strong><code>{{longid}}</code></p>
								<input type="hidden" name="columns[{{i}}][column_id]" value="{{id}}">
								<input type="hidden" name="columns[{{i}}][post_id]" value="<?= $obj->id ?>">

								<label class="radiobodge turn-around blue">
									<span class="label-field">Beibehalten</span>
									<input type="radio" name="columns[{{i}}][action]" value="nothing">
									<span class="bodgeradio">
										<span class="bodgetick"></span>
									</span>
								</label>

								<label class="radiobodge turn-around green">
									<span class="label-field">Hinzufügen</span>
									<input type="radio" name="columns[{{i}}][action]" value="new" checked>
									<span class="bodgeradio">
										<span class="bodgetick"></span>
									</span>
								</label>

								<label class="radiobodge turn-around yellow">
									<span class="label-field">Bearbeiten</span>
									<input type="radio" name="columns[{{i}}][action]" value="edit" disabled>
									<span class="bodgeradio">
										<span class="bodgetick"></span>
									</span>
								</label>

								<label class="radiobodge turn-around red">
									<span class="label-field">Entfernen</span>
									<input type="radio" name="columns[{{i}}][action]" value="delete" disabled>
									<span class="bodgeradio">
										<span class="bodgetick"></span>
									</span>
								</label>
							</div>
						</template>
						<button type="button" class="new green" data-action="open" data-modal="addcolumn">Rubrik hinzufügen</button>
					</div>

					<button type="submit" class="green">Speichern</button>
				</form>
			<?php } ?>

			<?php if($PostController->request->action == 'delete' && !$PostController->deleted()){ ?>
				<?php $obj = $Post; ?>
				<p>Post <code><?= $obj->longid ?></code> löschen?</p>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<button type="submit" class="red">Löschen</button>
				</form>
			<?php } ?>

			<div class="modal selectmodal" data-name="addcolumn">
				<div class="box">
					<h2>Rubrik auswählen</h2>
					<form action="#" method="GET">
						<div class="objectbox"></div>
						<template>
							<label>
								<input type="radio" name="result" value="{{id}}">
								<h3>{{name}}</h3>
								<code>{{longid}}</code>
							</label>
						</template>
						<button type="button" data-action="close">Schließen</button>
						<button type="submit" data-action="submit">Auswählen</button>
					</form>
				</div>
			</div>

		</main>
		<?php include COMPONENT_PATH . 'admin/footer.php'; ?>

		<?php if($PostController->request->action == 'new' || $PostController->request->action == 'edit'){
			include COMPONENT_PATH . 'admin/imageinput.php';
			include COMPONENT_PATH . 'admin/timeinput.php';
		} ?>
		<script src="<?= $server->url ?>/resources/js/admin/validate.js"></script>

		<script src="<?= $server->url ?>/resources/js/newadmin/column.js"></script>
		<script src="<?= $server->url ?>/resources/js/newadmin/modal.js"></script>
		<script src="<?= $server->url ?>/resources/js/newadmin/selectmodal.js"></script>
		<script src="<?= $server->url ?>/resources/js/newadmin/invoke.js"></script>
		<script>
			modals['addcolumn'].type = 'column';
			modals['addcolumn'].onSubmit = () => {
				var count = Number(document.querySelector('.relationlist .objectbox').getAttribute('data-count'));
				count++;
				document.querySelector('.relationlist .objectbox').setAttribute('data-count', count);

				var newcontent = modals['addcolumn'].valueObject.replace(document.querySelector('.relationlist template').innerHTML);
				newcontent = newcontent.replace(/{{i}}/g, count);
				document.querySelector('.relationlist .objectbox').innerHTML += newcontent;
			}
		</script>
	</body>
</html>
