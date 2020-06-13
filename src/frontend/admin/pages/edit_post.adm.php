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
		} catch(Exception $e){
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

			<label for="overline">Overline (optional)</label>
			<input type="text" id="overline" name="overline" value="<?php echo $obj->overline; ?>">

			<label for="headline">Überschrift</label>
			<input type="text" id="headline" name="headline" required value="<?php echo $obj->headline; ?>">

			<label for="subline">Subline (optional)</label>
			<input type="text" id="subline" name="subline" value="<?php echo $obj->subline; ?>">

			<label for="teaser">Teaser (optional)</label>
			<textarea id="teaser" name="teaser" class="teaser-text"><?php echo $obj->teaser; ?></textarea>

			<label for="author">Autor</label>
			<input type="text" id="author" name="author" required value="<?php echo $obj->author; ?>">

			<label for="image_id">Bild-ID</label>
			<input type="text" id="image_id" name="image_id" value="<?php echo $obj->image->id; ?>">
			<a href="#image-select">Bild auswählen</a>
			<a href="#image-upload">Bild hochladen</a>

			<label for="content">Inhalt (optional)</label>
			<textarea id="content" name="content" class="long-text"><?php echo $obj->content; ?></textarea>

			<input type="submit" value="Speichern">
		</form>

		<?php
	}
}
?>

<a href="all_posts">Zurück zu allen Posts</a>

<div class="dialog open" id="image-select">
	<h2>Bild auswählen</h2>
	<div class="masonry">

<?php
$images = Image::pull_all();
foreach($images as $image){
	if($image->has_size('small')){
		$size = 'small';
	} else {
		$size = 'original';
	}
		?>

		<template>
			<button id="<?php echo $image->id; ?>" class="image">
				<img src="../resources/images/dynamic/<?php echo $image->id . '/' . $size . '.' . $image->extension; ?>"
					alt = "<?php echo $image->description; ?>">
			</button>
		</template>

		<?php
}
?>

	</div>
	<button class="more">Mehr Bilder anzeigen</button>
	<button class="close">Schließen</button>
	<button class="finish">Fertig</button>
</div>

<div class="dialog" id="image-upload">
	<h2>Bild hochladen</h2>
</div>
