<form action="#" method="post">

<?php if($PersonController->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Personen-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Personen-ID wird in der URL verwendet und entspricht meistens dem Namen.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Person->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<input type="hidden" name="id" value="<?= $Person->id ?>">
	<input type="hidden" name="longid" value="<?= $Person->longid ?>">

<?php } ?>

	<!-- NAME -->
	<label for="name">
		<span class="name">Name</span>
		<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
		<span class="infos">
			Der vollständige Name der Person.
		</span>
	</label>
	<input type="text" size="30"
		id="name" name="name" value="<?= $Person->name ?>"
		maxlength="50" required>

	<!-- IMAGE -->
	<label for="image_id">
		<span class="name">Profilbild</span>
		<span class="conditions">optional</span>
		<span class="infos">
			Das Profilbild sollte ein Portrait der Person sein.
		</span>
	</label>
	<input type="text" class="imageinput" size="8"
		id="image_id" name="image_id" value="<?= $Person->image?->id ?>"
		minlength="8" maxlength="8">

	<button type="submit" class="green">Speichern</button>
</form>
