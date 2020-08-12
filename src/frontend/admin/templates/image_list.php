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
<table>
	<tr>
		<th>URL</th>
		<th>Dateityp</th>
		<th>Aktionen</th>
	</tr>

	<?php foreach($controller->images as $image){ ?>
	<tr>
		<td><span class="code"><?= $image->longid ?></span></td>
		<td><span class="code"><?= $image->extension ?></span></td>
		<td><a href="<?= Config::SERVER_URL ?>/admin/images/<?= $image->id ?>">Ansehen</a></td>
		<td><a href="<?= Config::SERVER_URL ?>/admin/images/<?= $image->id ?>/edit">Bearbeiten</a></td>
		<td><a href="<?= Config::SERVER_URL ?>/admin/images/<?= $image->id ?>/delete">Löschen</a></td>
	</tr>
	<?php } ?>

</table>
<?php } ?>
