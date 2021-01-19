<form action="#" method="post">

<?php if($ColumnController->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Rubrik-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Rubrik-ID wird in der URL verwendet und entspricht oftmals ungefähr dem Namen.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Column?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<input type="hidden" name="id" value="<?= $Column?->id ?>">
	<input type="hidden" name="longid" value="<?= $Column?->longid ?>">

<?php } ?>

	<!-- NAME -->
	<label for="name">
		<span class="name">Name</span>
		<span class="conditions">erforderlich, 1 bis 30 Zeichen</span>
		<span class="infos">
			Der Name der Rubrik.
		</span>
	</label>
	<input type="text" size="30"
		id="name" name="name" value="<?= $Column?->name ?>"
		maxlength="30" required>

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional</span>
		<span class="infos">
			Die Beschreibung der Rubrik.
		</span>
	</label>
	<textarea id="description" name="description"
		cols="50" rows="3"><?= $Column?->description ?></textarea>

	<button type="submit" class="green">Speichern</button>
</form>
