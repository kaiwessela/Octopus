<form action="#" method="post" class="posts">
	<input type="hidden" id="id" name="id" value="<?= $Object->id ?>">
	<p>Artikel <code><?= $Object->longid ?></code> endgültig löschen?</p>
	<p>Falls der Artikel ein Bild enthält, wird dieses dadurch nicht gelöscht.</p>
	<button type="submit" class="red">Löschen</button>
</form>
