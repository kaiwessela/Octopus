<?php include COMPONENT_PATH . 'admin/start.php'; ?>
<main>
	<?php
	$Controller = $GroupController;
	$Object = $Group;
	$singular = 'Gruppe';
	$plural = 'Gruppen';
	$urlclass = 'groups';

	include COMPONENT_PATH . 'admin/common-1.php';
	?>

	<?php if($Controller->request->action == 'list' && $Controller->found()){ ?>
		<?php
		$pagination = $Controller->pagination;
		include COMPONENT_PATH . 'admin/pagination.php';
		?>

		<?php foreach($Object as $obj){ ?>
		<article>
			<code><?= $obj->longid ?></code>
			<h2><?= $obj->name ?></h2>
			<div>
				<a class="button blue"
					href="<?= $server->url ?>/admin/groups/<?= $obj->id ?>">Ansehen</a>
				<a class="button yellow"
					href="<?= $server->url ?>/admin/groups/<?= $obj->id ?>/edit">Bearbeiten</a>
				<a class="button red"
					href="<?= $server->url ?>/admin/groups/<?= $obj->id ?>/delete">Löschen</a>
			</div>
		</article>
		<?php } ?>
	<?php } ?>

	<?php if($Controller->request->action == 'show' && $Controller->found()){ ?>
		<?php $obj = $Object; ?>
		<article>
			<code><?= $obj->longid ?></code>
			<h1><?= $obj->name ?></h1>
			<p><?= $obj->description ?></p>

			<h2>Mitglieder:</h2>
			<ul>
			<?php foreach($obj->persons as $person){ ?>
				<li><code><?= $person->longid ?></code> <strong><?= $person->name ?></strong></li>
			<?php } ?>
		</ul>
		</article>
	<?php } ?>

	<?php if(($Controller->request->action == 'edit' && !$Controller->edited()) || ($Controller->request->action == 'new' && !$Controller->created())){ ?>
		<?php $obj = $Object; ?>
		<form action="#" method="post">

			<?php if($Controller->request->action == 'new'){ ?>
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
			<input type="text" id="longid" name="longid" value="<?= $obj->longid ?>" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" autocomplete="off">
			<?php } else { ?>
			<input type="hidden" name="id" value="<?= $obj->id ?>">
			<input type="hidden" name="longid" value="<?= $obj->longid ?>">
			<?php } ?>

			<label for="name">
				<span class="name">Name</span>
				<span class="conditions">erforderlich, 1 bis 30 Zeichen</span>
				<span class="infos">
					Der Name der Gruppe.
				</span>
			</label>
			<input type="text" id="name" name="name" value="<?= $obj->name ?>" size="30" required maxlength="30">

			<label for="description">
				<span class="name">Beschreibung</span>
				<span class="conditions">optional</span>
				<span class="infos">
					Die Beschreibung der Gruppe.
				</span>
			</label>
			<textarea id="description" name="description" cols="50" rows="3"><?= $obj->description ?></textarea>

			<label>
				<span class="name">Mitglieder</span>
				<span class="conditions">optional</span>
			</label>
			<div class="pseudoinput relationlist">
				<div class="objectbox" data-count="<?= count($Object->persons) + 1 ?>">
				<?php foreach($obj->persons as $i => $person){ ?>
					<div class="listitem">
						<p><strong><?= $person->name ?></strong> <code><?= $person->longid ?></code></p>
						<input type="hidden" name="persons[<?= $i ?>][id]" value="<?= $Object->relations[$i]['id'] ?>">
						<input type="hidden" name="persons[<?= $i ?>][person_id]" value="<?= $person->id ?>">
						<input type="hidden" name="persons[<?= $i ?>][group_id]" value="<?= $Object->id ?>">

						<label for="persons-<?= $i ?>-role">Funktion:</label>
						<input type="text" name="persons[<?= $i ?>][role]" id="persons-<?= $i ?>-role" size="30" maxlength="40" value="<?= $Object->relations[$i]['role'] ?>">

						<label for="persons-<?= $i ?>-number">Nummer:</label>
						<input type="number" name="persons[<?= $i ?>][number]" id="persons-<?= $i ?>-number" value="<?= $Object->relations[$i]['number'] ?>">

						<label class="radiobodge turn-around blue">
							<span class="label-field">Keine Änderung</span>
							<input type="radio" name="persons[<?= $i ?>][action]" value="ignore" checked>
							<span class="bodgeradio">
								<span class="bodgetick"></span>
							</span>
						</label>

						<label class="radiobodge turn-around green">
							<span class="label-field">Hinzufügen</span>
							<input type="radio" name="persons[<?= $i ?>][action]" value="new" disabled>
							<span class="bodgeradio">
								<span class="bodgetick"></span>
							</span>
						</label>

						<label class="radiobodge turn-around yellow">
							<span class="label-field">Bearbeiten</span>
							<input type="radio" name="persons[<?= $i ?>][action]" value="edit">
							<span class="bodgeradio">
								<span class="bodgetick"></span>
							</span>
						</label>

						<label class="radiobodge turn-around red">
							<span class="label-field">Entfernen</span>
							<input type="radio" name="persons[<?= $i ?>][action]" value="delete">
							<span class="bodgeradio">
								<span class="bodgetick"></span>
							</span>
						</label>
					</div>
				<?php } ?>
				</div>
				<template>
					<div class="listitem">
						<p><strong>{{name}}</strong> <code>{{longid}}</code></p>
						<input type="hidden" name="persons[{{i}}][person_id]" value="{{id}}">
						<input type="hidden" name="persons[{{i}}][group_id]" value="<?= $Object->id ?>">

						<label for="persons-{{i}}-role">Funktion:</label>
						<input type="text" name="persons[{{i}}][role]" id="persons-{{i}}-role" size="30" maxlength="40">

						<label for="persons-{{i}}-number">Nummer:</label>
						<input type="number" name="persons[{{i}}][number]" id="persons-{{i}}-number">

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
				<button type="button" class="new blue" data-action="open" data-modal="addperson">Person hinzufügen</button>
			</div>

			<button type="submit" class="green">Speichern</button>
		</form>
	<?php } ?>

	<?php if($Controller->request->action == 'delete' && !$Controller->deleted()){ ?>
		<?php $obj = $Object; ?>
		<p>Gruppe <code><?= $obj->longid ?></code> löschen?</p>
		<form action="#" method="post">
			<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
			<button type="submit" class="red">Löschen</button>
		</form>
	<?php } ?>

	<div class="modal selectmodal" data-name="addperson">
		<div class="box">
			<h2>Person auswählen</h2>
			<form action="#" method="GET">
				<div class="objectbox"></div>
				<template>
					<article>
						<h3>{{name}}</h3>
						<code>{{longid}}</code>
						<label class="radiobodge turn-around blue">
							<span class="label-field">Auswählen</span>
							<input type="radio" name="result" value="{{id}}">
							<span class="bodgeradio"><span class="bodgetick"></span></span>
						</label>
					</article>
				</template>
				<button type="button" data-action="close" class="red">Schließen</button>
				<button type="submit" data-action="submit" class="blue">Auswählen</button>
			</form>
		</div>
	</div>

</main>

<script src="<?= $server->url ?>/resources/js/newadmin/person.js"></script>
<script src="<?= $server->url ?>/resources/js/newadmin/modal.js"></script>
<script src="<?= $server->url ?>/resources/js/newadmin/selectmodal.js"></script>
<script src="<?= $server->url ?>/resources/js/newadmin/invoke.js"></script>
<script>
	modals['addperson'].type = 'person';
	modals['addperson'].onSubmit = () => {
		var count = Number(document.querySelector('.relationlist .objectbox').getAttribute('data-count'));
		count++;
		document.querySelector('.relationlist .objectbox').setAttribute('data-count', count);

		var newcontent = modals['addperson'].valueObject.replace(document.querySelector('.relationlist template').innerHTML);
		var newelem = document.createElement('div');
		newelem.innerHTML = newcontent.replace(/{{i}}/g, count);
		document.querySelector('.relationlist .objectbox').appendChild(newelem.firstElementChild);
	}
</script>

<?php include COMPONENT_PATH . 'admin/end.php'; ?>
