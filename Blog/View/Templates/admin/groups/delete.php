<form action="#" method="post" class="groups">
	<input type="hidden" id="id" name="id" value="<?= $Group->id ?>">
	<p>Gruppe <code><?= $Group->longid ?></code> endgültig löschen?</p>
	<p>Die Personen-Profile, die in dieser Gruppe sind, werden dadurch nicht gelöscht.</p>
	<button type="submit" class="red">Löschen</button>
</form>
