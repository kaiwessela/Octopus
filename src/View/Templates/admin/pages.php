<?php include COMPONENT_PATH . 'admin/start.php'; ?>
<main>
	<?php
	$Controller = $PageController;
	$Object = $Page;
	$singular = 'Seite';
	$plural = 'Seiten';
	$urlclass = 'pages';

	include COMPONENT_PATH . 'admin/common-1.php';
	?>

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

<?php include COMPONENT_PATH . 'admin/end.php'; ?>
