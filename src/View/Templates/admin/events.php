<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.php'; ?>
		<main>
			<?php if($Event->request->action == 'list'){ ?>
			<h1>Alle Veranstaltungen</h1>
			<?php } else if($Event->request->action == 'show'){ ?>
			<h1>Veranstaltung ansehen</h1>
			<?php } else if($Event->request->action == 'new'){ ?>
			<h1>Neue Veranstaltung erstellen</h1>
			<?php } else if($Event->request->action == 'edit'){ ?>
			<h1>Veranstaltung bearbeiten</h1>
			<?php } else if($Event->request->action == 'delete'){ ?>
			<h1>Veranstaltung löschen</h1>
			<?php } ?>

			<?php if($Event->request->action == 'list'){ ?>
				<a href="<?= $server->url ?>/admin/events/new" class="button new green">Neue Veranstaltung hinzufügen</a>
			<?php } else { ?>
				<a href="<?= $server->url ?>/admin/events" class="button back">Zurück zu allen Veranstaltungen</a>
			<?php } ?>

			<?php if($Event->created()){ ?>
				<div class="message green">
					Veranstaltung <code><?= $Event->object->longid ?></code> wurde erfolgreich hinzugefügt.
				</div>
			<?php } else if($Event->edited()){ ?>
				<div class="message green">
					Veranstaltung <code><?= $Event->object->longid ?></code> wurde erfolgreich bearbeitet.
				</div>
			<?php } else if($Event->deleted()){ ?>
				<div class="message green">
					Veranstaltung <code><?= $Event->object->longid ?></code> wurde erfolgreich gelöscht.
				</div>
			<?php } else if($Event->empty() && $Event->request->action == 'list'){ ?>
				<div class="message yellow">
					Es sind noch keine Veranstaltungen vorhanden.
				</div>
			<?php } else if($Event->unprocessable()){ ?>
				<div class="message red">
					Die hochgeladenen Daten sind fehlerhaft.
				</div>
				<ul>
				<?php foreach($Post->errors['import'] as $error){ ?>
					<li><code><?= $error['field'] ?></code>: <?= $error['type'] ?></li>
				<?php } ?>
				</ul>
			<?php } else if($Event->internal_error()){ ?>
				<div class="message red">
					Es ist ein interner Serverfehler aufgetreten.
				</div>
			<?php } ?>

			<?php if($Event->request->action != 'list' && $Event->request->action != 'new'){ ?>
			<div>
				<?php if($Event->request->action != 'show'){ ?>
				<a class="button blue" href="<?= $server->url ?>/admin/events/<?= $Event->object->id ?>">Ansehen</a>
				<?php } ?>

				<?php if($Event->request->action != 'edit'){ ?>
				<a class="button yellow" href="<?= $server->url ?>/admin/events/<?= $Event->object->id ?>/edit">Bearbeiten</a>
				<?php } ?>

				<?php if($Event->request->action != 'delete'){ ?>
				<a class="button red" href="<?= $server->url ?>/admin/events/<?= $Event->object->id ?>/delete">Löschen</a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if($Event->request->action == 'list' && $Event->found()){ ?>
				<?php
				$pagination = $Event->pagination;
				include COMPONENT_PATH . 'admin/pagination.php';
				?>

				<?php foreach($Event->objects as $obj){ ?>
				<article>
					<code><?= $obj->longid ?></code>
					<h2><?= $obj->title ?></h2>
					<small><?= $obj->location ?></small>
					<small><?= $obj->timestamp->datetime_long ?></small>
					<div>
						<a class="button blue"
							href="<?= $server->url ?>/admin/events/<?= $obj->id ?>">Ansehen</a>
						<a class="button yellow"
							href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/edit">Bearbeiten</a>
						<a class="button red"
							href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/delete">Löschen</a>
					</div>
				</article>
				<?php } ?>
			<?php } ?>

			<?php if($Event->request->action == 'show' && $Event->found()){ ?>
				<?php $obj = $Event->object; ?>
				<article>
					<code><?= $obj->longid ?></code>
					<h1><?= $obj->title ?></h1>
					<p><?= $obj->timestamp->datetime_long ?></p>
					<p><?= $obj->location ?></p>
				</article>
			<?php } ?>

			<?php if(($Event->request->action == 'edit' && !$Event->edited()) || ($Event->request->action == 'new' && !$Event->created())){ ?>
				<?php $obj = $Event->object; ?>
				<form action="#" method="post">

					<?php if($Event->request->action == 'new'){ ?>
					<label for="longid">
						<span class="name">Veranstaltungs-ID</span>
						<span class="conditions">
							erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
							Bindestriche (-)
						</span>
						<span class="infos">
							Die Veranstaltungs-ID wird in der URL verwendet und entspricht meistens dem Titel.
						</span>
					</label>
					<input type="text" id="longid" name="longid" value="<?= $obj->longid ?>" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" autocomplete="off">
					<?php } else { ?>
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">
					<?php } ?>

					<label for="title">
						<span class="name">Titel</span>
						<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
						<span class="infos">Der Titel der Veranstaltung.</span>
					</label>
					<input type="text" id="title" name="title" value="<?= $obj->title ?>" required size="40" maxlength="50">

					<label for="organisation">
						<span class="name">Organisation</span>
						<span class="conditions">erforderlich, 1 bis 40 Zeichen</span>
						<span class="infos">Die Organisation, die zur Veranstaltung eingeladen hat.</span>
					</label>
					<input type="text" id="organisation" name="organisation" value="<?= $obj->organisation ?>" required size="30" maxlength="40">

					<label for="timeinput">
						<span class="name">Datum und Uhrzeit</span>
						<span class="conditions">erforderlich</span>
						<span class="infos">Datum und Uhrzeit der Veranstaltung.</span>
					</label>
					<input type="number" class="timeinput" id="timestamp" name="timestamp" value="<?= $obj->timestamp ?>" required size="10">

					<label for="location">
						<span class="name">Ort</span>
						<span class="conditions">optional, bis zu 60 Zeichen</span>
						<span class="infos">Der Ort der Veranstaltung.</span>
					</label>
					<input type="text" id="location" name="location" value="<?= $obj->location ?>" size="40" maxlength="60">

					<label for="description">
						<span class="name">Beschreibung</span>
						<span class="conditions">optional</span>
						<span class="infos">Beschreibung der Veranstaltung.</span>
					</label>
					<textarea id="description" name="description" rows="5" cols="60"><?= $obj->description ?></textarea>

					<label for="cancelled">
						<span class="name">Absage</span>
						<span class="conditions">optional</span>
						<span class="description">Ist die Veranstaltung abgesagt?
					</label>
					<label class="checkbodge turn-around">
						<span class="label-field">Ja</span>
						<input type="checkbox" id="cancelled" name="cancelled" value="true" <?php if($obj->cancelled){ echo 'checked'; } ?>>
						<span class="bodgecheckbox">
							<span class="bodgetick">
								<span class="bodgetick-down"></span>
								<span class="bodgetick-up"></span>
							</span>
						</span>
					</label>

					<button type="submit" class="green">Speichern</button>
				</form>
			<?php } ?>

			<?php if($Event->request->action == 'delete' && !$Event->deleted()){ ?>
				<?php $obj = $Event->object; ?>
				<p>Veranstaltung <code><?= $obj->longid ?></code> löschen?</p>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<button type="submit" class="red">Löschen</button>
				</form>
			<?php } ?>

		</main>
		<?php include COMPONENT_PATH . 'admin/footer.php'; ?>

		<?php if($Event->request->action == 'new' || $Event->request->action == 'edit'){
			include COMPONENT_PATH . 'admin/timeinput.php';
		} ?>

		<script src="<?= $server->url ?>/resources/js/admin/validate.js"></script>
	</body>
</html>
