<form action="#" method="post" class="persons">
	<input type="hidden" id="id" name="id" value="<?= $Person->id ?>">
	<p>Profil <code><?= $Person->longid ?></code> endgültig löschen?</p>
	<p>Falls ein Profilbild gesetzt ist, wird dieses dadurch nicht gelöscht.</p>
	<button type="submit" class="red">Löschen</button>
</form>
