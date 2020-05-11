<h1>Neues Bild hochladen</h1>

<?php
if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	$obj = Image::new();

	$error = false;
	try {
		$obj->insert($_POST);
	} catch(ObjectInsertException $e){
		$error = true;
	}

	if($error){
		?>

		<span class="message error">
			Fehler beim Versuch, die Bildinformationen hochzuladen.
		</span>
		<p>Details: <span class="code"><?php echo $e->getMessage(); ?></span></p>

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

	<form action="new_image?action=submit" method="post" enctype="multipart/form-data">
		<label for="longid">URL</label>
		<input type="text" id="longid" name="longid" required>

		<label for="description">Beschreibung</label>
		<input type="text" id="description" name="description">

		<label for="imagefile">Datei</label>
		<input type="file" id="imagedata" name="imagedata" required>

		<input type="submit" value="Speichern">
	</form>

	<?php
}
?>

<a href="all_images">Zurück zu allen Bildern</a>
