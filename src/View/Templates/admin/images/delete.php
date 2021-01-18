<p>Bild <code><?= $Image->longid ?></code> löschen?</p>
<img src="<?= $Image->source_original ?>" alt="[ANZEIGEFEHLER]">
<form action="<?= $server->url ?>/admin/images/<?= $Image->id ?>/delete" method="post">
	<input type="hidden" id="id" name="id" value="<?= $Image->id ?>">
	<button type="submit" class="red">Löschen</button>
</form>
