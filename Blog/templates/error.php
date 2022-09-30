<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title>Fehler <?= $response->code ?> – <?= $title ?></title>
	</head>
	<body>
		<?php include 'components/header.php'; ?>
		<main>
			<section>
				<header class="highlighted">
					<h1>Fehler <?= $exception?->get_status_code() ?? '500' ?></h1>
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
		<?php include 'components/footer.php'; ?>
		<?php include 'components/scripts.php'; ?>
	</body>
</html>
