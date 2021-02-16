<form action="#" method="post" class="groups edit">

<?php if($Controller->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Gruppen-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Gruppen-ID wird in der URL verwendet und entspricht oftmals ungefähr dem Namen.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Group?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Group?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Group?->longid ?>" size="40" readonly>

<?php } ?>

	<!-- NAME -->
	<label for="name">
		<span class="name">Name</span>
		<span class="conditions">erforderlich, 1 bis 30 Zeichen</span>
		<span class="infos">
			Der Name der Gruppe.
		</span>
	</label>
	<input type="text" size="30"
		id="name" name="name" value="<?= $Group?->name ?>"
		maxlength="30" required>

	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional</span>
		<span class="infos">
			Die Beschreibung der Gruppe.
		</span>
	</label>
	<textarea id="description" name="description"
		cols="50" rows="3"><?= $Group?->description ?></textarea>

	<!-- MEMBERS -->
	<label>
		<span class="name">Mitglieder</span>
		<span class="conditions">optional, Mehrfacheintrag möglich</span>
		<span class="infos">
			Änderungen werden lokal zwischengespeichert und beim Abschicken übernommen.
		</span>
	</label>
	<div class="relationinput nojs" data-type="Person" data-unique="false" data-for="persons" data-selectmodal="persons-select">
		<div class="objects">
			<?php $Group?->personrelations?->foreach(function($i, $rel) use ($Group){ ?>
				<div class="relation" data-i="<?= $i ?>" data-exists="true">
					<input type="hidden" name="personrelations[<?= $i ?>][id]" value="<?= $rel->id ?>">
					<input type="hidden" name="personrelations[<?= $i ?>][action]" class="action" value="ignore">
					<input type="hidden" name="personrelations[<?= $i ?>][person_id]" class="objectId" value="<?= $rel->person->id ?>">
					<input type="hidden" name="personrelations[<?= $i ?>][group_id]" value="<?= $Group?->id ?>">
					<p class="title" title="{{longid}}"><span><?= $rel->person->name ?></span></p>
					<input type="number" name="personrelations[<?= $i ?>][number]" value="<?= $rel->number ?>" data-origval="<?= $rel->number ?>" placeholder="Nr." size="5">
					<input type="text" name="personrelations[<?= $i ?>][role]" value="<?= $rel->role ?>" data-origval="<?= $rel->role ?>" placeholder="Rolle" size="20">
					<button type="button" class="red" data-action="remove">Entfernen</button>
					<button type="button" data-action="restore">Entf. rückgängig</button>
				</div>
			<?php }); ?>
			<template>
				<div class="relation" data-i="{{i}}" data-exists="false">
					<input type="hidden" name="personrelations[{{i}}][action]" class="action" value="new">
					<input type="hidden" name="personrelations[{{i}}][person_id]" class="objectId" value="{{id}}">
					<input type="hidden" name="personrelations[{{i}}][group_id]" value="<?= $Group?->id ?>">
					<p class="title" title="{{longid}}"><span>{{name}}</span></p>
					<input type="number" name="personrelations[<?= $i ?>][number]" placeholder="Nr." size="5">
					<input type="text" name="personrelations[<?= $i ?>][role]" placeholder="Rolle" size="20">
					<button type="button" class="red" data-action="remove">Entfernen</button>
				</div>
			</template>
		</div>
		<button type="button" class="new blue" data-action="select">Person(en) hinzufügen</button>
	</div>


	<button type="submit" class="green">Speichern</button>
</form>

<div class="modal multiselectmodal nojs" data-name="persons-select" data-type="Person" data-objectsperpage="20">
	<div class="box">
		<h2>Personen auswählen</h2>
		<form action="#" method="GET">
			<section class="objects">
				<template>
					<article>
						<label class="checkbodge turn-around">
							<span class="label-field">{{name}} – {{longid}}</span>
							<input type="checkbox" name="result" value="{{id}}">
							<span class="bodgecheckbox">
								<span class="bodgetick">
									<span class="bodgetick-down"></span>
									<span class="bodgetick-up"></span>
								</span>
							</span>
						</label>
					</article>
				</template>
			</section>
			<button type="button" data-action="loadmore">Weitere Personen laden</button><br>
			<button type="submit" data-action="submit" class="blue">Auswählen</button>
			<button type="button" data-action="close" class="red">Schließen</button>
		</form>
	</div>
</div>
