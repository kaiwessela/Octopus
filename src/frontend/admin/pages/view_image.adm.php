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

if($obj != false){
	$post = $obj;

	?>
	<img src="../resources/images/dynamic/<?php echo $obj->id . '.' $obj->extension; ?>" alt="Bild">
	<p><?php echo $obj->description; ?></p>
	<!-- IDEA show sizes list -->
	<?php
} else {
	echo 'Objekt nicht gefunden';
}
?>
