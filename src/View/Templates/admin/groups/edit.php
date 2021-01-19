<form action="#" method="post">

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
		id="longid" name="longid" value="<?= $Group->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<input type="hidden" name="id" value="<?= $Group->id ?>">
	<input type="hidden" name="longid" value="<?= $Group->longid ?>">

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
		id="name" name="name" value="<?= $Group->name ?>"
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
		cols="50" rows="3">
		<?= $Group->description ?>
	</textarea>

	<!-- MEMBERS -->
	<label>
		<span class="name">Mitglieder</span>
		<span class="conditions">optional</span>
	</label>
	<div class="relationinput nojs" data-type="Person" data-for="persons" data-selectmodal="persons-select">
		<div class="objects">
			<template>
				<div class="relation">
					<input type="hidden" name="persons[{{i}}][person_id]" value="{{id}}">
					<input type="hidden" name="persons[{{i}}][group_id]" value="<?= $Object->id ?>">
					<p>{{name}} – {{longid}}</p>

					<label class="radiobodge turn-around blue">
						<span class="label-field">Keine Änderung</span>
						<input type="radio" name="persons[{{i}}][action]" value="ignore">
						<span class="bodgeradio"><span class="bodgetick"></span></span>
					</label>

					<label class="radiobodge turn-around green">
						<span class="label-field">Hinzufügen</span>
						<input type="radio" name="persons[{{i}}][action]" value="new" checked>
						<span class="bodgeradio"><span class="bodgetick"></span></span>
					</label>

					<label class="radiobodge turn-around yellow">
						<span class="label-field">Bearbeiten</span>
						<input type="radio" name="persons[{{i}}][action]" value="edit" disabled>
						<span class="bodgeradio"><span class="bodgetick"></span></span>
					</label>

					<label class="radiobodge turn-around red">
						<span class="label-field">Entfernen</span>
						<input type="radio" name="persons[{{i}}][action]" value="delete" disabled>
						<span class="bodgeradio"><span class="bodgetick"></span></span>
					</label>
				</div>
			</template>
		</div>
		<button type="button" class="new blue" data-action="select">Person(en) hinzufügen</button>
	</div>


	<button type="submit" class="green">Speichern</button>
</form>

<div class="modal multiselectmodal nojs" data-name="persons-select" data-type="Person" data-objectsperpage="1">
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
