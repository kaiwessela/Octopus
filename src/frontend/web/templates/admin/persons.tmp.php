<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.comp.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.comp.php'; ?>
		<main>
			<?php if($Person->action == 'list'){ ?>
			<h1>Alle Personen</h1>
			<?php } else if($Person->action == 'show'){ ?>
			<h1>Person ansehen</h1>
			<?php } else if($Person->action == 'new'){ ?>
			<h1>Neue Person hinzufügen</h1>
			<?php } else if($Person->action == 'edit'){ ?>
			<h1>Person bearbeiten</h1>
			<?php } else if($Person->action == 'delete'){ ?>
			<h1>Person löschen</h1>
			<?php } ?>

			<?php if(!$Person->action == 'list'){ ?>
			<a href="<?= $server->url ?>/admin/persons" class="button">&laquo; Zurück zu allen Personen</a>
			<?php } ?>

			<?php foreach($Person->errors as $error){ ?>
			<span class="message error">

			</span>
			<p>Details: <span class="code"></span></p>
			<?php } ?>

			<?php if($Person->action == 'list'){ ?>
				<?php foreach($Person->objects as $obj){ ?>
				<article class="person preview">
					<p class="longid"><?= $obj->longid ?></p>
					<h3 class="name"><?= $obj->name ?></h3>
					<div>
						<a href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>" class="view">Ansehen</a>
						<a href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= $server->url ?>/admin/persons/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</div>
				</article>
				<?php } ?>
			<?php } ?>

			<?php if($Person->action == 'show'){ ?>
				<?php $obj = $Person->object; ?>
				<article class="person">
					<p>
						<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= Config::SERVER_URL ?>/admin/persons/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</p>
					<p class="longid"><?= $obj->longid ?></p>
					<h1 class="name"><?= $obj->name ?></h1>

					<?php if($obj->image){ ?>
					<div>
						Profilbild: <span class="code"><?= $obj->image->longid ?></span>
						<a href="<?= Config::SERVER_URL ?>/admin/images/<?= $obj->image->longid ?>">ansehen</a>
						<img src="<?= Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . $obj->image->longid . '.'
							. $obj->image->extension ?>?size=original" alt="<?= $obj->image->description ?>">
					</div>
					<?php } ?>
				</article>
			<?php } ?>

			<?php if($Person->action == 'edit' && !$Person->action->completed()){ ?>
				<?php $obj = $Person->object; ?>
				<form action="#" method="post">
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">

					<label for="name">
						<span class="name">Name</span>
						<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
						<span class="description">
							Der vollständige Name der Person.
						</span>
					</label>
					<input type="text" id="name" class="name" name="name" value="<?= $obj->name ?>" required>

					<label>
						<span class="name">Profilbild</span>
						<span class="requirements">optional</span>
						<span class="description">
							Das Profilbild sollte ein Portrait der Person sein.
						</span>
					</label>
					<div id="imageinput" data-value="<?= $obj->image->id ?? '' ?>" data-longid="<?= $obj->image->longid ?? '' ?>" data-name="image_id"
						data-extension="<?= $obj->image->extension ?>"></div>

					<input type="submit" value="Speichern">
				</form>
			<?php } ?>

			<?php if($Person->action == 'new' && !$Person->action->completed()){ ?>
				<?php $obj = $Person->object; ?>
				<form action="#" method="post">
					<label for="longid">
						<span class="name">Personen-ID</span>
						<span class="requirements">
							erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
							Bindestriche (-)
						</span>
						<span class="description">
							Die Personen-ID wird in der URL verwendet und entspricht meistens dem Namen.
						</span>
					</label>
					<input type="text" id="longid" class="longid" name="longid" required>

					<label for="name">
						<span class="name">Name</span>
						<span class="requirements">erforderlich, 1 bis 64 Zeichen</span>
						<span class="description">
							Der vollständige Name der Person.
						</span>
					</label>
					<input type="text" id="name" class="name" name="name" required>

					<label>
						<span class="name">Profilbild</span>
						<span class="requirements">optional</span>
						<span class="description">
							Das Profilbild sollte ein Portrait der Person sein.
						</span>
					</label>
					<div id="imageinput" data-value="" data-longid="" data-name="image_id"></div>

					<input type="submit" value="Speichern">
				</form>
			<?php } ?>

			<?php if($Person->action == 'delete' && !$Person->action->completed()){ ?>
				<?php $obj = $Person->object; ?>
				<p>
					<a href="<?= $server->url ?>/posts/<?= $obj->longid ?>">Blogansicht</a>
					<a href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
				</p>
				<p>Person <span class="code"><?= $obj->longid ?></span> löschen?</p>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<input type="submit" value="Löschen">
				</form>
			<?php } ?>

			<?php if($Person->action == 'new' || $Person->action == 'edit'){
				include COMPONENT_PATH . 'admin/imageinput.comp.php';
			} ?>

		</main>
	</body>
</html>
