<?php use \Blog\Config\MediaConfig; ?>

<form action="#" method="post" class="motions edit">

<?php if($Controller->call->action == 'new'){ ?>

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
		id="longid" name="longid" value="<?= $Object?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Object?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Object?->longid ?>" size="40" readonly>

<?php } ?>

	<!-- TITLE -->
	<label for="title">
		<span class="name">Titel</span>
		<span class="conditions">erforderlich, 1 bis 80 Zeichen</span>
		<span class="infos">Der Titel des Antrags.</span>
	</label>
	<input type="text" size="60"
		id="title" name="title" value="<?= $Object?->title ?>"
		maxlength="80" required>

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional, Markdown-Schreibweise möglich</span>
		<span class="infos">Die Beschreibung des Antrags.</span>
	</label>
	<textarea id="description" name="description"
		cols="70" rows="10"><?= $Object?->description ?></textarea>

	<!-- TIMESTAMP -->
	<label for="timestamp">
		<span class="name">Sitzungsdatum und -uhrzeit</span>
		<span class="conditions">erforderlich</span>
		<span class="infos">
			Datum und Uhrzeit der Sitzung, auf der der Antrag gestellt wurde oder werden soll.
		</span>
	</label>
	<input type="text" size="19"
		id="timestamp" name="timestamp" value="<?= $Object?->timestamp ?>"
		required>

	<!-- STATUS -->
	<label for="status">
		<span class="name">Status</span>
		<span class="conditions">erforderlich</span>
		<span class="infos">

		</span>
	</label>
	<select id="status" name="status">
		<option value="draft" <?php if($Object?->status == 'draft'){ ?>selected<?php } ?>>Entwurf</option>
		<option value="accepted" <?php if($Object?->status == 'accepted'){ ?>selected<?php } ?>>Angenommen</option>
		<option value="rejected" <?php if($Object?->status == 'rejected'){ ?>selected<?php } ?>>Abgelehnt</option>
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
				<?php if($Object?->votes){ foreach($Object->votes as $i => $vote){ ?>
				<tr class="item" data-number="<?= $i ?>">
					<td><input type="text" name="votes[<?= $i ?>][party]" value="<?= $vote['party'] ?>" size="10"></td>
					<td><select name="votes[<?= $i ?>][vote]">
						<option value="yes" <?php if($vote['vote'] == 'yes'){ ?>selected<?php } ?>>Ja</option>
						<option value="no" <?php if($vote['vote'] == 'no'){ ?>selected<?php } ?>>Nein</option>
						<option value="abstention" <?php if($vote['vote'] == 'abstention'){ ?>selected<?php } ?>>Enthaltung</option>
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
					<option value="yes">Ja</option>
					<option value="no">Nein</option>
					<option value="abstention">Enthaltung</option>
				</select></td>
				<td><input type="number" name="votes[{{i}}][amount]" value="1" size="5" min="1"></td>
				<td><button type="button" class="red" data-action="remove" data-number="{{i}}">Entfernen</button></td>
			</tr>
		</template>
		<button type="button" class="green" data-action="add">Stimme(n) hinzufügen</button>
	</div>

	<!-- DOCUMENT -->
	<label for="document_id">
		<span class="name">Dokument</span>
		<span class="conditions">optional</span>
		<span class="infos">Das Antragsdokument</span>
	</label>
	<input type="text" class="imageinput" size="8"
		id="document_id" name="document_id" value="<?= $Object?->document?->id ?>"
		minlength="8" maxlength="8">


	<button type="submit" class="green">Speichern</button>
</form>


<div class="modal selectmodal nojs" data-name="document-select" data-type="Application" data-objectsperpage="20">
	<div class="box">
		<h2>Dokument auswählen</h2>
		<div class="pagination">
			<template>
				<button type="button" data-action="paginate" data-page="{{page}}">{{page}}</button>
			</template>
		</div>
		<form action="#" method="GET">
			<section class="objects">
				<template>
					<label class="application">

						<label class="radiobodge">
							<input type="radio" name="result" value="{{id}}" {{current}}>
							<div class="bodgeradio">
								<div class="bodgetick"></div>
							</div>
						</label>

						<img src="<?= $server->url ?>/resources/images/icons/{{type}}.svg">
						<div class="label">
							<h2 class="title">{{title}}</h2>
							<code>{{longid}}</code>
						</div>
					</label>
				</template>
			</section>
			<button type="submit" data-action="submit" class="blue">Auswählen</button>
			<button type="button" data-action="close" class="red">Schließen</button>
		</form>
	</div>
</div>

<div class="modal uploadmodal nojs" data-name="document-upload" data-type="Application">
	<div class="box">
		<h2>Neues Dokument hochladen</h2>
		<form action="#" method="GET">
			<label for="document-upload-longid">
				<span class="name">Bild-ID</span>
				<span class="conditions">
					erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
					Bindestriche (-)
				</span>
				<span class="infos">
					Die Dokumenten-ID wird in der URL verwendet und sollte auf den Titel oder Inhalt
					hinweisen.
				</span>
			</label>
			<input type="text" size="40" autocomplete="off"
				id="document-upload-longid" name="longid"
				minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

			<label for="document-upload-title">
				<span class="name">Titel</span>
				<span class="conditions">optional, bis zu 60 Zeichen</span>
				<span class="infos">Der Titel des Dokuments.</span>
			</label>
			<input type="text" size="40"
				id="document-upload-title" name="title"
				maxlength="60">

			<label for="document-upload-description">
				<span class="name">Beschreibung</span>
				<span class="conditions">optional, bis zu 250 Zeichen</span>
				<span class="infos">
					Eine kurze Beschreibung des Dokumentinhalts.
				</span>
			</label>
			<input type="text" size="80"
				id="document-upload-description" name="description"
				maxlength="250">

			<label for="document-upload-copyright">
				<span class="name">Urheberrechtshinweis</span>
				<span class="conditions">optional, bis zu 250 Zeichen</span>
				<span class="infos">
					Der Urbeherrechtshinweis kann genutzt werden, um den Urheber des Dokuments und die Lizenz,
					unter der es zur Verfügung steht, anzugeben.
				</span>
			</label>
			<input type="text" size="80"
				id="document-upload-copyright" name="copyright"
				maxlength="250">

			<label for="document-upload-file">
				<span class="name">Datei</span>
				<span class="conditions">erforderlich</span>
			</label>
			<input type="file" class="file"
				id="document-upload-file" name="file" required
				accept="<?= implode(', ', MediaConfig::APPLICATION_TYPES); ?>">

			<button type="submit" data-action="submit" class="green">Hochladen</button>
			<button type="button" data-action="close" class="red">Schließen</button>
		</form>
	</div>
</div>

<div class="pseudoinput nojs" data-type="Application" data-for="document_id" data-selectmodal="document-select" data-uploadmodal="document-upload">
	<div class="object"></div>
	<template data-state="empty">
		<p>Kein Dokument ausgewählt.</p>
	</template>
	<template data-state="set">
		<article class="application">
			<img src="<?= $server->url ?>/resources/images/icons/{{type}}.svg">
			<div class="label">
				<h2 class="title">{{title}}</h2>
				<code>{{longid}}</code>
			</div>
		</article>
	</template>
	<button type="button" class="green" data-action="upload">Neues Dokument hochladen</button>
	<button type="button" class="blue" data-action="select">Aus vorhandenen auswählen</button>
	<button type="button" class="red" data-action="clear">Dokument entfernen</button>
</div>



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
