<h1>Bild löschen</h1>

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
		$obj->delete();
		?>

		<span class="message success">
			Bild erfolgreich gelöscht.
		</span>

		<?php
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

		<p>Bild <span class="code"><?= $obj->longid ?></span> löschen?</p>
		<form action="<?= ADMIN_URL ?>/delete_image?id=<?= $obj->id ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
			<input type="submit" value="Löschen">
		</form>

		<?php
	}
}
?>

<a href="<?= ADMIN_URL ?>/all_images">Zurück zu allen Posts</a>
