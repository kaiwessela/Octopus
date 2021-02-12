<form action="#" method="post" class="proposals edit">

<?php if($Controller->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Antrags-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Antrags-ID wird in der URL verwendet und entspricht oftmals ungefähr dem Titel.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Proposal?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Proposal?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Proposal?->longid ?>" size="40" readonly>

<?php } ?>

	<!-- TITLE -->
	<label for="title">
		<span class="name">Titel</span>
		<span class="conditions">erforderlich, 1 bis 80 Zeichen</span>
		<span class="infos">Der Titel des Antrags.</span>
	</label>
	<input type="text" size="60"
		id="title" name="title" value="<?= $Proposal?->title ?>"
		maxlength="80" required>

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional</span>
		<span class="infos">Die Beschreibung des Antrags.</span>
	</label>
	<textarea id="description" name="description"
		cols="70" rows="10"><?= $Proposal?->description ?></textarea>

	<!-- TIMESTAMP -->
	<label for="timestamp">
		<span class="name">Veröffentlichungsdatum und -uhrzeit</span>
		<span class="conditions">erforderlich</span>
		<span class="infos">
			Datum und Uhrzeit der Veröffentlichung. Hat derzeit nur eine informierende Funktion,
			Artikel mit Datum in der Zukunft werden trotzdem angezeigt. Es ist aber eine Funktion
			zur terminierten Veröffentlichung geplant.
		</span>
	</label>
	<input type="text" size="19"
		id="timestamp" name="timestamp" value="<?= $Proposal?->timestamp ?>"
		required>

	<!-- STATUS -->
	<label for="status">
		<span class="name">Status</span>
		<span class="conditions">erforderlich</span>
		<span class="infos">

		</span>
	</label>
	<select id="status" name="status">
		<option value="draft" <?php if($Proposal?->status == 'draft'){ ?>selected<?php } ?>>Entwurf</option>
		<option value="accepted" <?php if($Proposal?->status == 'accepted'){ ?>selected<?php } ?>>Angenommen</option>
		<option value="rejected" <?php if($Proposal?->status == 'rejected'){ ?>selected<?php } ?>>Abgelehnt</option>
	</select>

	<!-- VOTES -->
	<label>
		<span class="name">Abstimmungsergebnis</span>
		<span class="conditions">optional</span>
		<span class="infos">
			Liste von Stimmen, gruppiert nach Fraktion und Stimme (Ja/Nein/Enthaltung).
			Anzahl gibt die Zahl der Fraktionsmitglieder an, die entsprechend gestimmt haben.
			Falls eine Fraktion uneinheitlich abgestimmt hat, müssen mehrere Einträge für diese
			Fraktion angelegt werden.
		</span>
	</label>
	<div class="listinput nojs" data-for="votes">
		<table>
			<tbody class="items">
				<tr>
					<th>Fraktion</th>
					<th>Stimme</th>
					<th>Stimmenanzahl</th>
					<th>Entfernen</th>
				</tr>
				<?php if($Proposal?->votes){ foreach($Proposal->votes as $i => $vote){ ?>
				<tr class="item" data-number="<?= $i ?>">
					<td><input type="text" name="votes[<?= $i ?>][party]" value="<?= $vote['party'] ?>" size="10"></td>
					<td><select name="votes[<?= $i ?>][vote]">
						<option value="true" <?php if($vote['vote'] === true){ ?>selected<?php } ?>>Ja</option>
						<option value="false" <?php if($vote['vote'] === false){ ?>selected<?php } ?>>Nein</option>
						<option value="" <?php if($vote['vote'] === null){ ?>selected<?php } ?>>Enthaltung</option>
					</select></td>
					<td><input type="number" name="votes[<?= $i ?>][amount]" value="<?= $vote['amount'] ?>" size="5" min="1"></td>
					<td><button type="button" class="red" data-action="remove" data-number="<?= $i ?>">Entfernen</button></td>
				</tr>
				<?php }} ?>
			</tbody>
		</table>
		<template>
			<tr class="item" data-number="{{i}}">
				<td><input type="text" name="votes[{{i}}][party]" size="20"></td>
				<td><select name="votes[{{i}}][vote]">
					<option value="true">Ja</option>
					<option value="false">Nein</option>
					<option value="">Enthaltung</option>
				</select></td>
				<td><input type="number" name="votes[{{i}}][amount]" value="1" size="5" min="1"></td>
				<td><button type="button" class="red" data-action="remove" data-number="{{i}}">Entfernen</button></td>
			</tr>
		</template>
		<button type="button" class="green" data-action="add">Stimme(n) hinzufügen</button>
	</div>


	<button type="submit" class="green">Speichern</button>
</form>

<div class="timeinput nojs" data-for="timestamp">
	<label>
		Datum:
		<input type="date">
	</label>
	<label>
		Uhrzeit:
		<input type="time">
	</label>
</div>
