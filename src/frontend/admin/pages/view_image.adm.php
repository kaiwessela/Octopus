<h1>Bild ansehen</h1>

<?php
if(isset($_GET['id'])){
	try {
		$obj = Image::pull_by_id($_GET['id']);
	} catch(ObjectNotFoundException $e){
		$obj = false;
	}
} else {
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

	<a href="all_images">Zurück zu allen Bildern</a><br>
	<a href="edit_image?id=<?php echo $obj->id; ?>" class="button">Bildinformationen bearbeiten</a>
	<a href="delete_image?id=<?php echo $obj->id; ?>" class="button">Bild löschen</a><br><br>

	<p>Bild-URL: <span class="code"><?php echo $obj->longid; ?></span></p><br>
	<img src="../resources/images/dynamic/<?php echo $obj->longid . '.' . $obj->extension; ?>" alt="Bild">
	<h2>Beschreibung:</h2>
	<p><?php echo $obj->description; ?></p>

	<h2>Verfügbare Größen:</h2>
	<p>

	<?php
	$sizes = Imagefile::pull_all_sizes($obj->id, true);
	ksort($sizes);
	foreach($sizes as $key => $value){
		switch($key){
			case 0: echo 'Original'; break;
			case 1: echo ', Klein'; break;
			case 2: echo ', Mittel'; break;
			case 3: echo ', Groß'; break;
		}
	}
	?>

	</p>

	<?php
}
?>
