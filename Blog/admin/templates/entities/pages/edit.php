<div class="main">
	<label for="content">
		<div class="name">Inhalt</div>
	</label>
	<textarea id="content" name="content" cols="100" rows="40"><?= $Entity?->content ?></textarea>

</div>
<div class="side">
	<?php include __DIR__.'/../../components/edit/id_and_longid.php'; ?>

	<label for="title">
		<div class="name">Titel</div>
	</label>
	<input type="text" id="title" name="title" value="<?= $Entity?->title ?>"
		size="100" maxlength="100" required>

</div>
