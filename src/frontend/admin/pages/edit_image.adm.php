<h1>Bildinformationen bearbeiten</h1>

<?php
if(!isset($_GET['id'])){
	?>

	<span class="message error">
		Keine Bild-ID angegeben.
	</span>

	<?php
} else if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	try {
		$obj = Image::pull_by_id($_GET['id']);
	} catch(ObjectNotFoundException $e){
		$obj = false;
	}

	if($obj == false){
		?>

		<span class="message error">
			Bild nicht vorhanden.
		</span>

		<?php
	} else {
		$error = false;
		try {
			$obj->update($_POST);
		} catch(ObjectUpdateException $e){
			$error = true;
		}

		if($error){
			?>

			<span class="message error">
				Fehler beim Versuch, die Bildinformationen zu ändern.
			</span>
			<p>Details: <span class="code"><?php echo $e->getMessage(); ?></span></p>

			<?php
		} else {
			?>

			<span class="message success">
				Bildinformationen erfolgreich geändert.
			</span>

			<?php
		}
	}

} else {
	try {
		$obj = Image::pull_by_id($_GET['id']);
	} catch(ObjectNotFoundException $e){
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

		<p>Bild-URL: <span class="code"><?php echo $obj->longid; ?></span></p>
		<form action="edit_image?id=<?php echo $obj->id; ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?php echo $obj->id; ?>">
			<input type="hidden" id="longid" name="longid" value="<?php echo $obj->longid; ?>">

			<label for="description">Beschreibung</label>
			<input type="text" id="description" name="description" value="<?php echo $obj->description; ?>">

			<input type="submit" value="Speichern">
		</form>

		<?php
	}
}
?>

<a href="all_images">Zurück zu allen Bildern</a>
