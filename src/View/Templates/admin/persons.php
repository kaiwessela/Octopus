<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.php'; ?>
		<main>
			<?php if($PersonController->request->action == 'list'){ ?>
			<h1>Alle Personen</h1>
			<?php } else if($PersonController->request->action == 'show'){ ?>
			<h1>Person ansehen</h1>
			<?php } else if($PersonController->request->action == 'new'){ ?>
			<h1>Neue Person hinzufügen</h1>
			<?php } else if($PersonController->request->action == 'edit'){ ?>
			<h1>Person bearbeiten</h1>
			<?php } else if($PersonController->request->action == 'delete'){ ?>
			<h1>Person löschen</h1>
			<?php } ?>

			<?php if($PersonController->request->action == 'list'){ ?>
				<a href="<?= $server->url ?>/admin/persons/new" class="button new green">Neue Person hinzufügen</a>
			<?php } else { ?>
				<a href="<?= $server->url ?>/admin/persons" class="button back">Zurück zu allen Personen</a>
			<?php } ?>

			<?php if($PersonController->created()){ ?>
				<div class="message green">
					Person <code><?= $Person->longid ?></code> wurde erfolgreich hinzugefügt.
				</div>
			<?php } else if($PersonController->edited()){ ?>
				<div class="message green">
					Person <code><?= $Person->longid ?></code> wurde erfolgreich bearbeitet.
				</div>
			<?php } else if($PersonController->deleted()){ ?>
				<div class="message green">
					Person <code><?= $Person->longid ?></code> wurde erfolgreich gelöscht.
				</div>
			<?php } else if($PersonController->empty() && $PersonController->request->action == 'list'){ ?>
				<div class="message yellow">
					Es sind noch keine Personen vorhanden.
				</div>
			<?php } else if($PersonController->unprocessable()){ ?>
				<div class="message red">
					Die hochgeladenen Daten sind fehlerhaft.
				</div>
				<ul>
				<?php foreach($PersonController->errors['import'] as $error){ ?>
					<li><code><?= $error['field'] ?></code>: <?= $error['type'] ?></li>
				<?php } ?>
				</ul>
			<?php } else if($PersonController->internal_error()){ ?>
				<div class="message red">
					Es ist ein interner Serverfehler aufgetreten.
				</div>
			<?php } ?>

			<?php if($PersonController->request->action != 'list' && $PersonController->request->action != 'new'){ ?>
			<div>
				<?php if($PersonController->request->action != 'show'){ ?>
				<a class="button blue" href="<?= $server->url ?>/admin/persons/<?= $Person->id ?>">Ansehen</a>
				<?php } ?>

				<?php if($PersonController->request->action != 'edit'){ ?>
				<a class="button yellow" href="<?= $server->url ?>/admin/persons/<?= $Person->id ?>/edit">Bearbeiten</a>
				<?php } ?>

				<?php if($PersonController->request->action != 'delete'){ ?>
				<a class="button red" href="<?= $server->url ?>/admin/persons/<?= $Person->id ?>/delete">Löschen</a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if($PersonController->request->action == 'list' && $PersonController->found()){ ?>
				<?php
				$pagination = $PersonController->pagination;
				include COMPONENT_PATH . 'admin/pagination.php';
				?>

				<?php foreach($Person as $obj){ ?>
				<article>
					<code><?= $obj->longid ?></code>
					<h2><?= $obj->name ?></h2>
					<div>
						<a class="button blue"
							href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>">Ansehen</a>
						<a class="button yellow"
							href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>/edit">Bearbeiten</a>
						<a class="button red"
							href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>/delete">Löschen</a>
					</div>
				</article>
				<?php } ?>
			<?php } ?>

			<?php if($PersonController->request->action == 'show' && $PersonController->found()){ ?>
				<?php $obj = $Person; ?>
				<article>
					<code><?= $obj->longid ?></code>
					<h1 class="name"><?= $obj->name ?></h1>

					<?php if($obj->image){ ?>
					<div>
						Profilbild: <code><?= $obj->image->longid ?></code>
						<a href="<?= $server->url ?>/admin/images/<?= $obj->image->longid ?>">ansehen</a>
						<img src="<?= $server->url . $server->dyn_img_path . $obj->image->longid . '.'
							. $obj->image->extension ?>?size=original" alt="<?= $obj->image->description ?>">
					</div>
					<?php } ?>
				</article>
			<?php } ?>

			<?php if(($PersonController->request->action == 'edit' && !$PersonController->edited()) || ($PersonController->request->action == 'new' && !$PersonController->created())){ ?>
				<?php $obj = $Person; ?>
				<form action="#" method="post">

					<?php if($PersonController->request->action == 'new'){ ?>
					<label for="longid">
						<span class="name">Personen-ID</span>
						<span class="conditions">
							erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
							Bindestriche (-)
						</span>
						<span class="infos">
							Die Personen-ID wird in der URL verwendet und entspricht meistens dem Namen.
						</span>
					</label>
					<input type="text" id="longid" name="longid" value="<?= $obj->longid ?>" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" autocomplete="off">
					<?php } else { ?>
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">
					<?php } ?>

					<label for="name">
						<span class="name">Name</span>
						<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
						<span class="infos">
							Der vollständige Name der Person.
						</span>
					</label>
					<input type="text" id="name" name="name" value="<?= $obj->name ?>" required size="30" maxlength="50">

					<label for="image_id">
						<span class="name">Profilbild</span>
						<span class="conditions">optional</span>
						<span class="infos">
							Das Profilbild sollte ein Portrait der Person sein.
						</span>
					</label>
					<input type="text" class="imageinput" id="image_id" name="image_id" value="<?= $obj->image->id ?? '' ?>" size="8" minlength="8" maxlength="8">

					<button type="submit" class="green">Speichern</button>
				</form>
			<?php } ?>

			<?php if($PersonController->request->action == 'delete' && !$PersonController->deleted()){ ?>
				<?php $obj = $Person; ?>
				<p>Person <code><?= $obj->longid ?></code> löschen?</p>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<button type="submit" class="red">Löschen</button>
				</form>
			<?php } ?>

		</main>
		<?php include COMPONENT_PATH . 'admin/footer.php'; ?>

		<?php if($PersonController->request->action == 'new' || $PersonController->request->action == 'edit'){
			include COMPONENT_PATH . 'admin/imageinput.php';
		} ?>

		<script src="<?= $server->url ?>/resources/js/admin/validate.js"></script>
	</body>
</html>
