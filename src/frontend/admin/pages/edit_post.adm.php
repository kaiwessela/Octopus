<?php
if(!isset($_GET['id'])){
	echo 'Kein Objekt angegeben';
} else if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	try {
		$obj = Post::pull_by_id($_GET['id']);
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
		$obj = Post::pull_by_id($_GET['id']);
	} catch(ObjectNotFoundException $e){
		$obj = false;
	}

	if($obj == false){
		echo 'Objekt nicht gefunden';
	} else {
		?>
		<h1>Post bearbeiten</h1>
		<form action="edit_post?id=<?php echo $obj->id; ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?php echo $obj->id; ?>">
			<input type="hidden" id="longid" name="longid" value="<?php echo $obj->longid; ?>">

			<label for="overline">Overline</label>
			<input type="text" id="overline" name="overline" value="<?php echo $obj->overline; ?>"><br><br>

			<label for="headline">Überschrift</label>
			<input type="text" id="headline" name="headline" required value="<?php echo $obj->headline; ?>"><br><br>

			<label for="subline">Subline</label>
			<input type="text" id="subline" name="subline" value="<?php echo $obj->subline; ?>"><br><br>

			<label for="teaser">Teaser</label>
			<textarea id="teaser" name="teaser"><?php echo $obj->teaser; ?></textarea><br><br>

			<label for="author">Autor</label>
			<input type="text" id="author" name="author" required value="<?php echo $obj->author; ?>"><br><br>

			<!-- TODO add image -->

			<label for="content">Inhalt</label>
			<textarea id="content" name="content"><?php echo $obj->content; ?></textarea><br><br>

			<input type="submit" value="Speichern">
		</form>
		<?php
	}
}
?>
