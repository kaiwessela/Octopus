<form action="#" method="post" class="images">
	<input type="hidden" id="id" name="id" value="<?= $Object->id ?>">
	<p>Dokument <code><?= $Object->longid ?></code> endgültig löschen?</p>
	<p>
		<a class="button" href="<?= $Object->src() ?>">Datei: <?= $Object->longid.'.'.$Object->extension ?></a>
	</p>
	<button type="submit" class="red">Löschen</button>
</form>
