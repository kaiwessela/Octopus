<?php
use \Blog\Config\Config;
$controller->image = $controller->obj; // TEMP
?>

<h1>Bild ansehen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Bild nicht vorhanden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_obj){ ?>
<?php $image = $controller->image ?>
<a href="<?= Config::SERVER_URL ?>/admin/images" class="button">&laquo; Zurück zu allen Bildern</a>

<article class="image">
	<p>
		<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $image->id ?>/edit" class="edit">Bearbeiten</a>
		<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $image->id ?>/delete" class="delete">Löschen</a>
	</p>
	<p class="longid"><?= $image->longid ?></p>
	<p class="description"><?= $image->description ?></p>
	<figure>
		<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . $image->longid ?>/original.<?= $image->extension ?>"
			alt="[ANZEIGEFEHLER] Hier sollte das Bild angezeigt werden">
		<figcaption><?= $image->copyright; ?>
	</figure>
	<p>
		Verfügbare Größen:
		<?php foreach($image->sizes as $size){ ?>
		<br>
		<a href="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . $image->longid ?>/<?= $size ?>.<?= $image->extension ?>">
			<?= $size ?>
		</a>
		<?php } ?>
	</p>
</article>
<?php } ?>
