<form action="#" method="post" class="images">
	<input type="hidden" id="id" name="id" value="<?= $Application->id ?>">
	<p>Datei <code><?= $Application->longid ?></code> endgültig löschen?</p>
	<p>
		Damit wird die Datei aus allen Artikeln und Profilen, die sie referenzieren, entfernt.
		Die Artikel und Profile selbst werden nicht gelöscht.
	</p>
	<a href="<?= $Application->src() ?>">Datei: <?= $Application->longid.'.'.$Application->extension ?></a>
	<button type="submit" class="red">Löschen</button>
</form>
