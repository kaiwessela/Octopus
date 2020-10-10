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

			<?php if($Person->action == 'list'){ ?>
				<a href="<?= $server->url ?>/admin/persons/new" class="button new green">Neue Person hinzufügen</a>
			<?php } else { ?>
				<a href="<?= $server->url ?>/admin/persons" class="button back">Zurück zu allen Personen</a>
			<?php } ?>

			<?php foreach($Person->errors as $error){ ?>
			<div class="message red">
				<?= $error->getMessage(); ?>
			</div>
			<?php } ?>

			<?php if($Person->action != 'list' && $Person->action != 'new'){ ?>
			<div>
				<?php if($Person->action != 'show'){ ?>
				<a class="button blue" href="<?= $server->url ?>/admin/persons/<?= $Person->object->id ?>">Ansehen</a>
				<?php } ?>

				<?php if($Person->action != 'edit'){ ?>
				<a class="button yellow" href="<?= $server->url ?>/admin/persons/<?= $Person->object->id ?>/edit">Bearbeiten</a>
				<?php } ?>

				<?php if($Person->action != 'delete'){ ?>
				<a class="button red" href="<?= $server->url ?>/admin/persons/<?= $Person->object->id ?>/delete">Löschen</a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if(($Person->action == 'new' || $Person->action == 'edit') && $Person->action->completed()){ ?>
			<div class="message green">
				Person <code><?= $Person->object->longid ?></code> wurde erfolgreich gespeichert.
			</div>
			<?php } ?>

			<?php if($Person->action == 'list'){ ?>
				<?php
				$pagination = $Person->pagination;
				include COMPONENT_PATH . 'admin/pagination.comp.php';
				?>

				<?php if(empty($Person->objects)){ ?>
				<div class="message yellow">
					Es sind noch keine Personen vorhanden.
				</div>

				<?php } else { foreach($Person->objects as $obj){ ?>
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
				<?php }} ?>
			<?php } ?>

			<?php if($Person->action == 'show'){ ?>
				<?php $obj = $Person->object; ?>
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

			<?php if($Person->action == 'edit' && !$Person->action->completed()){ ?>
				<?php $obj = $Person->object; ?>
				<form action="#" method="post">
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">

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

			<?php if($Person->action == 'new' && !$Person->action->completed()){ ?>
				<?php $obj = $Person->object; ?>
				<form action="#" method="post">
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
					<input type="text" id="longid" name="longid" required size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]$" autocomplete="off">

					<label for="name">
						<span class="name">Name</span>
						<span class="conditions">erforderlich, 1 bis 50 Zeichen</span>
						<span class="infos">
							Der vollständige Name der Person.
						</span>
					</label>
					<input type="text" id="name" class="name" name="name" required size="30" maxlength="50">

					<label>
						<span class="name">Profilbild</span>
						<span class="conditions">optional</span>
						<span class="infos">
							Das Profilbild sollte ein Portrait der Person sein.
						</span>
					</label>
					<input type="text" class="imageinput" id="image_id" name="image_id" size="8" minlength="8" maxlength="8">

					<button type="submit" class="green">Speichern</button>
				</form>
			<?php } ?>

			<?php if($Person->action == 'delete' && !$Person->action->completed()){ ?>
				<?php $obj = $Person->object; ?>
				<p>
					<a href="<?= $server->url ?>/posts/<?= $obj->longid ?>">Blogansicht</a>
					<a href="<?= $server->url ?>/admin/posts/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
				</p>
				<p>Person <code><?= $obj->longid ?></code> löschen?</p>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<button type="submit" class="red">Löschen</button>
				</form>
			<?php } ?>

			<?php if($Person->action == 'new' || $Person->action == 'edit'){
				include COMPONENT_PATH . 'admin/imageinput.comp.php';
			} ?>

			<script src="<?= $server->url ?>/resources/js/admin/validate.js"></script>
		</main>
	</body>
</html>
