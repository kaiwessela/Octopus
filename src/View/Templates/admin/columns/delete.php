<form action="#" method="post" class="columns">
	<input type="hidden" id="id" name="id" value="<?= $Column->id ?>">
	<p>Rubrik <code><?= $Column->longid ?></code> endgültig löschen?</p>
	<p>Die Artikel, die in dieser Rubrik sind, werden dadurch nicht gelöscht.</p>
	<button type="submit" class="red">Löschen</button>
</form>
