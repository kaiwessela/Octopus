<div class="main">
	<label for="name">
		<div class="name">Name</div>
	</label>
	<input type="text" id="name" name="name" value="<?= $Entity?->name ?>"
		size="60" maxlength="60" required>

	<label for="profile">
		<div class="name">Profiltext</div>
	</label>
	<textarea id="profile" name="profile" cols="100" rows="30"><?= $Entity?->profile ?></textarea>

</div>
<div class="side">
	<?php include __DIR__.'/../../components/edit/id_and_longid.php'; ?>

	<label for="image">
		<div class="name">Profilbild</div>
		<div class="note info" data-nojs="on">
			Da JavaScript in Ihrem Browser deaktiviert ist, müssen Sie die ID des Bildes manuell eingeben.
		</div>
	</label>
	<input type="text" id="image" name="image" value="<?= $Entity?->image?->id ?>" class="monospace"
		size="8" minlength="8" maxlength="8" pattern="^[a-f0-9]*$">

	<!-- TODO groups -->

</div>
