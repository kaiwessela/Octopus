<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.comp.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.comp.php'; ?>
		<main>
			<?php if($Event->action == 'list'){ ?>
			<h1>Alle Veranstaltungen</h1>
			<?php } else if($Event->action == 'show'){ ?>
			<h1>Veranstaltung ansehen</h1>
			<?php } else if($Event->action == 'new'){ ?>
			<h1>Neue Veranstaltung erstellen</h1>
			<?php } else if($Event->action == 'edit'){ ?>
			<h1>Veranstaltung bearbeiten</h1>
			<?php } else if($Event->action == 'delete'){ ?>
			<h1>Veranstaltung löschen</h1>
			<?php } ?>

			<?php if(!$Event->action == 'list'){ ?>
			<a href="<?= $server->url ?>/admin/pages" class="button">&laquo; Zurück zu allen Veranstaltungen</a>
			<?php } ?>

			<?php foreach($Event->errors as $error){ ?>
			<span class="message error">

			</span>
			<p>Details: <span class="code"></span></p>
			<?php } ?>

			<?php if($Event->action == 'list'){ ?>
				<?php if(empty($Event->objects)){ ?>
				<span class="message warning">
					Es sind noch keine Veranstaltungen vorhanden.
				</span>

				<?php } else { foreach($Event->objects as $obj){ ?>
				<article class="event preview">
					<p class="longid"><?= $obj->longid ?></p>
					<h3 class="name"><?= $obj->title ?></h3>
					<p class="timestamp"><?= date('d.m.Y, H:i \U\h\r', $obj->timestamp) ?></p>
					<div>
						<a href="<?= $server->url ?>/admin/events/<?= $obj->id ?>" class="view">Ansehen</a>
						<a href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</div>
				</article>
				<?php }} ?>
			<?php } ?>

			<?php if($Event->action == 'show'){ ?>
				<?php $obj = $Event->object; ?>
				<article class="event">
					<p>
						<a href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= $server->url ?>/admin/events/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</p>
					<p class="longid"><?= $obj->longid ?></p>
					<h1 class="title"><?= $obj->title ?></h1>
					<p class="timestamp"><?= $obj->timestamp ?></p>
					<p class="location"><?= $obj->location ?></p>
				</article>
			<?php } ?>

			<?php if($Event->action == 'edit' && !$Event->action->completed()){ ?>
				<?php $obj = $Event->object; ?>
				<form action="#" method="post">
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">

					<label for="title">
						<span class="name">Titel</span>
						<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
						<span class="description">Der Titel der Veranstaltung.</span>
					</label>
					<input type="text" id="title" class="title" name="title" value="<?= $obj->title ?>" required>

					<label for="organisation">
						<span class="name">Organisation</span>
						<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
						<span class="description">Die Organisation, die zur Veranstaltung eingeladen hat.</span>
					</label>
					<input type="text" id="organisation" class="organisation" name="organisation" value="<?= $obj->organisation ?>" required>

					<label>
						<span class="name">Datum und Uhrzeit</span>
						<span class="requirements">erforderlich</span>
						<span class="description">Datum und Uhrzeit der Veranstaltung.</span>
					</label>
					<div id="timeinput" data-value="<?= $obj->timestamp ?>" data-name="timestamp"></div>

					<label for="location">
						<span class="name">Ort</span>
						<span class="requirements">optional, bis zu 128 Zeichen</span>
						<span class="description">Der Ort der Veranstaltung.</span>
					</label>
					<input type="text" id="location" class="location" name="location" value="<?= $obj->location ?>">

					<label for="description">
						<span class="name">Beschreibung</span>
						<span class="requirements">optional</span>
						<span class="description">Beschreibung der Veranstaltung.</span>
					</label>
					<textarea id="description" name="description" class="description"><?= $obj->description ?></textarea>

					<label for="cancelled">
						<span class="name">Absage</span>
						<span class="requirements">optional</span>
						<span class="description">Ist die Veranstaltung abgesagt?
					</label>
					<label class="checkbodge turn-around">
						<span class="label-field">Ja</span>
						<input type="checkbox" id="cancelled" name="cancelled" class="cancelled" value="true" <?php if($obj->cancelled){ echo 'checked'; } ?>>
						<span class="bodgecheckbox">
							<span class="bodgetick">
								<span class="bodgetick-down"></span>
								<span class="bodgetick-up"></span>
							</span>
						</span>
					</label>

					<input type="submit" value="Speichern">
				</form>
			<?php } ?>

			<?php if($Event->action == 'new' && !$Event->action->completed()){ ?>
				<?php $obj = $Event->object; ?>
				<form action="#" method="post">
					<label for="longid">
						<span class="name">Veranstaltungs-ID</span>
						<span class="requirements">
							erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
							Bindestriche (-)
						</span>
						<span class="description">
							Die Veranstaltungs-ID wird in der URL verwendet und entspricht meistens dem Titel.
						</span>
					</label>
					<input type="text" id="longid" class="longid" name="longid" required>

					<label for="title">
						<span class="name">Titel</span>
						<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
						<span class="description">Der Titel der Veranstaltung.</span>
					</label>
					<input type="text" id="title" class="title" name="title" required>

					<label for="organisation">
						<span class="name">Organisation</span>
						<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
						<span class="description">Die Organisation, die zur Veranstaltung eingeladen hat.</span>
					</label>
					<input type="text" id="organisation" class="organisation" name="organisation" required>

					<label>
						<span class="name">Datum und Uhrzeit</span>
						<span class="requirements">erforderlich</span>
						<span class="description">Datum und Uhrzeit der Veranstaltung.</span>
					</label>
					<div id="timeinput" data-value="" data-name="timestamp"></div>

					<label for="location">
						<span class="name">Ort</span>
						<span class="requirements">optional, bis zu 128 Zeichen</span>
						<span class="description">Der Ort der Veranstaltung.</span>
					</label>
					<input type="text" id="location" class="location" name="location">

					<label for="description">
						<span class="name">Beschreibung</span>
						<span class="requirements">optional</span>
						<span class="description">Beschreibung der Veranstaltung.</span>
					</label>
					<textarea id="description" name="description" class="description"></textarea>

					<label>
						<span class="name">Absage</span>
						<span class="requirements">optional</span>
						<span class="description">Ist die Veranstaltung abgesagt?
					</label>
					<label class="checkbodge turn-around">
						<span class="label-field">Ja</span>
						<input type="checkbox" id="cancelled" name="cancelled" class="cancelled" value="true">
						<span class="bodgecheckbox">
							<span class="bodgetick">
								<span class="bodgetick-down"></span>
								<span class="bodgetick-up"></span>
							</span>
						</span>
					</label>


					<input type="submit" value="Speichern">
				</form>
			<?php } ?>

			<?php if($Event->action == 'delete' && !$Event->action->completed()){ ?>
				<?php $obj = $Event->object; ?>
				<p>Veranstaltung <span class="code"><?= $obj->longid ?></span> löschen?</p>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<input type="submit" value="Löschen">
				</form>
			<?php } ?>

			<?php if($Event->action == 'new' || $Event->action == 'edit'){
				include COMPONENT_PATH . 'admin/timeinput.comp.php';
			} ?>

		</main>
	</body>
</html>
