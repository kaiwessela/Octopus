<div class="main">
	<label for="overline">
		<div class="name">Dachzeile</div>
	</label>
	<input type="text" id="overline" name="overline" value="<?= $Entity?->overline ?>"
		size="50" maxlength="50">

	<label for="headline">
		<div class="name">Schlagzeile</div>
		<div class="note error">
			Ungültiger Wert!
		</div>
	</label>
	<input type="text" id="headline" name="headline" value="<?= $Entity?->headline ?>"
		size="100" maxlength="100" required>

	<label for="subline">
		<div class="name">Unterzeile</div>
	</label>
	<input type="text" id="subline" name="subline" value="<?= $Entity?->subline ?>"
		size="100" maxlength="100">

	<label for="teaser">
		<div class="name">Teaser</div>
	</label>
	<textarea id="teaser" name="teaser" cols="80" rows="5"><?= $Entity?->teaser ?></textarea>

	<label for="content">
		<div class="name">Inhalt</div>
	</label>
	<textarea id="content" name="content" cols="100" rows="40"><?= $Entity?->content ?></textarea>

</div>
<div class="side">
	<?php include __DIR__.'/../../components/edit/id_and_longid.php'; ?>

	<label for="author">
		<div class="name">Autor</div>
	</label>
	<input type="text" id="author" name="author" value="<?= $Entity?->author ?>"
		size="40" maxlength="100" required
		autocomplete="name">

	<fieldset id="timestamp" class="row">
		<legend>Veröffentlichungszeit</legend>

		<label>
			<div class="name">Datum</div>
			<input type="date" id="timestamp-date" name="timestamp[date]" value="<?= $Entity?->timestamp?->to_html_date() ?>"
				size="10" required>
		</label>

		<label>
			<div class="name">Uhrzeit</div>
			<input type="time" id="timestamp-time" name="timestamp[time]" value="<?= $Entity?->timestamp?->to_html_time() ?>"
				size="5" required>
		</label>
	</fieldset>

	<label for="image">
		<div class="name">Titelbild</div>
		<div class="note info nojs">
			Da JavaScript in Ihrem Browser deaktiviert ist, müssen Sie die ID des Bildes manuell eingeben.
		</div>
	</label>
	<input type="text" id="image" name="image" value="<?= $Entity?->image?->id ?>" class="monospace"
		size="8" minlength="8" maxlength="8" pattern="^[a-f0-9]*$" data-nojs>
	<div class="entity-input jsonly" data-for="image" data-entity="image">
		<div class="container"></div>
		<template data-case="set">
			<img data-nopreload-src="{src}" data-nopreload-srcset="{srcset}">
		</template>
		<template data-case="clear">
			Leer
		</template>
		<button type="button" data-action="clear">Entfernen</button>
		<button type="button" data-action="pick">Bild auswählen</button>
	</div>
	<?php $interactive_forms[] = 'images/interactive-pick'; ?>

	<fieldset id="columns" class="column">
		<legend>Rubriken</legend>

		<label for="columns-abcdef01">
			<div class="name">name – <code>longid</code></div>
			<input type="checkbox" id="columns-abcdef01" name="columns[abcdef01]">
		</label>

	</fieldset>
</div>
