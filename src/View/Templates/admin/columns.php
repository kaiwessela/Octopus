<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.php'; ?>
		<main>
			<?php if($ColumnController->request->action == 'list'){ ?>
			<h1>Alle Rubriken</h1>
			<?php } else if($ColumnController->request->action == 'show'){ ?>
			<h1>Rubrik ansehen</h1>
			<?php } else if($ColumnController->request->action == 'new'){ ?>
			<h1>Neue Rubrik hinzufügen</h1>
			<?php } else if($ColumnController->request->action == 'edit'){ ?>
			<h1>Rubrik bearbeiten</h1>
			<?php } else if($ColumnController->request->action == 'delete'){ ?>
			<h1>Rubrik löschen</h1>
			<?php } ?>

			<?php if($ColumnController->request->action == 'list'){ ?>
				<a href="<?= $server->url ?>/admin/columns/new" class="button new green">Neue Rubrik hinzufügen</a>
			<?php } else { ?>
				<a href="<?= $server->url ?>/admin/columns" class="button back">Zurück zu allen Rubriken</a>
			<?php } ?>

			<?php if($ColumnController->created()){ ?>
				<div class="message green">
					Rubrik <code><?= $Column->longid ?></code> wurde erfolgreich hinzugefügt.
				</div>
			<?php } else if($ColumnController->edited()){ ?>
				<div class="message green">
					Rubrik <code><?= $Column->longid ?></code> wurde erfolgreich bearbeitet.
				</div>
			<?php } else if($ColumnController->deleted()){ ?>
				<div class="message green">
					Rubrik <code><?= $Column->longid ?></code> wurde erfolgreich gelöscht.
				</div>
			<?php } else if($ColumnController->empty() && $ColumnController->request->action == 'list'){ ?>
				<div class="message yellow">
					Es sind noch keine Rubriken vorhanden.
				</div>
			<?php } else if($ColumnController->unprocessable()){ ?>
				<div class="message red">
					Die hochgeladenen Daten sind fehlerhaft.
				</div>
				<ul>
				<?php foreach($ColumnController->errors['import'] as $error){ ?>
					<li><code><?= $error['field'] ?></code>: <?= $error['type'] ?></li>
				<?php } ?>
				</ul>
			<?php } else if($ColumnController->internal_error()){ ?>
				<div class="message red">
					Es ist ein interner Serverfehler aufgetreten.
				</div>
			<?php } ?>

			<?php if($ColumnController->request->action != 'list' && $ColumnController->request->action != 'new'){ ?>
			<div>
				<?php if($ColumnController->request->action != 'show'){ ?>
				<a class="button blue" href="<?= $server->url ?>/admin/columns/<?= $Column->id ?>">Ansehen</a>
				<?php } ?>

				<?php if($ColumnController->request->action != 'edit'){ ?>
				<a class="button yellow" href="<?= $server->url ?>/admin/columns/<?= $Column->id ?>/edit">Bearbeiten</a>
				<?php } ?>

				<?php if($ColumnController->request->action != 'delete'){ ?>
				<a class="button red" href="<?= $server->url ?>/admin/columns/<?= $Column->id ?>/delete">Löschen</a>
				<?php } ?>
			</div>
			<?php } ?>

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

					<label>
						<span class="name">Posts</span>
						<span class="conditions">optional</span>
						<span class="infos">Die Posts, die in der Rubrik enthalten sind.</span>
					</label>
					<div class="pseudoinput">
						<?php foreach($obj->posts as $i => $post){ ?>
						<div class="listitem">
							<p><strong><?= $post->headline ?></strong> <code><?= $post->longid ?></code></p>
							<input type="hidden" name="relations[<?= $i ?>][id]" value="">
							<input type="hidden" name="relations[<?= $i ?>][post_id]" value="">
							<input type="hidden" name="relations[<?= $i ?>][column_id]" value="">

							<label class="radiobodge turn-around blue">
								<span class="label-field">Beibehalten</span>
								<input type="radio" name="relations[<?= $i ?>][action]" value="nothing" checked>
								<span class="bodgeradio">
									<span class="bodgetick"></span>
								</span>
							</label>

							<label class="radiobodge turn-around green">
								<span class="label-field">Hinzufügen</span>
								<input type="radio" name="relations[<?= $i ?>][action]" value="new" disabled>
								<span class="bodgeradio">
									<span class="bodgetick"></span>
								</span>
							</label>

							<label class="radiobodge turn-around yellow">
								<span class="label-field">Bearbeiten</span>
								<input type="radio" name="relations[<?= $i ?>][action]" value="edit" disabled>
								<span class="bodgeradio">
									<span class="bodgetick"></span>
								</span>
							</label>

							<label class="radiobodge turn-around red">
								<span class="label-field">Entfernen</span>
								<input type="radio" name="relations[<?= $i ?>][action]" value="delete">
								<span class="bodgeradio">
									<span class="bodgetick"></span>
								</span>
							</label>
						</div>
						<?php } ?>
					<!-- <button type="button" class="new green">Post hinzufügen</button> -->
					</div>

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
		<?php include COMPONENT_PATH . 'admin/footer.php'; ?>

		<script src="<?= $server->url ?>/resources/js/admin/validate.js"></script>
	</body>
</html>
