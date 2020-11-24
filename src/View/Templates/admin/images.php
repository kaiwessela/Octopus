<?php include COMPONENT_PATH . 'admin/start.php'; ?>
<main>
	<?php
	$Controller = $ImageController;
	$Object = $Image;
	$singular = 'Bild';
	$plural = 'Bilder';
	$urlclass = 'images';

	include COMPONENT_PATH . 'admin/common-1.php';
	?>

	<?php if($ImageController->request->action == 'list' && $ImageController->found()){ ?>
		<?php
		$pagination = $ImageController->pagination;
		include COMPONENT_PATH . 'admin/pagination.php';
		?>

		<section class="grid">
			<?php foreach($Image as $obj){ ?>
			<article>
				<a href="<?= $server->url ?>/admin/images/<?= $obj->id ?>">
					<img src="<?= $obj->source_original ?>" alt="<?= $obj->description ?>">
					<code><?= $obj->longid ?></code>
				</a>
			</article>
			<?php } ?>
		</section>
	<?php } ?>

	<?php if($ImageController->request->action == 'show' && $ImageController->found()){ ?>
		<?php $obj = $Image; ?>
		<article>
			<code><?= $obj->longid ?></code>
			<p><?= $obj->description ?></p>
			<figure>
				<img src="<?= $obj->source_original ?>"
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

	<?php if(($ImageController->request->action == 'edit' && !$ImageController->edited()) || ($ImageController->request->action == 'new' && !$ImageController->created())){ ?>
		<?php $obj = $Image; ?>
		<form action="#" method="post" enctype="multipart/form-data">

			<?php if($ImageController->request->action == 'new'){ ?>
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
			<input type="text" id="longid" name="longid" value="<?= $obj->longid ?>" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" autocomplete="off">
			<?php } else { ?>
			<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
			<input type="hidden" id="longid" name="longid" value="<?= $obj->longid ?>">
			<?php } ?>

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

			<?php if($ImageController->request->action == 'new'){ // TODO TODO TODO see in backend how invalid image requests are handled ?>
			<label for="imagefile">
				<span class="name">Datei</span>
				<span class="conditions">erforderlich; PNG, JPEG oder GIF</span>
			</label>
			<input type="file" id="imagefile" class="file" name="imagedata" required>
			<?php } ?>

			<button type="submit" class="green">Speichern</button>
		</form>

		<?php if($ImageController->request->action == 'edit'){ ?>
		<br>
		<img src="<?= $server->url . $server->dyn_img_path . "$obj->longid/original.$obj->extension" ?>" alt="[ANZEIGEFEHLER]">
		<?php } ?>
	<?php } ?>

	<?php if($ImageController->request->action == 'delete' && !$ImageController->deleted()){ ?>
		<?php $obj = $Image; ?>
		<p>Bild <code><?= $obj->longid ?></code> löschen?</p>
		<img src="<?= $obj->source_original ?>" alt="[ANZEIGEFEHLER]">
		<form action="<?= $server->url ?>/admin/images/<?= $obj->id ?>/delete" method="post">
			<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
			<button type="submit" class="red">Löschen</button>
		</form>
	<?php } ?>

</main>

<?php include COMPONENT_PATH . 'admin/end.php'; ?>
