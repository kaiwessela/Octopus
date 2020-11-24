<?php include COMPONENT_PATH . 'admin/start.php'; ?>
<main>
	<?php
	$Controller = $PersonController;
	$Object = $Person;
	$singular = 'Person';
	$plural = 'Personen';
	$urlclass = 'persons';

	include COMPONENT_PATH . 'admin/common-1.php';
	?>


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

		<?php if($PersonController->request->action == 'new' || $PersonController->request->action == 'edit'){
			include COMPONENT_PATH . 'admin/imageinput.php';
		} ?>

<?php include COMPONENT_PATH . 'admin/end.php'; ?>
