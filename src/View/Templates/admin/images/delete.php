<form action="#" method="post" class="images">
	<input type="hidden" id="id" name="id" value="<?= $Image->id ?>">
	<p>Bild <code><?= $Image->longid ?></code> endgültig löschen?</p>
	<p>
		Damit wird das Bild aus allen Artikeln und Profilen, die es referenzieren, entfernt.
		Die Artikel und Profile selbst werden nicht gelöscht.
	</p>
	<img src="<?= $Image->src() ?>" alt="[ANZEIGEFEHLER]">
	<button type="submit" class="red">Löschen</button>
</form>
