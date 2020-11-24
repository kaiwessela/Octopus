<?php if($Controller->request->action == 'list'){ ?>
<h1>Alle <?= $plural ?></h1>
<?php } else if($Controller->request->action == 'show'){ ?>
<h1><?= $singular ?> ansehen</h1>
<?php } else if($Controller->request->action == 'new'){ ?>
<h1><?= $singular ?> hinzufügen</h1>
<?php } else if($Controller->request->action == 'edit'){ ?>
<h1><?= $singular ?> bearbeiten</h1>
<?php } else if($Controller->request->action == 'delete'){ ?>
<h1><?= $singular ?> löschen</h1>
<?php } ?>

<?php if($Controller->request->action == 'list'){ ?>
	<a href="<?= $server->url ?>/admin/<?= $urlclass ?>/new" class="button new green"><?= $singular ?> hinzufügen</a>
<?php } else { ?>
	<a href="<?= $server->url ?>/admin/<?= $urlclass ?>" class="button back">Zurück zu allen <?= $plural ?></a>
<?php } ?>

<?php if($Controller->created()){ ?>
	<div class="message green">
		<?= $singular ?> <code><?= $Object->longid ?></code> wurde erfolgreich hinzugefügt.
	</div>
<?php } else if($Controller->edited()){ ?>
	<div class="message green">
		<?= $singular ?> <code><?= $Object->longid ?></code> wurde erfolgreich bearbeitet.
	</div>
<?php } else if($Controller->deleted()){ ?>
	<div class="message green">
		<?= $singular ?> <code><?= $Object->longid ?></code> wurde erfolgreich gelöscht.
	</div>
<?php } else if($Controller->empty() && $Controller->request->action == 'list'){ ?>
	<div class="message yellow">
		Es sind noch keine <?= $plural ?> vorhanden.
	</div>
<?php } else if($Controller->unprocessable()){ ?>
	<div class="message red">
		Die hochgeladenen Daten sind fehlerhaft.
	</div>
	<ul>
	<?php foreach($Controller->errors['import'] as $error){ ?>
		<li><code><?= $error['field'] ?></code>: <?= $error['type'] ?></li>
	<?php } ?>
	</ul>
<?php } else if($Controller->internal_error()){ ?>
	<div class="message red">
		Es ist ein interner Serverfehler aufgetreten.
	</div>
<?php } ?>

<?php if($Controller->request->action != 'list' && $Controller->request->action != 'new'){ ?>
<div>
	<?php if($Controller->request->action != 'show'){ ?>
	<a class="button blue" href="<?= $server->url ?>/admin/<?= $urlclass ?>/<?= $Object->id ?>">Ansehen</a>
	<?php } ?>

	<?php if($Controller->request->action != 'edit'){ ?>
	<a class="button yellow" href="<?= $server->url ?>/admin/<?= $urlclass ?>/<?= $Object->id ?>/edit">Bearbeiten</a>
	<?php } ?>

	<?php if($Controller->request->action != 'delete'){ ?>
	<a class="button red" href="<?= $server->url ?>/admin/<?= $urlclass ?>/<?= $Object->id ?>/delete">Löschen</a>
	<?php } ?>
</div>
<?php } ?>
