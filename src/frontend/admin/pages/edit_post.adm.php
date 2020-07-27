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
			<p>Details: <span class="code"><?= $e->getMessage() ?></span></p>

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

		<form action="<?= ADMIN_URL ?>/edit_post?id=<?= $obj->id ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
			<input type="hidden" id="longid" name="longid" value="<?= $obj->longid ?>">

			<label for="overline">Overline (optional)</label>
			<input type="text" id="overline" name="overline" value="<?= $obj->overline ?>">

			<label for="headline">Überschrift</label>
			<input type="text" id="headline" name="headline" required value="<?= $obj->headline ?>">

			<label for="subline">Subline (optional)</label>
			<input type="text" id="subline" name="subline" value="<?= $obj->subline ?>">

			<label for="teaser">Teaser (optional)</label>
			<textarea id="teaser" name="teaser" class="teaser-text"><?= $obj->teaser ?></textarea>

			<label for="author">Autor</label>
			<input type="text" id="author" name="author" required value="<?= $obj->author ?>">

			<label for="image_id">Bild-ID</label>
			<input type="text" id="image_id" name="image_id" value="<?= $obj->image->id ?? '' ?>">

			<button type="button" onclick="document.querySelector('#image-select').classList.add('open')">Bild auswählen</button>
			<button type="button" onclick="document.querySelector('#image-upload').classList.add('open')">Bild hochladen</button>

			<label for="content">Inhalt (optional)</label>
			<textarea id="content" name="content" class="long-text"><?= $obj->content ?></textarea>

			<input type="submit" value="Speichern">
		</form>

		<?php
	}
}
?>

<a href="<?= ADMIN_URL ?>/all_posts">Zurück zu allen Posts</a>

<div class="dialog" id="image-select">
	<button class="close" onclick="document.querySelector('#image-select').classList.remove('open')">X</button>
	<h2>Bild auswählen</h2>
	<div class="masonry">

<?php
try {
	$images = Image::pull_all();
} catch(Exception $e){

}

foreach($images as $image){
	if($image->has_size('small')){
		$size = 'small';
	} else {
		$size = 'original';
	}
		?>

		<template>
			<button id="<?= $image->id ?>" class="image">
				<img src="<?= DYN_IMG_PATH . $image->id . '/' . $size . '.' . $image->extension ?>"
					alt = "<?= $image->description ?>">
			</button>
		</template>

		<?php
}
?>

	</div>
	<button class="more">Mehr Bilder anzeigen</button>
	<button class="break" onclick="document.querySelector('#image-select').classList.remove('open')">Abbrechen</button>
	<button class="finish" onclick="document.querySelector('#image-select').classList.remove('open')">Speichern</button>
</div>

<div class="dialog" id="image-upload">
	<button class="close" onclick="document.querySelector('#image-upload').classList.remove('open')">X</button>
	<h2>Bild hochladen</h2>
	<div class="content">
		<form id="image-upload-form">
			<label for="image_longid">URL</label>
			<input type="text" id="image_longid" name="longid" required>

			<label for="image_description">Beschreibung</label>
			<input type="text" id="image_description" name="description">

			<label for="image_imagefile">Datei</label>
			<input type="file" id="image_imagefile" name="imagedata" required>

			<input type="submit" value="Hochladen">
		</form>
		<template id="image-upload-display" class="hidden">
			<p>Hochgeladenes Bild:</p>
			<img src="/resources/images/dynamic/§IMAGEID§/original.§IMAGETYPE§" alt="Bild">
		</template>
	</div>
	<button class="break" onclick="document.querySelector('#image-upload').classList.remove('open')">Abbrechen</button>
	<button class="finish" onclick="document.querySelector('#image-upload').classList.remove('open')">Speichern</button>
</div>

<script>
var imageuploadform = document.getElementById('image-upload-form');
var imageuploaddisplay = document.getElementById('image-upload-display');
imageuploadform.addEventListener('submit', function(event){
	event.preventDefault();

	var longid = document.getElementById('image_longid').value;
	var description = document.getElementById('image_description').value;

	var imagefile = document.getElementById('image_imagefile').files[0];

	var data = {
		longid: longid,
		description: description
	}

	var filereader = new FileReader();
	filereader.addEventListener('load', function(){
		data.imagedata = filereader.result;

		var ajax = new XMLHttpRequest();
		ajax.responseType = 'json';
		ajax.onreadystatechange = function(){
			if(this.readyState == 4 && this.status == 200){
				var resultimg = this.response.result;
				imageuploadform.classList.add('hidden');
				var newcontent = imageuploaddisplay.innerHTML;
				newcontent = newcontent.replace('§IMAGEID§', resultimg.longid);
				newcontent = newcontent.replace('§IMAGETYPE§', resultimg.extension);
				document.querySelector('#image-upload > .content').innerHTML += newcontent;
			}
		}

		ajax.open('POST', '/api/v1/images/new', true);
		ajax.setRequestHeader('Content-Type', 'application/json');
		ajax.send(JSON.stringify(data));

	});

	filereader.readAsDataURL(imagefile);
});


</script>
