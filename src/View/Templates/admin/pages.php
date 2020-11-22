<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.php'; ?>
		<main>
			<?php if($PageController->request->action == 'list'){ ?>
			<h1>Alle Seiten</h1>
			<?php } else if($PageController->request->action == 'show'){ ?>
			<h1>Seite ansehen</h1>
			<?php } else if($PageController->request->action == 'new'){ ?>
			<h1>Neue Seite erstellen</h1>
			<?php } else if($PageController->request->action == 'edit'){ ?>
			<h1>Seite bearbeiten</h1>
			<?php } else if($PageController->request->action == 'delete'){ ?>
			<h1>Seite löschen</h1>
			<?php } ?>

			<?php if($PageController->request->action == 'list'){ ?>
				<a href="<?= $server->url ?>/admin/pages/new" class="button new green">Neue Seite erstellen</a>
			<?php } else { ?>
				<a href="<?= $server->url ?>/admin/pages" class="button back">Zurück zu allen Seiten</a>
			<?php } ?>

			<?php if($PageController->created()){ ?>
				<div class="message green">
					Seite <code><?= $Page->longid ?></code> wurde erfolgreich hinzugefügt.
				</div>
			<?php } else if($PageController->edited()){ ?>
				<div class="message green">
					Seite <code><?= $Page->longid ?></code> wurde erfolgreich bearbeitet.
				</div>
			<?php } else if($PageController->deleted()){ ?>
				<div class="message green">
					Seite <code><?= $Page->longid ?></code> wurde erfolgreich gelöscht.
				</div>
			<?php } else if($PageController->empty() && $PageController->request->action == 'list'){ ?>
				<div class="message yellow">
					Es sind noch keine Seiten vorhanden.
				</div>
			<?php } else if($PageController->unprocessable()){ ?>
				<div class="message red">
					Die hochgeladenen Daten sind fehlerhaft.
				</div>
				<ul>
				<?php foreach($PageController->errors['import'] as $error){ ?>
					<li><code><?= $error['field'] ?></code>: <?= $error['type'] ?></li>
				<?php } ?>
				</ul>
			<?php } else if($PageController->internal_error()){ ?>
				<div class="message red">
					Es ist ein interner Serverfehler aufgetreten.
				</div>
			<?php } ?>

			<?php if($PageController->request->action != 'list' && $PageController->request->action != 'new'){ ?>
			<div>
				<?php if($PageController->request->action != 'show'){ ?>
				<a class="button blue" href="<?= $server->url ?>/admin/pages/<?= $Page->id ?>">Ansehen</a>
				<?php } ?>

				<a class="button blue" href="<?= $server->url ?>/<?= $Page->longid ?>">Vorschau</a>

				<?php if($PageController->request->action != 'edit'){ ?>
				<a class="button yellow" href="<?= $server->url ?>/admin/pages/<?= $Page->id ?>/edit">Bearbeiten</a>
				<?php } ?>

				<?php if($PageController->request->action != 'delete'){ ?>
				<a class="button red" href="<?= $server->url ?>/admin/pages/<?= $Page->id ?>/delete">Löschen</a>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if($PageController->request->action == 'list' && $PageController->found()){ ?>
				<?php
				$pagination = $PageController->pagination;
				include COMPONENT_PATH . 'admin/pagination.php';
				?>

				<?php foreach($Page as $obj){ ?>
				<article>
					<code><?= $obj->longid ?></code>
					<h2><?= $obj->title ?></h2>
					<div>
						<a class="button blue" href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>">Ansehen</a>
						<a class="button yellow" href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/edit">Bearbeiten</a>
						<a class="button red" href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/delete">Löschen</a>
					</div>
				</article>
				<?php } ?>
			<?php } ?>

			<?php if($PageController->request->action == 'show' && $PageController->found()){ ?>
				<?php $obj = $Page; ?>
				<article>
					<code><?= $obj->longid ?></code>
					<h1><?= $obj->headline ?></h1>
					<p><?= $obj->content ?></p>
				</article>
			<?php } ?>

			<?php if(($PageController->request->action == 'edit' && !$PageController->edited()) || ($PageController->request->action == 'new' && !$PageController->created())){ ?>
				<?php $obj = $Page; ?>
				<form action="#" method="post">

					<?php if($PageController->request->action == 'new'){ ?>
					<label for="longid">
						<span class="name">Seiten-ID</span>
						<span class="conditions">
							erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
							Bindestriche (-)
						</span>
						<span class="infos">
							Die Seiten-ID wird als URL verwendet
							(<code><?= $server->url ?>/[Seiten-ID]</code>) und entspricht
							oftmals ungefähr dem Titel.
						</span>
					</label>
					<input type="text" id="longid" name="longid" value="<?= $obj->longid ?>" size="40" minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required autocomplete="off">
					<?php } else { ?>
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">
					<?php } ?>

					<label for="title">
						<span class="name">Titel</span>
						<span class="conditions">erforderlich, 1 bis 60 Zeichen</span>
						<span class="infos">
							Der Titel der Seite steht u.a. im Fenstertitel des Browsers und sollte
							einen Hinweis auf den Inhalt geben.
						</span>
					</label>
					<input type="text" id="title" name="title" value="<?= $obj->title ?>" required size="40" maxlength="60">

					<label for="content">
						<span class="name">Inhalt</span>
						<span class="conditions">
							optional, HTML und Markdown-Schreibweise möglich
							(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
						</span>
						<span class="infos">Der eigentliche Inhalt der Seite.</span>
					</label>
					<textarea id="content" name="content" cols="80" rows="20"><?= $obj->content ?></textarea>

					<button type="submit" class="blue">Speichern</button>
				</form>
			<?php } ?>

			<?php if($PageController->request->action == 'delete' && !$PageController->deleted()){ ?>
				<?php $obj = $Page; ?>
				<p>Seite <code><?= $obj->longid ?></code> löschen?</p>
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
