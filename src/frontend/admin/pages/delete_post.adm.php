<h1>Post löschen</h1>

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
		$obj->delete();
		?>

		<span class="message success">
			Post erfolgreich gelöscht.
		</span>

		<?php
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

		<p>Post <span class="code"><?php echo $obj->longid; ?></span> löschen?</p>
		<form action="delete_post?id=<?php echo $obj->id; ?>&action=submit" method="post">
			<input type="hidden" id="id" name="id" value="<?php echo $obj->id; ?>">
			<input type="submit" value="Löschen">
		</form>

		<?php
	}
}
?>

<a href="all_posts">Zurück zu allen Posts</a>
