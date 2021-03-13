<form action="#" method="post" class="images">
	<input type="hidden" id="id" name="id" value="<?= $Application->id ?>">
	<p>Dokument <code><?= $Application->longid ?></code> endgültig löschen?</p>
	<p>
		<a href="<?= $Application->src() ?>">Datei: <?= $Application->longid.'.'.$Application->extension ?></a>
	</p>
	<button type="submit" class="red">Löschen</button>
</form>
