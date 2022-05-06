<label for="id">
	<div class="name">ID</div>
	<div class="note info">
		Die ID wird zufällig generiert und kann nicht verändert werden.
	</div>
</label>
<?php if($EntityController->get_action() === 'empty'){ ?>
	<input type="text" id="id" name="id" size="8" placeholder="(random)" disabled>
<?php } else { ?>
	<input type="text" id="id" name="id" size="8" value="<?= $Entity?->id ?>" readonly>
<?php } ?>

<label for="longid">
	<div class="name">URL-Name</div>
	<div class="note warning" data-if="altered" data-nojs="on">
		Es ist grundsätzlich nicht empfohlen, den URL-Namen zu ändern, weil Verlinkungen oder Lesezeichen auf diese
		Seite dann nicht mehr funktionieren.
	</div>
</label>
<input type="text" id="longid" name="longid" value="<?= $Entity?->longid ?>" class="monospace"
	size="50" maxlength="60" required pattern="^[A-Za-z0-9-_]*$"
	autocomplete="off">
