<form action="#" method="post" class="events">
	<input type="hidden" id="id" name="id" value="<?= $Event->id ?>">
	<p>Veranstaltung <code><?= $Event->longid ?></code> endgültig löschen?</p>
	<button type="submit" class="red">Löschen</button>
</form>
