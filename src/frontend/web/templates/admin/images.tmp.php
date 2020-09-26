<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.comp.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.comp.php'; ?>
		<main>
			<?php if($Image->action == 'list'){ ?>
			<h1>Alle Bilder</h1>
			<?php } else if($Image->action == 'show'){ ?>
			<h1>Bild ansehen</h1>
			<?php } else if($Image->action == 'new'){ ?>
			<h1>Neues Bild hinzufügen</h1>
			<?php } else if($Image->action == 'edit'){ ?>
			<h1>Bild bearbeiten</h1>
			<?php } else if($Image->action == 'delete'){ ?>
			<h1>Bild löschen</h1>
			<?php } ?>

			<?php if(!$Image->action == 'list'){ ?>
			<a href="<?= $server->url ?>/admin/images" class="button">&laquo; Zurück zu allen Bildern</a>
			<?php } ?>

			<?php foreach($Image->errors as $error){ ?>
			<span class="message error">

			</span>
			<p>Details: <span class="code"></span></p>
			<?php } ?>

			<?php if($Image->action == 'list'){ ?>
				<section class="grid">
				<?php foreach($Image->objects as $obj){ ?>
					<article class="image preview">
						<a href="<?= $server->url ?>/admin/images/<?= $obj->id ?>">
							<img src="<?= $server->url . $server->dyn_img_path
								. $obj->longid ?>/original.<?= $obj->extension ?>" alt="<?= $obj->alt ?>">
							<span class="longid"><?= $obj->longid ?></span>
						</a>
					</article>
					<?php } ?>
				</section>
			<?php } ?>

			<?php if($Image->action == 'show'){ ?>
				<?php $obj = $Image->object; ?>
				<article class="image">
					<p>
						<a href="<?= $server->url ?>/admin/images/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= $server->url ?>/admin/images/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</p>
					<p class="longid"><?= $obj->longid ?></p>
					<p class="description"><?= $obj->description ?></p>
					<figure>
						<img src="<?= $server->url . $server->dyn_img_path . $obj->longid ?>/original.<?= $obj->extension ?>"
							alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden">
						<figcaption><?= $obj->copyright; ?>
					</figure>
					<p>
						Verfügbare Größen:
						<?php foreach($obj->sizes as $size){ ?>
						<br>
						<a href="<?= $server->url . $server->dyn_img_path . $obj->longid ?>/<?= $size ?>.<?= $obj->extension ?>">
							<?= $size ?>
						</a>
						<?php } ?>
					</p>
				</article>
			<?php } ?>

			<?php if($Image->action == 'edit' && !$Image->action->completed()){ ?>
				<?php $obj = $Image->object; ?>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<input type="hidden" id="longid" name="longid" value="<?= $obj->longid ?>">

					<label for="description">
						<span class="name">Beschreibung</span>
						<span class="requirements">optional, bis zu 256 Zeichen</span>
						<span class="description">
							Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
							werden kann. Sie sollte den Bildinhalt wiedergeben.
						</span>
					</label>
					<input type="text" id="description" class="description" name="description" value="<?= $obj->description ?>">

					<label for="copyright">
						<span class="name">Urheberrechtshinweis</span>
						<span class="requirements">optional, bis zu 256 Zeichen</span>
						<span class="description">
							Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
							zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
						</span>
					</label>
					<input type="text" id="copyright" class="copyright" name="copyright" value="<?= $obj->copyright ?>">

					<input type="submit" value="Speichern">
				</form>

				<img src="<?= $server->url . $server->dyn_img_path . "$obj->longid/original.$obj->extension" ?>" alt="[ANZEIGEFEHLER]">
			<?php } ?>

			<?php if($Image->action == 'new' && !$Image->action->completed()){ ?>
				<?php $obj = $Image->object; ?>
				<form action="#" method="post" enctype="multipart/form-data">
					<label for="longid">
						<span class="name">Bild-ID</span>
						<span class="requirements">
							erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
							Bindestriche (-)
						</span>
						<span class="description">
							Die Bild-ID wird in der URL verwendet und sollte den Bildinhalt kurz
							beschreiben.
						</span>
					</label>
					<input type="text" id="longid" class="longid" name="longid" required>

					<label for="description">
						<span class="name">Beschreibung</span>
						<span class="requirements">optional</span>
						<span class="description">
							Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
							werden kann. Sie sollte den Bildinhalt wiedergeben.
						</span>
					</label>
					<input type="text" id="description" class="description" name="description">

					<label for="copyright">
						<span class="name">Urheberrechtshinweis</span>
						<span class="requirements">optional</span>
						<span class="description">
							Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
							zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
						</span>
					</label>
					<input type="text" id="copyright" class="copyright" name="copyright">

					<label for="imagefile">
						<span class="name">Datei</span>
						<span class="requirements">erforderlich; PNG, JPEG oder GIF</span>
					</label>
					<input type="file" id="imagefile" class="file" name="imagedata" required>

					<input type="submit" value="Hochladen">
				</form>
			<?php } ?>

			<?php if($Image->action == 'delete' && !$Image->action->completed()){ ?>
				<?php $obj = $Image->object; ?>
				<p>Bild <span class="code"><?= $obj->longid ?></span> löschen?</p>

				<form action="<?= $server->url ?>/admin/images/<?= $obj->id ?>/delete" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<input type="submit" value="Löschen">
				</form>

				<img src="<?= $server->url . $server->dyn_img_path . "$obj->longid/original.$obj->extension" ?>" alt="[ANZEIGEFEHLER]">
			<?php } ?>

		</main>
	</body>
</html>
