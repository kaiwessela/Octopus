<?php
use \Blog\Config\Config;
?>

<h1>Bild ansehen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Bild nicht vorhanden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_image){ ?>
<a href="<?= Config::SERVER_URL ?>/admin/images">Zurück zu allen Bildern</a><br>
<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $controller->image->id ?>/edit" class="button">Bildinformationen bearbeiten</a>
<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $controller->image->id ?>/delete" class="button">Bild löschen</a><br><br>

<p>Bild-URL: <span class="code"><?= $controller->image->longid ?></span></p><br>
<img src="<?= Config::SERVER_URL . Config::DYN_IMG_PATH . $controller->image->longid?>/original.<?= $controller->image->extension ?>" alt="Bild">
<h2>Beschreibung:</h2>
<p><?= $controller->image->description ?></p>

<h2>Verfügbare Größen:</h2>

	<?php foreach($controller->image->sizes as $size){ ?>
	<a href="<?= Config::SERVER_URL . Config::DYN_IMG_PATH . $obj->longid?>/<?= $size ?>.<?= $obj->extension ?>">
		<?= $size ?>
	</a><br>
	<?php } ?>
<?php } ?>
