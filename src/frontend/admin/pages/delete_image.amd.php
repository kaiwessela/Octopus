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
			$obj->delete();
			echo 'Erfolgreich gelöscht';
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
		<h1>Bild löschen</h1>
		<form action="delete_image?id=<?php echo $obj->id; ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?php echo $obj->id; ?>">
			<input type="submit" value="Löschen">
		</form>
		<?php
	}
}
?>
