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

		<p>Bild <span class="code"><?php echo $obj->longid; ?></span> löschen?</p>
		<form action="delete_image?id=<?php echo $obj->id; ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?php echo $obj->id; ?>">
			<input type="submit" value="Löschen">
		</form>

		<?php
	}
}
?>

<a href="all_images">Zurück zu allen Posts</a>
