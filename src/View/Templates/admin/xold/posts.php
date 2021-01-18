<?php include COMPONENT_PATH . 'admin/start.php'; ?>
<main>
	<?php
	$Controller = $PostController;
	$Object = $Post;
	$singular = 'Post';
	$plural = 'Posts';
	$urlclass = 'posts';

	include COMPONENT_PATH . 'admin/common-1.php';
	?>

	<?php if($Controller->request->action == 'list' && $Controller->found()){ ?>
		<?php
		$pagination = $Controller->pagination;
		include COMPONENT_PATH . 'admin/pagination.php';
		?>

		<?php foreach($Object as $obj){ ?>
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

	<?php if($Controller->request->action == 'show' && $Controller->found()){ ?>
		<article>
			<code><?= $Object->longid ?></code>
			<b><?= $Object->overline ?></b>
			<h1><?= $Object->headline ?></h1>
			<strong><?= $Object->subline ?></strong>
			<p><?= $Object->teaser ?></p>
			<small>
				Von <?= $Object->author ?> –
				<time datetime="<?= $Object->timestamp->iso ?>">
					<?= $Object->timestamp->datetime ?>
				</time>
			</small>

			<?php if($Object->image){ ?>
			<div>
				Bild: <code><?= $Object->image->longid ?></code>
				<a href="<?= $server->url ?>/admin/images/<?= $Object->image->id ?>">ansehen</a>
				<img src="<?= $server->url . $server->dyn_img_path . $Object->image->longid . '/original.'
					. $Object->image->extension ?>" alt="<?= $Object->image->description ?>">
			</div>
			<?php } ?>

			<p><?= $Object->content ?></p>
		</article>
	<?php } ?>

	<?php if(($Controller->request->action == 'edit' && !$Controller->edited()) || ($Controller->request->action == 'new' && !$Controller->created())){ ?>
		<form action="#" method="post">

			<?php if($Controller->request->action == 'new'){ ?>
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
			<input type="text" id="longid" name="longid" value="<?= $Post->longid ?>" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" autocomplete="off">
			<?php } else { ?>
			<input type="hidden" name="id" value="<?= $Post->id ?>">
			<input type="hidden" name="longid" value="<?= $Post->longid ?>">
			<?php } ?>

			<label for="overline">
				<span class="name">Dachzeile</span>
				<span class="conditions">optional, bis zu 25 Zeichen</span>
				<span class="infos">
					Die Dachzeile steht direkt über der Überschrift und beinhaltet meist ein kurzes
					Stichwort, das das Thema des Artikels angibt.
				</span>
			</label>
			<input type="text" id="overline" name="overline" value="<?= $Post->overline ?>" size="20" maxlength="25">

			<label for="headline">
				<span class="name">Schlagzeile</span>
				<span class="conditions">erforderlich, 1 bis 60 Zeichen</span>
				<span class="infos">
					Die Schlagzeile ist die Überschrift des Artikels und fasst die Kernaussage prägnant
					zusammen.
				</span>
			</label>
			<input type="text" id="headline" name="headline" value="<?= $Post->headline ?>" size="40" required maxlength="60">

			<label for="subline">
				<span class="name">Unterzeile</span>
				<span class="conditions">optional, bis zu 40 Zeichen</span>
				<span class="infos">
					Die Unterzeile steht unterhalb der Schlagzeile und ergänzt diese um weitere
					Informationen.
				</span>
			</label>
			<input type="text" id="subline" name="subline" value="<?= $Post->subline ?>" size="30" maxlength="40">

			<label for="teaser">
				<span class="name">Teaser</span>
				<span class="conditions">optional</span>
				<span class="infos">
					Der Teaser wird nur in der Artikelvorschau angezeigt. Er fasst den Artikel kurz
					zusammen und soll zum Weiterlesen anregen.
				</span>
			</label>
			<textarea id="teaser" name="teaser" cols="50" rows="3"><?= $Post->teaser ?></textarea>

			<label for="author">
				<span class="name">Autor</span>
				<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
				<span class="infos">Der Autor des Artikels.</span>
			</label>
			<input type="text" id="author" name="author" required size="30" maxlength="50" value="<?= $Post->author ?>">

			<label for="timestamp">
				<span class="name">Veröffentlichungsdatum und -uhrzeit</span>
				<span class="conditions">erforderlich</span>
				<span class="infos">
					Datum und Uhrzeit der Veröffentlichung. Hat derzeit nur eine informierende Funktion,
					Artikel mit Datum in der Zukunft werden trotzdem angezeigt. Es ist aber eine Funktion
					zur terminierten Veröffentlichung geplant.
				</span>
			</label>
			<input type="number" class="timeinput" id="timestamp" name="timestamp" required size="10" value="<?= $Post->timestamp ?>">

			<label for="image_id">
				<span class="name">Artikelbild</span>
				<span class="conditions">optional</span>
				<span class="infos">
					Das Artikelbild wird prominent zwischen Überschrift und Inhalt sowie in der
					Artikelvorschau angezeigt.
				</span>
			</label>
			<input type="text" class="imageinput" id="image_id" name="image_id" size="8" minlength="8" maxlength="8" value="<?= $Post->image->id ?>">

			<div class="pseudoinput">
				<div class="objectbox">
					<?php if(!empty($Post->image)){ ?>
					<img src="<?= $server->url ?>.<?= $server->dyn_img_path ?>/<?= $Post->image->longid ?>/original.<?= $Post->image->extension ?>" alt="[ANZEIGEFEHLER]">
					<?php } else { ?>
					<div>Kein Bild ausgewählt.</div>
					<?php } ?>
				</div>
				<template>
					<img src="<?= $server->url ?>.<?= $server->dyn_img_path ?>"
				</template>
			</div>

			<label for="content">
				<span class="name">Inhalt</span>
				<span class="conditions">
					optional, Markdown-Schreibweise möglich
					(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
				</span>
				<span class="infos">Der eigentliche Inhalt des Artikels</span>
			</label>
			<textarea id="content" name="content" cols="80" rows="20"><?= $Post->content ?></textarea>

			<label>
				<span class="name">Rubriken</span>
				<span class="conditions">optional</span>
			</label>
			<div class="pseudoinput relationlist">
				<div class="objectbox" data-count="<?= count($Post->columns) + 1 ?>">
				<?php foreach($Post->columns as $i => $column){ ?>
					<div class="listitem">
						<p><strong><?= $column->name ?></strong> <code><?= $column->longid ?></code></p>
						<input type="hidden" name="columns[<?= $i ?>][id]" value="<?= $Post->relations[$i]['id'] ?>">
						<input type="hidden" name="columns[<?= $i ?>][column_id]" value="<?= $column->id ?>">
						<input type="hidden" name="columns[<?= $i ?>][post_id]" value="<?= $Post->id ?>">

						<label class="radiobodge turn-around blue">
							<span class="label-field">Keine Änderung</span>
							<input type="radio" name="columns[<?= $i ?>][action]" value="ignore" checked>
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>

						<label class="radiobodge turn-around green">
							<span class="label-field">Hinzufügen</span>
							<input type="radio" name="columns[<?= $i ?>][action]" value="new" disabled>
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>

						<label class="radiobodge turn-around yellow">
							<span class="label-field">Bearbeiten</span>
							<input type="radio" name="columns[<?= $i ?>][action]" value="edit" disabled>
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>

						<label class="radiobodge turn-around red">
							<span class="label-field">Entfernen</span>
							<input type="radio" name="columns[<?= $i ?>][action]" value="delete">
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>
					</div>
				<?php } ?>
				</div>
				<template>
					<div class="listitem">
						<p><strong>{{name}}</strong> <code>{{longid}}</code></p>
						<input type="hidden" name="columns[{{i}}][column_id]" value="{{id}}">
						<input type="hidden" name="columns[{{i}}][post_id]" value="<?= $Post->id ?>">

						<label class="radiobodge turn-around blue">
							<span class="label-field">Keine Änderung</span>
							<input type="radio" name="columns[{{i}}][action]" value="ignore">
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>

						<label class="radiobodge turn-around green">
							<span class="label-field">Hinzufügen</span>
							<input type="radio" name="columns[{{i}}][action]" value="new" checked>
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>

						<label class="radiobodge turn-around yellow">
							<span class="label-field">Bearbeiten</span>
							<input type="radio" name="columns[{{i}}][action]" value="edit" disabled>
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>

						<label class="radiobodge turn-around red">
							<span class="label-field">Entfernen</span>
							<input type="radio" name="columns[{{i}}][action]" value="delete" disabled>
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>
					</div>
				</template>
				<button type="button" class="new blue" data-action="open" data-modal="addcolumn">Rubrik hinzufügen</button>
			</div>

			<button type="submit" class="green">Speichern</button>
		</form>
	<?php } ?>

	<?php if($Controller->request->action == 'delete' && !$Controller->deleted()){ ?>
		<p>Post <code><?= $Post->longid ?></code> löschen?</p>
		<form action="#" method="post">
			<input type="hidden" id="id" name="id" value="<?= $Post->id ?>">
			<button type="submit" class="red">Löschen</button>
		</form>
	<?php } ?>

	<div class="modal selectmodal" data-name="addcolumn">
		<div class="box">
			<h2>Rubrik auswählen</h2>
			<form action="#" method="GET">
				<div class="objectbox"></div>
				<template>
					<article>
						<h3>{{name}}</h3>
						<code>{{longid}}</code>
						<label class="radiobodge turn-around blue">
							<span class="label-field">Auswählen</span>
							<input type="radio" name="result" value="{{id}}">
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>
					</article>
				</template>
				<button type="button" data-action="close" class="red">Schließen</button>
				<button type="submit" data-action="submit" class="blue">Auswählen</button>
			</form>
		</div>
	</div>

</main>

<?php if($Controller->request->action == 'new' || $Controller->request->action == 'edit'){
	include COMPONENT_PATH . 'admin/imageinput.php';
	include COMPONENT_PATH . 'admin/timeinput.php';
} ?>

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
		var newelem = document.createElement('div');
		newelem.innerHTML = newcontent.replace(/{{i}}/g, count);
		document.querySelector('.relationlist .objectbox').appendChild(newelem.firstElementChild);
	}

	modals['selectimage'].type = 'image';
	modals['selectimage'].onSubmit = () => {

	}
</script>

<?php include COMPONENT_PATH . 'admin/end.php'; ?>
