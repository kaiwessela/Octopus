<?php
use \Blog\Config\Config;
?>

<h1>Alle Bilder</h1>

<?php if($controller->show_warn_no_found){ ?>
<span class="message warning">
	Bisher sind keine Bilder vorhanden.
</span>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/images/new" class="button">Neues Bild hochladen</a>

<?php if($controller->show_list){ ?>
<section class="grid">
	<?php foreach($controller->images as $image){ ?>
	<article class="image preview">
		<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $image->id ?>">
			<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH
				. $image->longid ?>/original.<?= $image->extension ?>" alt="<?= $image->alt ?>">
			<span class="longid"><?= $image->longid ?></span>
		</a>
	</article>
	<?php } ?>
</section>
<?php } ?>
