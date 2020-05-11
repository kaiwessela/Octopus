<h1>Neuen Post hinzufügen</h1>

<?php
if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	$obj = Post::new();

	$error = false;
	try {
		$obj->insert($_POST);
	} catch(Exception $e){
		$error = true;
	}

	if($error){
		?>

		<span class="message error">
			Fehler beim Versuch, die Post-Daten hochzuladen.
		</span>
		<p>Details: <span class="code"><?php echo $e->getMessage(); ?></span></p>

		<?php
	} else {
		?>

		<span class="message success">
			Post erfolgreich hochgeladen.
		</span>

		<?php
	}
} else {
	?>

	<form action="new_post?action=submit" method="post">
		<label for="longid">URL</label>
		<input type="text" id="longid" name="longid" required>

		<label for="overline">Overline</label>
		<input type="text" id="overline" name="overline">

		<label for="headline">Überschrift</label>
		<input type="text" id="headline" name="headline" required>

		<label for="subline">Subline</label>
		<input type="text" id="subline" name="subline">

		<label for="teaser">Teaser</label>
		<textarea id="teaser" name="teaser"></textarea>

		<label for="author">Autor</label>
		<input type="text" id="author" name="author" required>

		<!-- TODO add image -->

		<label for="content">Inhalt</label>
		<textarea id="content" name="content"></textarea>

		<input type="submit" value="Speichern">
	</form>

	<?php
}
?>

<a href="all_images">Zurück zu allen Posts</a>
