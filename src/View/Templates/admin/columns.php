<?php include COMPONENT_PATH . 'admin/start.php'; ?>
<main>
	<?php
	$Controller = $ColumnController;
	$Object = $Column;
	$singular = 'Rubrik';
	$plural = 'Rubriken';
	$urlclass = 'columns';

	include COMPONENT_PATH . 'admin/common-1.php';
	?>

	<?php if($ColumnController->request->action == 'list' && $ColumnController->found()){ ?>
		<?php
		$pagination = $ColumnController->pagination;
		include COMPONENT_PATH . 'admin/pagination.php';
		?>

		<?php foreach($Column as $obj){ ?>
		<article>
			<code><?= $obj->longid ?></code>
			<h2><?= $obj->name ?></h2>
			<div>
				<a class="button blue"
					href="<?= $server->url ?>/admin/columns/<?= $obj->id ?>">Ansehen</a>
				<a class="button yellow"
					href="<?= $server->url ?>/admin/columns/<?= $obj->id ?>/edit">Bearbeiten</a>
				<a class="button red"
					href="<?= $server->url ?>/admin/columns/<?= $obj->id ?>/delete">Löschen</a>
			</div>
		</article>
		<?php } ?>
	<?php } ?>

	<?php if($ColumnController->request->action == 'show' && $ColumnController->found()){ ?>
		<?php $obj = $Column; ?>
		<article>
			<code><?= $obj->longid ?></code>
			<h1><?= $obj->name ?></h1>
			<p><?= $obj->description ?></p>

			<?php foreach($obj->posts as $post){ ?>
				<article>
					<code><?= $post->longid ?></code>
					<h2><?= $post->headline ?></h2>
				</article>
			<?php } ?>
		</article>
	<?php } ?>

	<?php if(($ColumnController->request->action == 'edit' && !$ColumnController->edited()) || ($ColumnController->request->action == 'new' && !$ColumnController->created())){ ?>
		<?php $obj = $Column; ?>
		<form action="#" method="post">

			<?php if($ColumnController->request->action == 'new'){ ?>
			<label for="longid">
				<span class="name">Rubrik-ID</span>
				<span class="conditions">
					erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
					Bindestriche (-)
				</span>
				<span class="infos">
					Die Rubrik-ID wird in der URL verwendet und entspricht oftmals ungefähr dem Namen.
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
					Der Name der Rubrik.
				</span>
			</label>
			<input type="text" id="name" name="name" value="<?= $obj->name ?>" size="30" required maxlength="30">

			<label for="description">
				<span class="name">Beschreibung</span>
				<span class="conditions">optional</span>
				<span class="infos">
					Die Beschreibung der Rubrik.
				</span>
			</label>
			<textarea id="description" name="description" cols="50" rows="3"><?= $obj->description ?></textarea>

			<button type="submit" class="green">Speichern</button>
		</form>
	<?php } ?>

	<?php if($ColumnController->request->action == 'delete' && !$ColumnController->deleted()){ ?>
		<?php $obj = $Column; ?>
		<p>Rubrik <code><?= $obj->longid ?></code> löschen?</p>
		<form action="#" method="post">
			<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
			<button type="submit" class="red">Löschen</button>
		</form>
	<?php } ?>

</main>
<?php include COMPONENT_PATH . 'admin/end.php'; ?>
