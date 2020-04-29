<?php
if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	$obj = Image::new();

	try {
		$obj->insert($_POST);
		echo 'Erfolgreich hochgeladen';
	} catch(ObjectInsertException $e){
		echo $e->getMessage();
	}

} else {
	?>
	<h1>Neues Bild hochladen</h1>
	<form action="new_image?action=submit" method="post" enctype="multipart/form-data">
		<label for="longid">URL</label>
		<input type="text" id="longid" name="longid" required><br><br>

		<label for="description">Beschreibung</label>
		<input type="text" id="description" name="description"><br><br>

		<label for="imagefile">Datei</label>
		<input type="file" id="imagefile" name="imagefile" required><br><br>

		<input type="submit" value="Speichern">
	</form>
	<?php
}
?>
