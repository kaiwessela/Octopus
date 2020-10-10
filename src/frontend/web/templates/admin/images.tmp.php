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
			<h1>Neues Bild hochladen</h1>
			<?php } else if($Image->action == 'edit'){ ?>
			<h1>Bild bearbeiten</h1>
			<?php } else if($Image->action == 'delete'){ ?>
			<h1>Bild löschen</h1>
			<?php } ?>

			<?php if($Image->action == 'list'){ ?>
				<a href="<?= $server->url ?>/admin/images/new" class="button new green">Neues Bild hochladen</a>
			<?php } else { ?>
				<a href="<?= $server->url ?>/admin/images" class="button back">Zurück zu allen Bildern</a>
			<?php } ?>

			<?php foreach($Image->errors as $error){ ?>
			<div class="message red">
				<?= $error->getMessage(); ?>
			</div>
			<?php } ?>

			<?php if($Image->action != 'list' && $Image->action != 'new'){ ?>
			<div>
				<?php if($Image->action != 'show'){ ?>
				<a class="button blue" href="<?= $server->url ?>/admin/images/<?= $Image->object->id ?>">Ansehen</a>
				<?php } ?>

				<?php if($Image->action != 'edit'){ ?>
				<a class="button yellow" href="<?= $server->url ?>/admin/images/<?= $Image->object->id ?>/edit">Bearbeiten</a>
				<?php } ?>

				<?php if($Image->action != 'delete'){ ?>
				<a class="button red" href="<?= $server->url ?>/admin/images/<?= $Image->object->id ?>/delete">Löschen</a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if(($Image->action == 'new' || $Image->action == 'edit') && $Image->action->completed()){ ?>
			<div class="message green">
				Bild <code><?= $Image->object->longid ?></code> wurde erfolgreich gespeichert.
			</div>
			<?php } ?>

			<?php if($Image->action == 'list'){ ?>
				<?php
				$pagination = $Image->pagination;
				include COMPONENT_PATH . 'admin/pagination.comp.php';
				?>

				<?php if(empty($Image->objects)){ ?>
				<div class="message yellow">
					Es sind noch keine Bilder vorhanden.
				</div>

				<?php } else { ?>
				<section class="grid">
					<?php foreach($Image->objects as $obj){ ?>
					<article>
						<a href="<?= $server->url ?>/admin/images/<?= $obj->id ?>">
							<img src="<?= $server->url . $server->dyn_img_path
								. $obj->longid ?>/original.<?= $obj->extension ?>" alt="<?= $obj->description ?>">
							<code><?= $obj->longid ?></code>
						</a>
					</article>
					<?php } ?>
				</section>
				<?php } ?>
			<?php } ?>

			<?php if($Image->action == 'show'){ ?>
				<?php $obj = $Image->object; ?>
				<article>
					<code><?= $obj->longid ?></code>
					<p><?= $obj->description ?></p>
					<figure>
						<img src="<?= $server->url . $server->dyn_img_path . $obj->longid ?>/original.<?= $obj->extension ?>"
							alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden">
						<figcaption><small><?= $obj->copyright; ?></small></figcaption>
					</figure>
					<p>
						Verfügbare Größen:
						<?php foreach($obj->sizes as $size){ ?>
						<a href="<?= $server->url . $server->dyn_img_path . $obj->longid ?>/<?= $size ?>.<?= $obj->extension ?>" class="button gray">
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
						<span class="conditions">optional, bis zu 100 Zeichen</span>
						<span class="infos">
							Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
							werden kann. Sie sollte den Bildinhalt wiedergeben.
						</span>
					</label>
					<input type="text" id="description" name="description" value="<?= $obj->description ?>" size="60" maxlength="100">

					<label for="copyright">
						<span class="name">Urheberrechtshinweis</span>
						<span class="conditions">optional, bis zu 100 Zeichen</span>
						<span class="infos">
							Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
							zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
						</span>
					</label>
					<input type="text" id="copyright" class="copyright" name="copyright" value="<?= $obj->copyright ?>" size="50" maxlength="100">

					<button type="submit" class="green">Speichern</button>
				</form>

				<br>
				<img src="<?= $server->url . $server->dyn_img_path . "$obj->longid/original.$obj->extension" ?>" alt="[ANZEIGEFEHLER]">
			<?php } ?>

			<?php if($Image->action == 'new' && !$Image->action->completed()){ ?>
				<?php $obj = $Image->object; ?>
				<form action="#" method="post" enctype="multipart/form-data">
					<label for="longid">
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
					<input type="text" id="longid" name="longid" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]$" autocomplete="off">

					<label for="description">
						<span class="name">Beschreibung</span>
						<span class="conditions">optional, bis zu 100 Zeichen</span>
						<span class="infos">
							Die Beschreibung wird als Alternativtext angezeigt, wenn das Bild nicht geladen
							werden kann. Sie sollte den Bildinhalt wiedergeben.
						</span>
					</label>
					<input type="text" id="description" class="description" name="description" size="60" maxlength="100">

					<label for="copyright">
						<span class="name">Urheberrechtshinweis</span>
						<span class="conditions">optional, bis zu 100 Zeichen</span>
						<span class="infos">
							Der Urbeherrechtshinweis kann genutzt werden, um Lizensierungsinformationen zu dem Bild
							zur Verfügung zu stellen. Er wird normalerweise unterhalb des Bildes angezeigt.
						</span>
					</label>
					<input type="text" id="copyright" class="copyright" name="copyright" size="50" maxlength="100">

					<label for="imagefile">
						<span class="name">Datei</span>
						<span class="conditions">erforderlich; PNG, JPEG oder GIF</span>
					</label>
					<input type="file" id="imagefile" class="file" name="imagedata" required>

					<button type="submit" class="green">Hochladen</button>
				</form>
			<?php } ?>

			<?php if($Image->action == 'delete' && !$Image->action->completed()){ ?>
				<?php $obj = $Image->object; ?>
				<p>Bild <code><?= $obj->longid ?></code> löschen?</p>
				<img src="<?= $server->url . $server->dyn_img_path . "$obj->longid/original.$obj->extension" ?>" alt="[ANZEIGEFEHLER]">
				<form action="<?= $server->url ?>/admin/images/<?= $obj->id ?>/delete" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<button type="submit" class="red">Löschen</button>
				</form>

			<?php } ?>

			<script src="<?= $server->url ?>/resources/js/admin/validate.js"></script>
		</main>
	</body>
</html>
