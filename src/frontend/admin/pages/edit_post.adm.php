<h1>Post bearbeiten</h1>

<?php
if(!isset($_GET['id'])){
	?>

	<span class="message error">
		Keine Post-ID angegeben.
	</span>

	<?php
} else if(isset($_GET['action']) && $_GET['action'] == 'submit'){
	try {
		$obj = Post::pull_by_id($_GET['id']);
	} catch(EmptyResultException $e){
		$obj = false;
	}

	if($obj == false){
		?>

		<span class="message error">
			Post nicht vorhanden.
		</span>

		<?php
	} else {
		$error = false;
		try {
			$obj->update($_POST);
		} catch(EmptyResultException $e){
			$error = true;
		}

		if($error){
			?>

			<span class="message error">
				Fehler beim Versuch, die Post-Daten zu ändern.
			</span>
			<p>Details: <span class="code"><?php echo $e->getMessage(); ?></span></p>

			<?php
		} else {
			?>

			<span class="message success">
				Post erfolgreich geändert.
			</span>

			<?php
		}
	}

} else {
	try {
		$obj = Post::pull_by_id($_GET['id']);
	} catch(EmptyResultException $e){
		$obj = false;
	}

	if($obj == false){
		?>

		<span class="message error">
			Post nicht vorhanden.
		</span>

		<?php
	} else {
		?>

		<form action="edit_post?id=<?php echo $obj->id; ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?php echo $obj->id; ?>">
			<input type="hidden" id="longid" name="longid" value="<?php echo $obj->longid; ?>">

			<label for="overline">Overline</label>
			<input type="text" id="overline" name="overline" value="<?php echo $obj->overline; ?>">

			<label for="headline">Überschrift</label>
			<input type="text" id="headline" name="headline" required value="<?php echo $obj->headline; ?>">

			<label for="subline">Subline</label>
			<input type="text" id="subline" name="subline" value="<?php echo $obj->subline; ?>">

			<label for="teaser">Teaser</label>
			<textarea id="teaser" name="teaser"><?php echo $obj->teaser; ?></textarea>

			<label for="author">Autor</label>
			<input type="text" id="author" name="author" required value="<?php echo $obj->author; ?>">

			<!-- TODO add image -->

			<label for="content">Inhalt</label>
			<textarea id="content" name="content"><?php echo $obj->content; ?></textarea>

			<input type="submit" value="Speichern">
		</form>

		<?php
	}
}
?>

<a href="all_images">Zurück zu allen Posts</a>
