<?php
if(!isset($_GET['id'])){
	echo 'Kein Objekt angegeben';
} else if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	try {
		$obj = Image::pull_by_id($_GET['id']);
	} catch(ObjectNotFoundException $e){
		$obj = false;
	}

	if($obj == false){
		echo 'Objekt nicht gefunden';
	} else {
		try {
			$obj->update($_POST);
			echo 'Erfolgreich hochgeladen';
		} catch(ObjectUpdateException $e){
			echo $e->getMessage();
		}
	}

} else {
	try {
		$obj = Image::pull_by_id($_GET['id']);
	} catch(ObjectNotFoundException $e){
		$obj = false;
	}

	if($obj == false){
		echo 'Objekt nicht gefunden';
	} else {
		?>
		<h1>Bild bearbeiten</h1>
		<form action="edit_image?id=<?php echo $obj->id; ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?php echo $obj->id; ?>">
			<input type="hidden" id="longid" name="longid" value="<?php echo $obj->longid; ?>">

			<label for="description">Beschreibung</label>
			<input type="text" id="description" name="description" value="<?php echo $obj->description; ?>"><br><br>

			<input type="submit" value="Speichern">
		</form>
		<?php
	}
}
?>
