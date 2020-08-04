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
	} catch(EmptyResultException $e){
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
		} catch(Exception $e){
			$error = true;
		}

		if($error){
			?>

			<span class="message error">
				Fehler beim Versuch, die Bildinformationen zu ändern.
			</span>
			<p>Details: <span class="code"><?= $e->getMessage() ?></span></p>

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
	} catch(EmptyResultException $e){
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

		<p>Bild-URL: <span class="code"><?= $obj->longid ?></span></p>
		<form action="<?= ADMIN_URL ?>/edit_image?id=<?= $obj->id ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
			<input type="hidden" id="longid" name="longid" value="<?= $obj->longid ?>">

			<label for="description">Beschreibung</label>
			<input type="text" id="description" name="description" value="<?= $obj->description ?>">

			<input type="submit" value="Speichern">
		</form>

		<?php
	}
}
?>

<a href="<?= ADMIN_URL ?>/all_images">Zurück zu allen Bildern</a>
