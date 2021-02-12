<form action="#" method="post" class="posts">
	<input type="hidden" id="id" name="id" value="<?= $Post->id ?>">
	<p>Post <code><?= $Post->longid ?></code> endgültig löschen?</p>
	<p>Falls der Artikel ein Bild enthält, wird dieses dadurch nicht gelöscht.</p>
	<button type="submit" class="red">Löschen</button>
</form>
