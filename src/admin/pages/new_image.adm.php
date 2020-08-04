<h1>Neues Bild hochladen</h1>

<?php
if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	$obj = Image::new();

	$error = false;
	try {
		$obj->insert($_POST);
	} catch(Exception $e){
		$error = true;
	}

	if($error){
		?>

		<span class="message error">
			Fehler beim Versuch, die Bildinformationen hochzuladen.
		</span>
		<p>Details: <span class="code"><?= $e->getMessage() ?></span></p>

		<?php
	} else {
		?>

		<span class="message success">
			Bild erfolgreich hochgeladen.
		</span>

		<?php
	}
} else {
	?>

	<form action="<?= ADMIN_URL ?>/new_image?action=submit" method="post" enctype="multipart/form-data">
		<label for="longid">URL</label>
		<input type="text" id="longid" name="longid" required>

		<label for="description">Beschreibung</label>
		<input type="text" id="description" name="description">

		<label for="imagefile">Datei</label>
		<input type="file" id="imagefile" name="imagedata" required>

		<input type="submit" value="Speichern">
	</form>

	<?php
}
?>

<a href="<?= ADMIN_URL ?>/all_images">Zurück zu allen Bildern</a>
