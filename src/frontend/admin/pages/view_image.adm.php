<h1>Bild ansehen</h1>

<?php
if(isset($_GET['id'])){
	try {
		$obj = Image::pull_by_id($_GET['id']);
	} catch(EmptyResultException $e){
		$obj = false;
	}
} else {
	$obj = false;
}

if($obj == false){
	?>

	<span class="message error">
		Bild nicht vorhanden.
	</span>

	<?php
} else {
	?>

	<a href="<?= ADMIN_URL ?>/all_images">Zurück zu allen Bildern</a><br>
	<a href="<?= ADMIN_URL ?>/edit_image?id=<?= $obj->id ?>" class="button">Bildinformationen bearbeiten</a>
	<a href="<?= ADMIN_URL ?>/delete_image?id=<?= $obj->id ?>" class="button">Bild löschen</a><br><br>

	<p>Bild-URL: <span class="code"><?= $obj->longid ?></span></p><br>
	<img src="<?= DYN_IMG_PATH . $obj->longid . '/original.' . $obj->extension ?>" alt="Bild">
	<h2>Beschreibung:</h2>
	<p><?= $obj->description ?></p>

	<h2>Verfügbare Größen:</h2>

	<?php
	foreach($obj->sizes as $size){
		?>
		<a href="<?= DYN_IMG_PATH . $obj->longid . '/' . $size . '.' . $obj->extension ?>">
			<?= $size ?>
		</a><br>
		<?php
	}
}
?>
