<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="<?= $server->url ?>/resources/css/admin.css">
		<title>Fehler 500 – <?= $site->title ?></title>
	</head>
	<body>
		<header>
			<div class="title">
				<?= $site->title ?><span class="darkened"> – Admin</span>
			</div>
		</header>
		<main>
			<section>
				<header class="highlighted">
					<h1>Fehler 500</h1>
					<p>Es ist ein unerwarteter Fehler aufgetreten.</p>
				</header>
				<?php if(!empty($exception)){ ?>
				<h2><?= get_class($exception) ?></h2>
				<p>
					Fehler der Klasse <code><?= get_class($exception) ?></code>,<br>
					aufgetreten in <code><?= $exception->getFile() ?></code>,
					Zeile <code><?= $exception->getLine() ?></code>.
				</p>
				<p>Meldung: <code><?= $exception->getMessage() ?></code></p>
				<h2>Trace</h2>
				<ol>
					<?php foreach($exception->getTrace() as $trait){ ?>
					<li>
						<code><?= $trait['file'] ?></code> (<?= $trait['line'] ?>): <wbr>
						<code><?= $trait['class'] . $trait['type'] . $trait['function'] ?>();</code>
					</li>
					<?php } ?>
				</ol>
				<?php } ?>
			</section>
		</main>
		<footer>
			<p>
				Diese Seite nutzt »Blog« von Kai Florian Wessela in der Version <?= $server->version ?>.<br>
				Diese Software ist freie Software, lizensiert unter MIT-Lizenz und abrufbar unter
				<a href="https://github.com/kaiwessela/blog">github.com/kaiwessela/blog</a>.<br>
				Copyright © 2020 Kai Florian Wessela – <a href="https://wessela.eu">wessela.eu</a>
			</p>
		</footer>
	</body>
</html>
