<?php
if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	$obj = Post::new();

	try {
		$obj->insert($_POST);
		echo 'Erfolgreich hochgeladen';
	} catch(ObjectInsertException $e){
		echo $e->getMessage();
	}

} else {
	?>
	<h1>Neuen Post schreiben</h1>
	<form action="new_post?action=submit" method="post">
		<label for="longid">URL</label>
		<input type="text" id="longid" name="longid" required><br><br>

		<label for="overline">Overline</label>
		<input type="text" id="overline" name="overline"><br><br>

		<label for="headline">Überschrift</label>
		<input type="text" id="headline" name="headline" required><br><br>

		<label for="subline">Subline</label>
		<input type="text" id="subline" name="subline"><br><br>

		<label for="teaser">Teaser</label>
		<textarea id="teaser" name="teaser"></textarea><br><br>

		<label for="author">Autor</label>
		<input type="text" id="author" name="author" required><br><br>

		<!-- TODO add image -->

		<label for="content">Inhalt</label>
		<textarea id="content" name="content"></textarea><br><br>

		<input type="submit" value="Speichern">
	</form>
	<?php
}
?>
