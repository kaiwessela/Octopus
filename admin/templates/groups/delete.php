<form action="#" method="post" class="groups">
	<input type="hidden" id="id" name="id" value="<?= $Object->id ?>">
	<p>Gruppe <code><?= $Object->longid ?></code> endgültig löschen?</p>
	<p>Die Mitglieder dieser Gruppe werden dadurch nicht gelöscht.</p>
	<button type="submit" class="red">Löschen</button>
</form>
