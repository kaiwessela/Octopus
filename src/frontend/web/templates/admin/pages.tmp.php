<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'admin/head.comp.php'; ?>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'admin/header.comp.php'; ?>
		<main>
			<?php if($Page->action == 'list'){ ?>
			<h1>Alle Seiten</h1>
			<?php } else if($Page->action == 'show'){ ?>
			<h1>Seite ansehen</h1>
			<?php } else if($Page->action == 'new'){ ?>
			<h1>Neue Seite erstellen</h1>
			<?php } else if($Page->action == 'edit'){ ?>
			<h1>Seite bearbeiten</h1>
			<?php } else if($Page->action == 'delete'){ ?>
			<h1>Seite löschen</h1>
			<?php } ?>

			<?php if(!$Page->action == 'list'){ ?>
			<a href="<?= $server->url ?>/admin/pages" class="button">&laquo; Zurück zu allen Seiten</a>
			<?php } ?>

			<?php foreach($Page->errors as $error){ ?>
			<span class="message error">

			</span>
			<p>Details: <span class="code"></span></p>
			<?php } ?>

			<?php if($Page->action == 'list'){ ?>
				<?php if(empty($Page->objects)){ ?>
				<span class="message warning">
					Es sind noch keine Seiten vorhanden.
				</span>

				<?php } else { foreach($Page->objects as $obj){ ?>
				<article class="page preview">
					<p class="longid"><?= $obj->longid ?></p>
					<h3 class="title"><?= $obj->title ?></p>
					<div>
						<a href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>" class="view">Ansehen</a>
						<a href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</div>
				</article>
				<?php }} ?>
			<?php } ?>

			<?php if($Page->action == 'show'){ ?>
				<?php $obj = $Page->object; ?>
				<article class="page">
					<p>
						<a href="<?= $server->url ?>/<?= $obj->longid ?>">Blogansicht</a>
						<a href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
						<a href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/delete" class="delete">Löschen</a>
					</p>
					Blogansicht benutzen.
				</article>
			<?php } ?>

			<?php if($Page->action == 'edit' && !$Page->action->completed()){ ?>
				<?php $obj = $Page->object; ?>
				<form action="#" method="post">
					<input type="hidden" name="id" value="<?= $obj->id ?>">
					<input type="hidden" name="longid" value="<?= $obj->longid ?>">

					<label for="title">
						<span class="name">Titel</span>
						<span class="requirements">erforderlich, 1 bis 128 Zeichen</span>
						<span class="description">
							Der Titel der Seite steht u.a. im Fenstertitel des Browsers und sollte
							einen Hinweis auf den Inhalt geben.
						</span>
					</label>
					<input type="text" id="title" class="title" name="title" value="<?= $obj->title ?>" required>

					<label for="content">
						<span class="name">Inhalt</span>
						<span class="requirements">
							optional, HTML und Markdown-Schreibweise möglich
							(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
						</span>
						<span class="description">Der eigentliche Inhalt der Seite.</span>
					</label>
					<textarea id="content" class="content" name="content" class="long-text"><?= $obj->content ?></textarea>

					<input type="submit" value="Speichern">
				</form>
			<?php } ?>

			<?php if($Page->action == 'new' && !$Page->action->completed()){ ?>
				<?php $obj = $Page->object; ?>
				<form action="#" method="post">
					<label for="longid">
						<span class="name">Seiten-ID</span>
						<span class="requirements">
							erforderlich; 9 bis 128 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
							Bindestriche (-)
						</span>
						<span class="description">
							Die Seiten-ID wird als URL verwendet
							(https://<?= $server->url ?>/[Seiten-ID]) und entspricht oftmals
							ungefähr dem Titel.
						</span>
					</label>
					<input type="text" id="longid" class="longid" name="longid" required>

					<label for="title">
						<span class="name">Titel</span>
						<span class="requirements">erforderlich, 1 bis 128 Zeichen</span>
						<span class="description">
							Der Titel der Seite steht u.a. im Fenstertitel des Browsers und sollte
							einen Hinweis auf den Inhalt geben.
						</span>
					</label>
					<input type="text" id="title" class="title" name="title" required>

					<label for="content">
						<span class="name">Inhalt</span>
						<span class="requirements">
							optional, HTML und Markdown-Schreibweise möglich
							(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
						</span>
						<span class="description">Der eigentliche Inhalt der Seite.</span>
					</label>
					<textarea id="content" class="content" name="content"></textarea>

					<input type="submit" value="Speichern">
				</form>
			<?php } ?>

			<?php if($Page->action == 'delete' && !$Page->action->completed()){ ?>
				<?php $obj = $Page->object; ?>
				<p>
					<a href="<?= $server->url ?>/<?= $obj->longid ?>">Blogansicht</a>
					<a href="<?= $server->url ?>/admin/pages/<?= $obj->id ?>/edit" class="edit">Bearbeiten</a>
				</p>
				<p>Seite <span class="code"><?= $obj->longid ?></span> löschen?</p>
				<form action="#" method="post">
					<input type="hidden" id="id" name="id" value="<?= $obj->id ?>">
					<input type="submit" value="Löschen">
				</form>
			<?php } ?>

		</main>
	</body>
</html>
