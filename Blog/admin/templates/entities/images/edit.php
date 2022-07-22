<div class="main">
	<label for="name">
		<div class="name">Bildtitel</div>
	</label>
	<input type="text" id="name" name="name" value="<?= $Entity?->name ?>"
		size="60" maxlength="140">

	<label for="file">
		<div class="name">Datei</div>
	</label>
	<input type="file" id="file" name="file" required>

	<label for="alternative">
		<div class="name">Alternativtext</div>
	</label>
	<input type="text" id="alternative" name="alternative" value="<?= $Entity?->alternative ?>"
		size="100" maxlength="250">

</div>
<div class="side">
	<?php include __DIR__.'/../../components/edit/id_and_longid.php'; ?>

	<label for="copyright">
		<div class="name">Urheberrechtshinweis</div>
	</label>
	<input type="text" id="copyright" name="copyright" value="<?= $Entity?->copyright ?>"
		size="60" maxlength="250">
		
</div>
